<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\AuditLog;
use App\Services\ArsipService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ArsipController extends Controller
{
    public function __construct(private ArsipService $arsipService) {}

    /**
     * Display a listing of the archives with instant search & multi-filter.
     */
    public function index(Request $request)
    {
        $user = Auth::user();

        $data = $this->arsipService->getIndexData($request, $user);

        return Inertia::render('Arsip/Index', $data);
    }

    /**
     * Show the form for creating a new archive (multi-file upload).
     */
    public function create()
    {
        $user = Auth::user();

        return Inertia::render('Arsip/Create', $this->arsipService->getCreateData($user));
    }

    /**
     * Store multiple uploaded files as archive records in secure storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();

        $request->validate([
            'files' => ['required', 'array', 'min:1', 'max:10'],
            'files.*' => ['file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:10240'], // Max 10MB per file
            'judul' => ['required', 'array', 'min:1', 'max:10'],
            'judul.*' => ['required', 'string', 'max:255'],
            'nomor_arsip' => ['nullable', 'array'],
            'nomor_arsip.*' => ['nullable', 'string', 'max:100', 'unique:arsip,nomor_arsip'],
            'nomor_surat' => ['nullable', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori_id' => ['required', 'exists:kategori_arsip,id'],
            'divisi_id' => ['required', 'exists:divisi,id'],
            'tahun' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            'retention_date' => ['required', 'date', 'after:today'],
            'status_publikasi' => ['required', 'in:Public,Restricted'],
            'tags' => ['nullable', 'string', 'max:255'],
        ]);

        // Operator / Staf TU restriction
        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $request->divisi_id != $user->divisi_id) {
            return back()->withErrors([
                'divisi_id' => 'Anda hanya dapat mengarsipkan dokumen di unit kerja Anda sendiri.',
            ]);
        }

        $createdCount = $this->arsipService->storeFiles($request, $user);

        return redirect()->route('arsip.index')
            ->with('success', "{$createdCount} berkas berhasil diarsipkan ke penyimpanan aman.");
    }

    /**
     * Show the form for editing the archive.
     */
    public function edit(Arsip $arsip)
    {
        $user = Auth::user();

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan mengedit arsip unit lain.');
        }

        return Inertia::render('Arsip/Edit', $this->arsipService->getEditData($arsip, $user));
    }

    /**
     * Update the archive details and handle document versioning.
     */
    public function update(Request $request, Arsip $arsip)
    {
        $user = Auth::user();

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan memperbarui arsip unit lain.');
        }

        $request->validate([
            'nomor_arsip' => ['required', 'string', 'unique:arsip,nomor_arsip,'.$arsip->id],
            'nomor_surat' => ['nullable', 'string', 'max:255'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori_id' => ['required', 'exists:kategori_arsip,id'],
            'divisi_id' => ['required', 'exists:divisi,id'],
            'tahun' => ['nullable', 'integer', 'min:1990', 'max:'.now()->year],
            'file' => ['nullable', 'file', 'mimes:pdf,docx,jpg,jpeg,png', 'max:10240'],
            'retention_date' => ['required', 'date'],
            'status' => ['required', 'in:Aktif,Expired,Dimusnahkan'],
            'status_publikasi' => ['required', 'in:Public,Restricted'],
            'tags' => ['nullable', 'string', 'max:255'],
            'change_note' => ['nullable', 'string', 'max:255'],
        ]);

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $request->divisi_id != $user->divisi_id) {
            return back()->withErrors([
                'divisi_id' => 'Anda hanya dapat mengarsipkan dokumen di unit kerja Anda sendiri.',
            ]);
        }

        $this->arsipService->updateArchive($request, $arsip);

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil diperbarui.');
    }

    /**
     * Soft delete the archive.
     */
    public function destroy(Arsip $arsip)
    {
        $user = Auth::user();

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menghapus arsip unit lain.');
        }

        $this->arsipService->deleteArchive($arsip);

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil dipindahkan ke Recycle Bin.');
    }

    /**
     * Display a listing of soft deleted archives.
     */
    public function trashIndex()
    {
        $user = Auth::user();

        return Inertia::render('Arsip/Trash', [
            'arsip' => $this->arsipService->getTrashData($user),
        ]);
    }

    /**
     * Restore a soft deleted archive.
     */
    public function restore($id)
    {
        $user = Auth::user();
        $arsip = Arsip::onlyTrashed()->findOrFail($id);

        if (($user->hasRole('Operator') || $user->hasRole('Staf TU')) && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan memulihkan arsip unit lain.');
        }

        $this->arsipService->restoreArchive($id);

        return redirect()->route('arsip.trash')->with('success', 'Arsip berhasil dipulihkan dari Recycle Bin.');
    }

    /**
     * Force delete (permanently delete) the archive and its versions.
     */
    public function forceDelete($id)
    {
        $user = Auth::user();

        if (! $user->hasRole('Superadmin')) {
            abort(403, 'Hanya Superadmin yang diizinkan menghapus arsip secara permanen.');
        }

        $this->arsipService->forceDeleteArchive($id);

        return redirect()->route('arsip.trash')->with('success', 'Arsip berhasil dihapus secara permanen dari server.');
    }

    /**
     * Build the hierarchical folder tree (Fakultas > Prodi > Tahun > Kategori).
     */
    public function explorer()
    {
        return Inertia::render('Arsip/Explorer', [
            'tree' => $this->arsipService->getExplorerData(),
        ]);
    }

    /**
     * Securely stream file to client inline (to view).
     */
    public function streamFile(Arsip $arsip)
    {
        if (! $this->arsipService->canAccessFile($arsip, Auth::user())) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat arsip ini.');
        }

        if ($arsip->status === 'Expired') {
            abort(403, 'Akses ditolak. Masa retensi arsip ini telah habis (Expired).');
        }

        $absolutePath = Storage::disk('local')->path($arsip->file_path);

        if (! file_exists($absolutePath)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        return response()->file($absolutePath);
    }

    /**
     * Securely download file to client.
     */
    public function downloadFile(Arsip $arsip)
    {
        if (! $this->arsipService->canAccessFile($arsip, Auth::user())) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh arsip ini.');
        }

        if ($arsip->status === 'Expired') {
            abort(403, 'Akses ditolak. Masa retensi arsip ini telah habis (Expired).');
        }

        $absolutePath = Storage::disk('local')->path($arsip->file_path);

        if (! file_exists($absolutePath)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'Download',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Mengunduh file arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}).",
        ]);

        return response()->download($absolutePath);
    }

    /**
     * Show in-app document viewer (legacy Blade page).
     */
    public function viewDocument(Arsip $arsip)
    {
        if (! $this->arsipService->canAccessFile($arsip, Auth::user())) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membuka penampil arsip ini.');
        }

        if ($arsip->status === 'Expired') {
            abort(403, 'Akses ditolak. Masa retensi arsip ini telah habis (Expired).');
        }

        return view('arsip.viewer', compact('arsip'));
    }
}
