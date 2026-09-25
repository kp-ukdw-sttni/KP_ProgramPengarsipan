<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Hapus entri Program Studi (Prodi) dari tabel divisi.
 *
 * Latar belakang: Master data Unit Kerja (divisi) sebelumnya mengandung
 * entri dengan nama Prodi (TEOL, PAK, MTEOL) yang duplikat dengan tabel
 * study_programs. Sesuai keputusan arsitektur data, penunjukan Prodi
 * HANYA dilakukan melalui tabel study_programs, bukan divisi.
 *
 * Migration ini:
 * 1. Memindahkan user yang masih terikat ke divisi Prodi → ke divisi BAAK.
 * 2. Memindahkan arsip yang masih terikat ke divisi Prodi → ke divisi BAAK.
 * 3. Menghapus entri Prodi dari tabel divisi.
 */
return new class extends Migration
{
    /** Kode divisi Prodi yang akan dihapus */
    private array $prodiKodes = ['TEOL', 'PAK', 'MTEOL'];

    public function up(): void
    {
        // Resolve ID divisi BAAK sebagai tujuan reassign
        $baakId = DB::table('divisi')->where('kode', 'BAAK')->value('id');

        if (! $baakId) {
            // Jika BAAK belum ada, buat terlebih dahulu
            $baakId = DB::table('divisi')->insertGetId([
                'kode'       => 'BAAK',
                'name'       => 'BAAK (Bagian Administrasi Akademik & Kemahasiswaan)',
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Ambil ID semua divisi Prodi yang akan dihapus
        $prodiIds = DB::table('divisi')
            ->whereIn('kode', $this->prodiKodes)
            ->pluck('id')
            ->toArray();

        if (empty($prodiIds)) {
            return; // Sudah bersih, tidak ada yang perlu dihapus
        }

        // 1. Pindahkan User → BAAK
        DB::table('users')
            ->whereIn('divisi_id', $prodiIds)
            ->update(['divisi_id' => $baakId]);

        // 2. Pindahkan Arsip → BAAK (hindari data arsip yatim piatu)
        DB::table('arsip')
            ->whereIn('divisi_id', $prodiIds)
            ->update(['divisi_id' => $baakId]);

        // 3. Hapus entri divisi Prodi
        DB::table('divisi')
            ->whereIn('kode', $this->prodiKodes)
            ->delete();
    }

    public function down(): void
    {
        // Kembalikan entri Prodi ke tabel divisi (tanpa reassign user/arsip)
        $prodiEntries = [
            ['kode' => 'TEOL',  'name' => 'Prodi S1 Teologi'],
            ['kode' => 'PAK',   'name' => 'Prodi S1 Pendidikan Agama Kristen'],
            ['kode' => 'MTEOL', 'name' => 'Prodi S2 Magister Teologi'],
        ];

        foreach ($prodiEntries as $entry) {
            DB::table('divisi')->insertOrIgnore([
                'kode'       => $entry['kode'],
                'name'       => $entry['name'],
                'parent_id'  => null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
};
