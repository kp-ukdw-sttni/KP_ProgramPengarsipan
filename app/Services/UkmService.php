<?php

namespace App\Services;

use App\Models\Divisi;
use App\Models\Ukm;
use App\Models\UkmAnggota;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class UkmService
{
    /**
     * List UKM with member & attendance counts.
     */
    public function getIndexData(): array
    {
        return [
            'ukm' => Ukm::withCount(['anggota', 'presensi'])
                ->with('divisi')
                ->orderBy('name')
                ->paginate(10)
                ->withQueryString(),
            'divisi' => Divisi::orderBy('name')->get(['id', 'name', 'kode']),
        ];
    }

    /**
     * Data for the create form.
     */
    public function getCreateData(): array
    {
        return [
            'divisi' => Divisi::orderBy('name')->get(['id', 'name', 'kode']),
        ];
    }

    /**
     * Data for the edit form + member list.
     */
    public function getEditData(Ukm $ukm): array
    {
        return [
            'ukm' => $ukm->load(['divisi', 'anggota']),
            'divisi' => Divisi::orderBy('name')->get(['id', 'name', 'kode']),
        ];
    }

    /**
     * Create a new UKM.
     */
    public function store(Request $request): Ukm
    {
        return Ukm::create([
            'kode' => $request->kode ?: $this->generateKode($request->name),
            'name' => $request->name,
            'deskripsi' => $request->deskripsi,
            'pembina' => $request->pembina,
            'ketua' => $request->ketua,
            'divisi_id' => $request->divisi_id ?: null,
            'status' => $request->status,
        ]);
    }

    /**
     * Update an existing UKM.
     */
    public function update(Request $request, Ukm $ukm): void
    {
        $ukm->update([
            'kode' => $request->kode ?: $ukm->kode,
            'name' => $request->name,
            'deskripsi' => $request->deskripsi,
            'pembina' => $request->pembina,
            'ketua' => $request->ketua,
            'divisi_id' => $request->divisi_id ?: null,
            'status' => $request->status,
        ]);
    }

    /**
     * Soft-deny hard delete: simply remove the UKM (presensi rows cascade).
     */
    public function destroy(Ukm $ukm): void
    {
        $ukm->delete();
    }

    /**
     * Add a member to a UKM.
     */
    public function storeAnggota(Request $request, Ukm $ukm): UkmAnggota
    {
        return $ukm->anggota()->create([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'jabatan' => $request->jabatan,
            'status_keanggotaan' => $request->status_keanggotaan ?? 'Aktif',
        ]);
    }

    /**
     * Update a member.
     */
    public function updateAnggota(Request $request, UkmAnggota $anggota): void
    {
        $anggota->update([
            'nama' => $request->nama,
            'nim' => $request->nim,
            'jabatan' => $request->jabatan,
            'status_keanggotaan' => $request->status_keanggotaan ?? $anggota->status_keanggotaan,
        ]);
    }

    /**
     * Remove a member.
     */
    public function destroyAnggota(UkmAnggota $anggota): void
    {
        $anggota->delete();
    }

    /**
     * Auto-generate a unique UKM kode.
     */
    private function generateKode(string $name): string
    {
        $base = 'UKM-'.strtoupper(Str::slug($name, ''));

        do {
            $kode = $base.'-'.strtoupper(Str::random(3));
        } while (Ukm::where('kode', $kode)->exists());

        return $kode;
    }
}
