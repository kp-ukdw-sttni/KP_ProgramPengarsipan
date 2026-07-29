<?php

namespace App\Http\Controllers;

use App\Models\Arsip;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\Peminjaman;
use App\Models\AuditLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Response;

class ArsipController extends Controller
{
    /**
     * Display a listing of the archives with search filters.
     */
    public function index(Request $request)
    {
        $user = Auth::user();
        
        $query = Arsip::with(['divisi', 'kategori']);

        // Advanced Search Filters
        if ($request->filled('nomor_arsip')) {
            $query->where('nomor_arsip', 'like', '%' . $request->nomor_arsip . '%');
        }
        if ($request->filled('judul')) {
            $query->where('judul', 'like', '%' . $request->judul . '%');
        }
        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }
        if ($request->filled('divisi_id')) {
            $query->where('divisi_id', $request->divisi_id);
        }

        $arsip = $query->latest()->paginate(10);

        // Fetch active borrowing requests for Karyawan to render buttons
        $activePeminjaman = collect();
        if ($user->hasRole('Karyawan')) {
            $activePeminjaman = Peminjaman::where('user_id', $user->id)
                ->whereIn('status_approval', ['Pending', 'Approved'])
                ->get()
                ->keyBy('arsip_id');
        }

        $divisi = Divisi::all();
        $kategori = KategoriArsip::all();

        return view('arsip.index', compact('arsip', 'divisi', 'kategori', 'activePeminjaman'));
    }

    /**
     * Show the form for creating a new archive.
     */
    public function create()
    {
        $user = Auth::user();
        
        // Operators can only create archives in their own division
        if ($user->hasRole('Operator')) {
            $divisi = Divisi::where('id', $user->divisi_id)->get();
        } else {
            $divisi = Divisi::all();
        }
        
        $kategori = KategoriArsip::all();
        return view('arsip.create', compact('divisi', 'kategori'));
    }

    /**
     * Store a newly created archive in secure storage.
     */
    public function store(Request $request)
    {
        $user = Auth::user();
        
        $request->validate([
            'nomor_arsip' => ['required', 'string', 'unique:arsip,nomor_arsip'],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori_id' => ['required', 'exists:kategori_arsip,id'],
            'divisi_id' => ['required', 'exists:divisi,id'],
            'file' => ['required', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'], // Max 10MB
            'retention_date' => ['required', 'date', 'after:today'],
        ]);

        // Enforcement: Operator can only store under their own division
        if ($user->hasRole('Operator') && $request->divisi_id != $user->divisi_id) {
            return back()->withErrors(['divisi_id' => 'Sebagai Operator, Anda hanya dapat mengarsipkan dokumen di divisi Anda sendiri.']);
        }

        // Store file securely in storage/app/private/archives
        $file = $request->file('file');
        $fileName = time() . '_' . $file->getClientOriginalName();
        // putFileAs stores inside private storage since 'local' disk is private by default in Laravel
        $fileSubPath = 'private/archives/' . $request->divisi_id;
        $filePath = Storage::disk('local')->putFileAs($fileSubPath, $file, $fileName);

        Arsip::create([
            'nomor_arsip' => $request->nomor_arsip,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'kategori_id' => $request->kategori_id,
            'divisi_id' => $request->divisi_id,
            'file_path' => $filePath,
            'retention_date' => $request->retention_date,
            'status' => 'Aktif',
        ]);

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil disimpan dalam penyimpanan aman.');
    }

    /**
     * Show the form for editing the archive.
     */
    public function edit(Arsip $arsip)
    {
        $user = Auth::user();
        
        // Authorization check: Operator can only edit their own division's archives
        if ($user->hasRole('Operator') && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan mengedit arsip divisi lain.');
        }

        if ($user->hasRole('Operator')) {
            $divisi = Divisi::where('id', $user->divisi_id)->get();
        } else {
            $divisi = Divisi::all();
        }

        $kategori = KategoriArsip::all();
        return view('arsip.edit', compact('arsip', 'divisi', 'kategori'));
    }

    /**
     * Update the archive details.
     */
    public function update(Request $request, Arsip $arsip)
    {
        $user = Auth::user();

        // Authorization check
        if ($user->hasRole('Operator') && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan memperbarui arsip divisi lain.');
        }

        $request->validate([
            'nomor_arsip' => ['required', 'string', 'unique:arsip,nomor_arsip,' . $arsip->id],
            'judul' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'kategori_id' => ['required', 'exists:kategori_arsip,id'],
            'divisi_id' => ['required', 'exists:divisi,id'],
            'file' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:10240'],
            'retention_date' => ['required', 'date'],
            'status' => ['required', 'in:Aktif,Expired,Dimusnahkan'],
        ]);

        // Enforcement: Operator cannot change the division to another division
        if ($user->hasRole('Operator') && $request->divisi_id != $user->divisi_id) {
            return back()->withErrors(['divisi_id' => 'Sebagai Operator, Anda hanya dapat mengarsipkan dokumen di divisi Anda sendiri.']);
        }

        $data = [
            'nomor_arsip' => $request->nomor_arsip,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'kategori_id' => $request->kategori_id,
            'divisi_id' => $request->divisi_id,
            'retention_date' => $request->retention_date,
            'status' => $request->status,
        ];

        // If new file is uploaded
        if ($request->hasFile('file')) {
            // Delete old file
            if (Storage::disk('local')->exists($arsip->file_path)) {
                Storage::disk('local')->delete($arsip->file_path);
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $fileSubPath = 'private/archives/' . $request->divisi_id;
            $filePath = Storage::disk('local')->putFileAs($fileSubPath, $file, $fileName);
            $data['file_path'] = $filePath;
        }

        $arsip->update($data);

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil diperbarui.');
    }

    /**
     * Delete the archive.
     */
    public function destroy(Arsip $arsip)
    {
        $user = Auth::user();

        // Authorization check
        if ($user->hasRole('Operator') && $arsip->divisi_id != $user->divisi_id) {
            abort(403, 'Anda tidak diizinkan menghapus arsip divisi lain.');
        }

        // Delete physical file
        if (Storage::disk('local')->exists($arsip->file_path)) {
            Storage::disk('local')->delete($arsip->file_path);
        }

        $arsip->delete();

        return redirect()->route('arsip.index')->with('success', 'Arsip berhasil dihapus permanen.');
    }

    /**
     * Check if a user is authorized to read/download a specific archive file.
     */
    private function authorizeFileAccess(Arsip $arsip)
    {
        $user = Auth::user();

        // 1. Superadmin has full access
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        // 2. Operator has access to their own division's archives
        if ($user->hasRole('Operator') && $arsip->divisi_id == $user->divisi_id) {
            return true;
        }

        // 3. Employees (Karyawan) / Operators of other divisions must have an active and approved borrow record
        // An active borrow request is one that is 'Approved' and current time is between borrowed_at and expired_at.
        $hasActivePeminjaman = Peminjaman::where('arsip_id', $arsip->id)
            ->where('user_id', $user->id)
            ->where('status_approval', 'Approved')
            ->where('borrowed_at', '<=', now())
            ->where('expired_at', '>=', now())
            ->exists();

        if ($hasActivePeminjaman) {
            return true;
        }

        return false;
    }

    /**
     * Securely stream file to client inline (to view).
     */
    public function streamFile(Arsip $arsip)
    {
        if (!$this->authorizeFileAccess($arsip)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk melihat arsip ini.');
        }

        if ($arsip->status === 'Expired') {
            abort(403, 'Akses ditolak. Masa retensi arsip ini telah habis (Expired).');
        }

        $absolutePath = Storage::disk('local')->path($arsip->file_path);

        if (!file_exists($absolutePath)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        // Log the view action to the database
        AuditLog::create([
            'user_id' => Auth::id(),
            'action' => 'View',
            'arsip_id' => $arsip->id,
            'ip_address' => request()->ip(),
            'details' => "Membuka file arsip '{$arsip->judul}' (Nomor: {$arsip->nomor_arsip}) melalui viewer.",
        ]);

        return response()->file($absolutePath);
    }

    /**
     * Securely download file to client.
     */
    public function downloadFile(Arsip $arsip)
    {
        if (!$this->authorizeFileAccess($arsip)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengunduh arsip ini.');
        }

        if ($arsip->status === 'Expired') {
            abort(403, 'Akses ditolak. Masa retensi arsip ini telah habis (Expired).');
        }

        $absolutePath = Storage::disk('local')->path($arsip->file_path);

        if (!file_exists($absolutePath)) {
            abort(404, 'File tidak ditemukan di server.');
        }

        // Log the download action to the database
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
     * Show in-app document viewer.
     */
    public function viewDocument(Arsip $arsip)
    {
        if (!$this->authorizeFileAccess($arsip)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk membuka penampil arsip ini.');
        }

        if ($arsip->status === 'Expired') {
            abort(403, 'Akses ditolak. Masa retensi arsip ini telah habis (Expired).');
        }

        return view('arsip.viewer', compact('arsip'));
    }
}
