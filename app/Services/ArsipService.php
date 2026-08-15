<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\ArsipVersion;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\Peminjaman;
use App\Models\StudyProgram;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ArsipService
{
    /**
     * Build the arsip index data with instant search & multi-filter.
     */
    public function getIndexData(Request $request, User $user): array
    {
        $query = Arsip::with(['divisi', 'kategori', 'uploader', 'studyProgram']);

        // ===== Instant Search & Multi-Filter =====
        if ($request->filled('search')) {
            $search = trim($request->search);
            $query->where(function ($q) use ($search) {
                $q->where('judul', 'like', '%'.$search.'%')
                    ->orWhere('nomor_arsip', 'like', '%'.$search.'%')
                    ->orWhere('nomor_surat', 'like', '%'.$search.'%')
                    ->orWhere('pengirim', 'like', '%'.$search.'%')
                    ->orWhere('penerima', 'like', '%'.$search.'%')
                    ->orWhere('tags', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('kategori_id')) {
            $query->where('kategori_id', $request->kategori_id);
        }

        if ($request->filled('divisi_id')) {
            $query->where(function ($q) use ($request) {
                $q->where('divisi_id', $request->divisi_id)
                    ->orWhereIn(
                        'divisi_id',
                        Divisi::where('parent_id', $request->divisi_id)->pluck('id')
                    );
            });
        }

        if ($request->filled('study_program_id')) {
            $query->where('study_program_id', $request->study_program_id);
        }

        if ($request->filled('tahun')) {
            $query->where('tahun', $request->tahun);
        }

        if ($request->filled('status_publikasi')) {
            $query->where('status_publikasi', $request->status_publikasi);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $arsip = $query->latest()->paginate(10)->withQueryString();

        // Active borrow requests for requester roles (Mahasiswa/Dosen/Karyawan)
        $requesterRoles = ['Karyawan', 'Mahasiswa', 'Dosen'];
        $activePeminjaman = collect();
        if (array_intersect($user->getRoleNames()->all(), $requesterRoles)) {
            $activePeminjaman = Peminjaman::where('user_id', $user->id)
                ->whereIn('status_approval', ['Pending', 'Approved'])
                ->get()
                ->keyBy('arsip_id');
        }

        return [
            'arsip' => $arsip,
            'filters' => $request->only(['search', 'kategori_id', 'divisi_id', 'study_program_id', 'tahun', 'status_publikasi', 'status']),
            'kategori' => KategoriArsip::orderBy('name')->get(),
            'kategoriTree' => $this->kategoriTree(),
            'divisiTree' => $this->divisiTree(),
            'studyPrograms' => StudyProgram::orderBy('name')->get(),
            'tahunList' => $this->tahunList(),
            'activePeminjaman' => $activePeminjaman,
        ];
    }

    /**
     * Build the create form data (multi-file upload).
     */
    public function getCreateData(User $user): array
    {
        return [
            'divisiTree' => $this->divisiTree($user->hasRole('Operator') || $user->hasRole('Staf TU')),
            'kategoriTree' => $this->kategoriTree(),
            'studyPrograms' => StudyProgram::orderBy('name')->get(),
            'tahunList' => $this->tahunList(),
        ];
    }

    /**
     * Store multiple uploaded files as archive records in secure storage.
     */
    public function storeFiles(Request $request, User $user): int
    {
        $tahun = $request->tahun ?: now()->year;
        $createdCount = 0;

        foreach ($request->file('files') as $index => $file) {
            $judul = $request->judul[$index] ?? 'Dokumen tanpa judul';

            // Automatic unique file naming
            $extension = $file->getClientOriginalExtension() ?: 'pdf';
            $fileName = Str::slug($judul).'-'.uniqid().'.'.strtolower($extension);
            $fileSubPath = 'private/archives/'.$request->divisi_id.'/'.$tahun;
            $filePath = Storage::disk('local')->putFileAs($fileSubPath, $file, $fileName);

            // Automatic nomor_arsip when not provided
            $nomorArsip = $request->nomor_arsip[$index] ?? $this->generateNomorArsip($request->divisi_id, $tahun);

            Arsip::create([
                'nomor_arsip' => $nomorArsip,
                'nomor_surat' => $request->nomor_surat,
                'judul' => $judul,
                'deskripsi' => $request->deskripsi,
                'kategori_id' => $request->kategori_id,
                'divisi_id' => $request->divisi_id,
                'study_program_id' => $request->study_program_id,
                'tahun' => $tahun,
                'tanggal_dokumen' => $request->tanggal_dokumen,
                'tanggal_diterima' => $request->tanggal_diterima ?: now()->toDateString(),
                'pengirim' => $request->pengirim,
                'penerima' => $request->penerima,
                'file_path' => $filePath,
                'lokasi_fisik' => $request->lokasi_fisik,
                'file_size' => $file->getSize(),
                'file_mime' => $file->getMimeType(),
                'retention_date' => $request->retention_date,
                'status' => 'Aktif',
                'status_publikasi' => $request->status_publikasi,
                'tags' => $request->tags,
                'uploader_id' => $user->id,
            ]);

            $createdCount++;
        }

        return $createdCount;
    }

    /**
     * Build the edit form data.
     */
    public function getEditData(Arsip $arsip, User $user): array
    {
        return [
            'arsip' => $arsip->load(['divisi', 'kategori', 'studyProgram']),
            'divisiTree' => $this->divisiTree($user->hasRole('Operator') || $user->hasRole('Staf TU')),
            'kategoriTree' => $this->kategoriTree(),
            'studyPrograms' => StudyProgram::orderBy('name')->get(),
            'tahunList' => $this->tahunList(),
        ];
    }

    /**
     * Update the archive details and handle document versioning.
     */
    public function updateArchive(Request $request, Arsip $arsip): void
    {
        $data = [
            'nomor_arsip' => $request->nomor_arsip,
            'nomor_surat' => $request->nomor_surat,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'kategori_id' => $request->kategori_id,
            'divisi_id' => $request->divisi_id,
            'study_program_id' => $request->study_program_id,
            'tahun' => $request->tahun ?: now()->year,
            'tanggal_dokumen' => $request->tanggal_dokumen,
            'tanggal_diterima' => $request->tanggal_diterima,
            'pengirim' => $request->pengirim,
            'penerima' => $request->penerima,
            'lokasi_fisik' => $request->lokasi_fisik,
            'retention_date' => $request->retention_date,
            'status' => $request->status,
            'status_publikasi' => $request->status_publikasi,
            'tags' => $request->tags,
        ];

        // Document Versioning: If new file is uploaded
        if ($request->hasFile('file')) {
            $currentVersionNumber = $arsip->versions()->max('version_number') ?? 0;

            ArsipVersion::create([
                'arsip_id' => $arsip->id,
                'version_number' => $currentVersionNumber + 1,
                'file_path' => $arsip->file_path,
                'uploaded_by' => $arsip->uploader_id ?? $request->user()->id,
                'change_note' => $request->change_note ?? 'Revisi dokumen unggah baru.',
            ]);

            $file = $request->file('file');
            $fileName = Str::slug($request->judul).'-'.uniqid().'.'.strtolower($file->getClientOriginalExtension() ?: 'pdf');
            $fileSubPath = 'private/archives/'.$request->divisi_id.'/'.($request->tahun ?: now()->year);
            $filePath = Storage::disk('local')->putFileAs($fileSubPath, $file, $fileName);
            $data['file_path'] = $filePath;
            $data['file_size'] = $file->getSize();
            $data['file_mime'] = $file->getMimeType();
        }

        $arsip->update($data);
    }

    /**
     * Soft delete the archive.
     */
    public function deleteArchive(Arsip $arsip): void
    {
        $arsip->delete();
    }

    /**
     * Build the trash (soft deleted) listing data.
     */
    public function getTrashData(User $user)
    {
        $query = Arsip::onlyTrashed()->with(['divisi', 'kategori']);

        if ($user->hasRole('Operator') || $user->hasRole('Staf TU')) {
            $query->where('divisi_id', $user->divisi_id);
        }

        return $query->latest()->paginate(10)->withQueryString();
    }

    /**
     * Restore a soft deleted archive.
     */
    public function restoreArchive($id): Arsip
    {
        $arsip = Arsip::onlyTrashed()->findOrFail($id);
        $arsip->restore();

        return $arsip;
    }

    /**
     * Force delete (permanently delete) the archive and its versions.
     */
    public function forceDeleteArchive($id): void
    {
        $arsip = Arsip::onlyTrashed()->findOrFail($id);

        if (Storage::disk('local')->exists($arsip->file_path)) {
            Storage::disk('local')->delete($arsip->file_path);
        }

        foreach ($arsip->versions as $version) {
            if (Storage::disk('local')->exists($version->file_path)) {
                Storage::disk('local')->delete($version->file_path);
            }
        }

        $arsip->forceDelete();
    }

    /**
     * Build the hierarchical folder tree (Fakultas > Prodi > Tahun > Kategori).
     */
    public function getExplorerData()
    {
        $fakultas = Divisi::with('children')->whereNull('parent_id')->orderBy('name')->get();

        $arsipByDivisi = Arsip::with('kategori')
            ->where('status', 'Aktif')
            ->get(['id', 'judul', 'nomor_arsip', 'kategori_id', 'divisi_id', 'tahun'])
            ->groupBy('divisi_id');

        return $fakultas->map(function ($f) use ($arsipByDivisi) {
            $children = $f->children->map(function ($prodi) use ($arsipByDivisi) {
                $items = $arsipByDivisi->get($prodi->id, collect());

                $years = $items
                    ->groupBy(fn ($item) => $item->tahun ?? 'Tanpa Tahun')
                    ->sortKeysDesc()
                    ->map(function ($yearItems, $tahun) {
                        $kategori = $yearItems->groupBy('kategori_id')->map(function ($katItems) {
                            $first = $katItems->first();

                            return [
                                'id' => $katItems->pluck('kategori_id')->first(),
                                'name' => $first?->kategori?->name ?? 'Tanpa Kategori',
                                'count' => $katItems->count(),
                            ];
                        })->values();

                        return [
                            'tahun' => $tahun,
                            'total' => $yearItems->count(),
                            'kategori' => $kategori,
                        ];
                    })->values();

                return [
                    'id' => $prodi->id,
                    'name' => $prodi->name,
                    'count' => $items->count(),
                    'years' => $years,
                ];
            });

            return [
                'id' => $f->id,
                'name' => $f->name,
                'count' => $children->sum('count'),
                'children' => $children,
            ];
        });
    }

    /**
     * Check if a user is authorized to read/download a specific archive file.
     */
    public function canAccessFile(Arsip $arsip, User $user): bool
    {
        if ($user->hasRole('Superadmin')) {
            return true;
        }

        if ($arsip->status_publikasi === 'Public') {
            return true;
        }

        $staffSameDivisi = ($user->hasRole('Operator') || $user->hasRole('Staf TU'))
            && $arsip->divisi_id == $user->divisi_id;

        if ($arsip->status_publikasi === 'Internal'
            && ($staffSameDivisi || $user->hasRole('Dosen') || $user->hasRole('Kaprodi') || $user->hasRole('Dekan'))) {
            return true;
        }

        if ($arsip->status_publikasi === 'Confidential'
            && ($staffSameDivisi || $user->hasRole('Kaprodi') || $user->hasRole('Dekan'))) {
            return true;
        }

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
     * Build nested kategori tree for dropdowns & tree navigation.
     */
    private function kategoriTree(): array
    {
        return KategoriArsip::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'name' => $k->name,
                'kode' => $k->kode,
                'children' => $k->children->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'kode' => $c->kode,
                ])->all(),
            ])
            ->all();
    }

    /**
     * Build divisi tree. For Operator/Staf TU only their own unit is returned.
     */
    private function divisiTree(bool $selfOnly = false): array
    {
        if ($selfOnly) {
            $user = auth()->user();

            return Divisi::where('id', $user->divisi_id)->get()->map(fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'kode' => $d->kode,
                'children' => [],
            ])->all();
        }

        $fakultas = Divisi::with('children')->whereNull('parent_id')->orderBy('name')->get();
        $orgUnits = Divisi::whereNull('parent_id')->whereNull('kode')->orderBy('name')->get();

        $map = fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'kode' => $d->kode,
            'children' => ($d->children ?? collect())->map(fn ($c) => [
                'id' => $c->id,
                'name' => $c->name,
                'kode' => $c->kode,
                'children' => [],
            ])->all(),
        ];

        return collect($fakultas)->map($map)
            ->concat($orgUnits->map($map))
            ->values()
            ->all();
    }

    /**
     * Distinct list of years present in the archive records.
     */
    private function tahunList(): array
    {
        return Arsip::whereNotNull('tahun')
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun')
            ->map(fn ($t) => (int) $t)
            ->all();
    }

    /**
     * Generate a unique auto nomor_arsip (ARS-{KODE}-{tahun}-{random}).
     */
    private function generateNomorArsip($divisiId, $tahun): string
    {
        $divisi = Divisi::find($divisiId);
        $kode = $divisi?->kode ?: 'ARS';

        do {
            $nomor = 'ARS-'.strtoupper($kode).'-'.$tahun.'-'.strtoupper(Str::random(6));
        } while (Arsip::where('nomor_arsip', $nomor)->exists());

        return $nomor;
    }
}
