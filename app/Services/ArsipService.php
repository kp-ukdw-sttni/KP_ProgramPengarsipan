<?php

namespace App\Services;

use App\Models\Arsip;
use App\Models\ArsipVersion;
use App\Models\Divisi;
use App\Models\Peminjaman;
use App\Models\StudyProgram;
use App\Models\User;
use App\Support\AksesDokumen;
use App\Support\MasterData;
use Carbon\Carbon;
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

        $this->lampirkanStatusAkses($arsip, $user);

        return [
            'arsip' => $arsip,
            'filters' => $request->only(['search', 'kategori_id', 'divisi_id', 'study_program_id', 'tahun', 'status_publikasi', 'status']),
            'kategori' => MasterData::kategoriFlat(),
            'kategoriTree' => MasterData::kategoriTree(),
            'divisiTree' => MasterData::divisiTree(),
            'studyPrograms' => MasterData::studyPrograms(),
            'tahunList' => MasterData::tahunList(),
        ];
    }

    /**
     * Annotate each row with what the current user may actually do, so the list
     * can show a locked Confidential document plus a "Minta Akses" button
     * instead of guessing from the publication level alone.
     *
     * Resolved server-side on purpose: the old client-side canViewFile() had no
     * notion of an approved request, so an approved user saw no buttons.
     */
    private function lampirkanStatusAkses($paginator, User $user): void
    {
        $ids = $paginator->getCollection()->pluck('id')->all();

        if ($ids === []) {
            return;
        }

        $requests = Peminjaman::whereIn('arsip_id', $ids)
            ->where('user_id', $user->id)
            ->get()
            ->groupBy('arsip_id');

        $paginator->getCollection()->each(function (Arsip $arsip) use ($requests, $user) {
            $request = $requests->get($arsip->id)?->sortByDesc('id')->first();

            $arsip->setAttribute('bisa_diakses', AksesDokumen::canAccess($arsip, $user));
            $arsip->setAttribute('butuh_persetujuan', AksesDokumen::perluPersetujuan($arsip));
            $arsip->setAttribute('status_permintaan', $request?->status_approval ?? null);
            $arsip->setAttribute('permintaan_id', $request?->id ?? null);
        });
    }

    /**
     * Build the create form data (multi-file upload).
     */
    public function getCreateData(User $user): array
    {
        return [
            'divisiTree' => $this->divisiTree($user->hasRole('Operator') || $user->hasRole('Staf TU')),
            'kategoriTree' => MasterData::kategoriTree(),
            'studyPrograms' => MasterData::studyPrograms(),
            'tahunList' => MasterData::tahunList(),
        ];
    }

    /**
     * Store multiple uploaded files as archive records in secure storage.
     */
    public function storeFiles(Request $request, User $user): int
    {
        // Ambil tahun dari tanggal dokumen (YYYY)
        $tahun = Carbon::parse($request->tanggal_dokumen)->year;
        $createdCount = 0;

        $publikasiMap = [
            'Internal' => 'Internal',
            'Terbatas' => 'Internal',
            'Rahasia' => 'Confidential',
            'Confidential' => 'Confidential',
        ];
        $statusPublikasi = $publikasiMap[$request->status_publikasi] ?? 'Internal';

        $nomorSurat = $request->boolean('tanpa_nomor_surat') ? 'Tanpa Nomor' : ($request->nomor_surat ?: 'Tanpa Nomor');

        $divisiId = $request->divisi_id;
        if (! $divisiId && ! $user->hasRole('Admin') && ! $user->hasRole('Superadmin')) {
            $divisiId = $user->divisi_id;
        }

        $namaProdi = $request->boolean('tanpa_prodi') ? null : ($request->nama_prodi ?: null);

        foreach ($request->file('files') as $index => $file) {
            $judul = $request->judul[$index] ?? 'Dokumen tanpa judul';

            // Automatic unique file naming
            $extension = $file->getClientOriginalExtension() ?: 'pdf';
            $fileName = Str::slug($judul).'-'.uniqid().'.'.strtolower($extension);
            $subFolder = $divisiId ? 'divisi/'.$divisiId : 'prodi/'.Str::slug($namaProdi ?: 'general');
            $fileSubPath = 'private/archives/'.$subFolder.'/'.$tahun;
            $filePath = Storage::disk('local')->putFileAs($fileSubPath, $file, $fileName);

            // Automatic nomor_arsip when not provided
            $nomorArsip = $request->nomor_arsip[$index] ?? $this->generateNomorArsip($divisiId, $tahun, null);

            $arsip = Arsip::create([
                'nomor_arsip' => $nomorArsip,
                'nomor_surat' => $nomorSurat,
                'judul' => $judul,
                'deskripsi' => $request->deskripsi,
                'kategori_id' => $request->kategori_id,
                'divisi_id' => $divisiId,
                'study_program_id' => null,
                'nama_prodi' => $namaProdi,
                'tahun' => $tahun,
                'tanggal_dokumen' => $request->tanggal_dokumen ?: now()->toDateString(),
                'tanggal_diterima' => $request->tanggal_diterima ?: now()->toDateString(),
                'pengirim' => $request->pengirim,
                'penerima' => $request->penerima,
                'file_path' => $filePath,
                'lokasi_fisik' => $request->lokasi_fisik,
                'file_size' => $file->getSize(),
                'file_mime' => $file->getMimeType(),
                'retention_date' => $request->retention_date ?: now()->addYears(5)->toDateString(),
                'status' => 'Aktif',
                'status_publikasi' => $statusPublikasi,
                'tags' => $request->tags,
                'uploader_id' => $user->id,
            ]);

            // Otomatis Versi Dokumen Awal (v1.0)
            ArsipVersion::create([
                'arsip_id' => $arsip->id,
                'version_number' => 1,
                'file_path' => $filePath,
                'uploaded_by' => $user->id,
                'change_note' => 'v1.0 (Unggah Berkas Awal)',
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
            'kategoriTree' => MasterData::kategoriTree(),
            'studyPrograms' => MasterData::studyPrograms(),
            'tahunList' => MasterData::tahunList(),
        ];
    }

    /**
     * Update the archive details and handle document versioning.
     */
    public function updateArchive(Request $request, Arsip $arsip): void
    {
        $user = $request->user();
        $divisiId = $request->divisi_id;
        if (! $divisiId && ! $user->hasRole('Admin') && ! $user->hasRole('Superadmin')) {
            $divisiId = $user->divisi_id ?: $arsip->divisi_id;
        }

        $namaProdi = $request->boolean('tanpa_prodi') ? null : ($request->nama_prodi ?: null);

        $data = [
            'nomor_arsip' => $request->nomor_arsip,
            'nomor_surat' => $request->nomor_surat,
            'judul' => $request->judul,
            'deskripsi' => $request->deskripsi,
            'kategori_id' => $request->kategori_id,
            'divisi_id' => $divisiId,
            'study_program_id' => null,
            'nama_prodi' => $namaProdi,
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
        $disk = Storage::disk('local');

        $paths = array_filter([
            $arsip->file_path,
            $arsip->file_path_excel,
        ]);

        foreach ($arsip->versions as $version) {
            $paths[] = $version->file_path;
        }

        foreach (array_unique($paths) as $path) {
            if ($disk->exists($path)) {
                $disk->delete($path);
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
     *
     * Delegates to AksesDokumen so the list, the download guard and the request
     * workflow all agree on one rule set.
     */
    public function canAccessFile(Arsip $arsip, User $user): bool
    {
        return AksesDokumen::canAccess($arsip, $user);
    }

    /**
     * Alias method for canAccessFile to satisfy canUserAccessArsip requirement.
     */
    public function canUserAccessArsip(Arsip $arsip, User $user): bool
    {
        return $this->canAccessFile($arsip, $user);
    }

    /**
     * Build divisi tree. For Operator/Staf TU only their own unit is returned.
     */
    private function divisiTree(bool $selfOnly = false): array
    {
        if (! $selfOnly) {
            return MasterData::divisiTree();
        }

        $user = auth()->user();

        return Divisi::where('id', $user->divisi_id)->get()->map(fn ($d) => [
            'id' => $d->id,
            'name' => $d->name,
            'kode' => $d->kode,
            'children' => [],
        ])->all();
    }

    /**
     * Generate a unique auto nomor_arsip (ARS-{KODE}-{tahun}-{random}).
     */
    private function generateNomorArsip($divisiId, $tahun, $studyProgramId = null): string
    {
        $kode = 'ARS';
        if ($divisiId) {
            $divisi = Divisi::find($divisiId);
            $kode = $divisi?->kode ?: 'ARS';
        } elseif ($studyProgramId) {
            $prodi = StudyProgram::find($studyProgramId);
            $kode = $prodi?->kode ?: 'PRODI';
        }

        do {
            $nomor = 'ARS-'.strtoupper($kode).'-'.$tahun.'-'.strtoupper(Str::random(6));
        } while (Arsip::where('nomor_arsip', $nomor)->exists());

        return $nomor;
    }
}
