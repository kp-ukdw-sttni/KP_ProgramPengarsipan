<?php

use App\Models\Divisi;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Selaraskan Unit Kerja (tabel divisi) dengan struktur organisasi STTNI.
 *
 * Sumber: struktur organisasi STTNI (Wakil Ketua I–IV, Unit Perpustakaan,
 * dan LPM).
 *
 * Keputusan yang dipakai:
 * 1. Prodi TIDAK menjadi divisi. Prodi tetap hanya direferensikan lewat tabel
 *    study_programs — konsisten dengan migration
 *    2026_09_21_034751_remove_prodi_from_divisi_table. Entri
 *    "Kaprodi Teologi" dsb. di bawah adalah unit/jabatan pengusul arsip,
 *    bukan data Prodi.
 * 2. Pembina dan Ketua tidak dijadikan divisi (permintaan pemilik data).
 * 3. Struktur dibatasi 2 tingkat karena MasterData::divisiTree() hanya
 *    merender parent + anak, dan getExplorerData() hanya menghitung arsip
 *    pada divisi daun. Tingkat "Sekretaris" karena itu tidak dimodelkan.
 * 4. Daftar Dosen bukan unit kerja, jadi tidak dimodelkan sebagai divisi.
 *
 * CATATAN KODE: kolom `kode` sengaja TIDAK diubah meski nama layar berubah.
 * 'KMS' masih dipakai literal di App\Support\PresensiAccess::PEMILIK_KODE untuk
 * menentukan siapa yang boleh mengisi presensi, dan StudyProgram memakai kode
 * TEOL/PAK/MTEOL yang beririsan. Menyamakan kode adalah pekerjaan terpisah.
 *
 * Divisi top-level baru WAJIB punya kode: MasterData::divisiTree() menyatukan
 * seluruh top-level dengan top-level tanpa kode, sehingga entri tanpa kode
 * akan tampil dua kali di form arsip.
 */
return new class extends Migration
{
    /** Kode lama => nama baru. */
    private array $renames = [
        'BAAK'   => 'WK I Akademik',
        'BAUK'   => 'WK II Keu-Sar-Peg',
        'KMS'    => 'WK III Kemahasiswaan dan Alumni',
        'PERPUS' => 'Unit Perpustakaan',
    ];

    /** Nama lama, dipakai oleh down(). */
    private array $originalNames = [
        'BAAK'   => 'BAAK (Bagian Administrasi Akademik & Kemahasiswaan)',
        'BAUK'   => 'BAUK (Bagian Administrasi Umum & Keuangan)',
        'KMS'    => 'Bagian Kemahasiswaan & Pelayanan Mahasiswa',
        'PERPUS' => 'Perpustakaan & Sumber Pustaka',
    ];

    /** Kode anak => [kode parent, nama anak]. */
    private array $children = [
        // WK I Akademik — eselon: Kaprodi tiap prodi
        'KAPRODI-TEOL' => ['BAAK', 'Kaprodi Teologi'],
        'KAPRODI-PAK'  => ['BAAK', 'Kaprodi PAK'],
        'KAPRODI-MTEOL' => ['BAAK', 'Kaprodi Magister Teologi'],

        // WK II Keu-Sar-Peg
        'KASUB-ADMAK' => ['BAUK', 'Ka Sub Bid Administrasi Akademik'],
        'KASUB-KEU'   => ['BAUK', 'Ka Sub Bid Keuangan'],
        'KASUB-UMUM'  => ['BAUK', 'Ka Sub Bid Umum'],

        // WK III Kemahasiswaan dan Alumni
        'KASUB-KEM'  => ['KMS', 'Ka Sub Bid Kemahasiswaan'],
        'KASUB-ALUM' => ['KMS', 'Ka Sub Bid Alumni'],

        // WK IV Kerjasama Eksternal
        'KASUB-KERJASAMA'  => ['WKIV', 'Ka Sub Bid Kerjasama dan Pengembangan'],
        'KASUB-PENDIDIKAN' => ['WKIV', 'Ka Sub Bid Pendidikan Berkelanjutan'],
    ];

    /** Unit kerja baru di level atas. */
    private array $newParents = [
        'WKIV' => 'WK IV Kerjasama Eksternal',
    ];

    public function up(): void
    {
        // 1. Pastikan level atas hasil rename ada dan jadi root.
        foreach ($this->renames as $kode => $name) {
            $divisi = $this->ensureDivisi($kode, $name);
            $divisi->update(['name' => $name, 'parent_id' => null]);
        }

        // 2. Unit kerja level atas yang baru.
        foreach ($this->newParents as $kode => $name) {
            $divisi = $this->ensureDivisi($kode, $name);
            $divisi->update(['name' => $name, 'parent_id' => null]);
        }

        // 3. Unit kerja anak, dipasang di bawah induknya.
        foreach ($this->children as $childKode => [$parentKode, $childName]) {
            $parentId = DB::table('divisi')->where('kode', $parentKode)->value('id');

            if (! $parentId) {
                throw new \RuntimeException("Divisi induk '{$parentKode}' tidak ditemukan, anak '{$childKode}' tidak bisa dibuat.");
            }

            $this->ensureDivisi($childKode, $childName)->update([
                'name'      => $childName,
                'parent_id' => $parentId,
            ]);
        }
    }

    public function down(): void
    {
        // 1. Kembalikan nama lama.
        foreach ($this->originalNames as $kode => $name) {
            DB::table('divisi')->where('kode', $kode)->update(['name' => $name, 'updated_at' => now()]);
        }

        // 2. Buang unit kerja anak dan WK IV.
        $added = array_merge(array_keys($this->children), array_keys($this->newParents));

        DB::table('divisi')->whereIn('kode', $added)->delete();
    }

    /**
     * Buat divisi bila kode belum ada, lalu kembalikan modelnya.
     */
    private function ensureDivisi(string $kode, string $name): Divisi
    {
        return Divisi::firstOrCreate(['kode' => $kode], ['name' => $name]);
    }
};
