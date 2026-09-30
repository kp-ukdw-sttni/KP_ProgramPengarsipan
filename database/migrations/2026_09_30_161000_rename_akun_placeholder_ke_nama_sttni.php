<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Ganti nama akun placeholder dengan nama asli dari struktur leadership STTNI.
 *
 * Latar belakang: seed awal memakai nama fiktif ("Dr. Stefanus Kristianto",
 * "Pdt. Samuel Hendra", dst.) yang tidak ada di daftar resmi. Akun di-update
 * in-place berdasarkan email agar id, arsip, peminjaman, dan presensi yang
 * sudah menunjuk user_id tidak ikut rusak.
 *
 * Email sengaja TIDAK diganti: alamat email asli tidak tersedia, dan membuat
 * email dari nama berarti mengarang alamat yang mungkin tidak pernah ada.
 * nik_nim juga dikosongkan karena DSN-001/STF-001/ADM-001 bukan NIP asli.
 *
 * Hanya kolom name, divisi_id, dan nik_nim yang disentuh. Password, role,
 * status_akun, dan email tidak berubah.
 *
 * Depends on: 2026_09_30_160000_selaraskan_divisi_dengan_struktur_sttni
 * (divisi anak KAPRODI-* harus sudah ada).
 */
return new class extends Migration
{
    /**
     * email => [nama baru, kode divisi baru, nik_nim lama].
     * null pada nik_nim lama berarti kolomnya sebelumnya kosong.
     */
    private array $mapping = [
        'admin@sttni.ac.id' => [
            'Administrator Pengarsipan',
            'PIMP',
            'ADM-001',
        ],
        'dosen.kemahasiswaan@sttni.ac.id' => [
            'Sapto Sunariyanti, M.Th.',
            'KMS',
            'DSN-003',
        ],
        'dosen.teologi@sttni.ac.id' => [
            'Darmanto, M.Th.',
            'KAPRODI-TEOL',
            'DSN-001',
        ],
        'dosen.pak@sttni.ac.id' => [
            'Dr. Ramses Simanjuntak, M.Pd.K.',
            'KAPRODI-PAK',
            'DSN-002',
        ],
        'staf.akademik@sttni.ac.id' => [
            'Dr. Janwardi, MA, M.Mis.',
            'KAPRODI-MTEOL',
            'STF-001',
        ],
    ];

    /** Nilai lama untuk down(). */
    private array $originals = [
        'admin@sttni.ac.id' => ['Administrator Pengarsipan', 'PIMP'],
        'dosen.kemahasiswaan@sttni.ac.id' => ['Pdt. Samuel Hendra, M.Th. (Kemahasiswaan)', 'KMS'],
        'dosen.teologi@sttni.ac.id' => ['Dr. Stefanus Kristianto, M.Th.', 'BAAK'],
        'dosen.pak@sttni.ac.id' => ['Dr. Maria Natalia, M.Pd.K.', 'BAAK'],
        'staf.akademik@sttni.ac.id' => ['Rina Wijaya, S.Kom. (Staf BAAK)', 'BAAK'],
    ];

    public function up(): void
    {
        foreach ($this->mapping as $email => [$name, $divisiKode, $oldNik]) {
            $user = DB::table('users')->where('email', $email)->first();

            // Migration ini hanya untuk akun yang sudah di-seed. Pada database tanpa
            // seed (mis. RefreshDatabase di test) tidak ada akun untuk diubah,
            // jadi dilewati — nama & divisi yang benar sudah dibuat seeder.
            if (! $user) {
                continue;
            }

            $divisiId = DB::table('divisi')->where('kode', $divisiKode)->value('id');

            DB::table('users')->where('id', $user->id)->update([
                'name'       => $name,
                // Kalau divisi belum ada, jangan dikosongkan — pertahankan yang lama.
                'divisi_id'  => $divisiId ?? $user->divisi_id,
                'nik_nim'    => null,
                'updated_at' => now(),
            ]);
        }
    }

    public function down(): void
    {
        foreach ($this->originals as $email => [$name, $divisiKode]) {
            $user = DB::table('users')->where('email', $email)->first();

            if (! $user) {
                continue;
            }

            $divisiId = DB::table('divisi')->where('kode', $divisiKode)->value('id');

            DB::table('users')->where('id', $user->id)->update([
                'name'       => $name,
                'divisi_id'  => $divisiId ?? $user->divisi_id,
                'updated_at' => now(),
            ]);
        }

        // Kembalikan placeholder nik_nim. Unik, jadi satu per satu.
        foreach ($this->mapping as $email => [$name, $divisiKode, $oldNik]) {
            if ($oldNik) {
                DB::table('users')->where('email', $email)->update([
                    'nik_nim'    => $oldNik,
                    'updated_at' => now(),
                ]);
            }
        }
    }
};
