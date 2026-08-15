<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\StudyProgram;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with STT master data.
     */
    public function run(): void
    {
        // 1. Seed Roles
        $roles = [
            'Superadmin',
            'Operator',
            'Karyawan',
            'Mahasiswa',
            'Dosen',
            'Staf TU',
            'Kaprodi',
            'Dekan',
        ];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Seed Master Data Program Studi (study_programs)
        $studyPrograms = [
            ['kode' => 'TEOL', 'name' => 'S1 Teologi'],
            ['kode' => 'PAK', 'name' => 'S1 Pendidikan Agama Kristen (PAK)'],
            ['kode' => 'MTEOL', 'name' => 'S2 Magister Teologi'],
            ['kode' => 'UMUM', 'name' => 'Non-Prodi / Umum / Institutional'],
        ];
        foreach ($studyPrograms as $program) {
            StudyProgram::firstOrCreate(['kode' => $program['kode']], ['name' => $program['name']]);
        }

        // 3. Seed Master Data Unit / Divisi / Bagian (departments)
        $departments = [
            ['kode' => 'PIMP', 'name' => 'Pimpinan / Rektorat / Ketua STT'],
            ['kode' => 'BAAK', 'name' => 'BAAK (Bagian Administrasi Akademik & Kemahasiswaan)'],
            ['kode' => 'BAUK', 'name' => 'BAUK (Bagian Administrasi Umum & Keuangan)'],
            ['kode' => 'LPM', 'name' => 'LPM (Lembaga Penjaminan Mutu)'],
            ['kode' => 'LPPM', 'name' => 'LPPM (Lembaga Penelitian & Pengabdian Masyarakat)'],
            ['kode' => 'TEOL', 'name' => 'Prodi S1 Teologi'],
            ['kode' => 'PAK', 'name' => 'Prodi S1 Pendidikan Agama Kristen'],
            ['kode' => 'MTEOL', 'name' => 'Prodi S2 Magister Teologi'],
            ['kode' => 'PERPUS', 'name' => 'Perpustakaan / Sumber Pustaka'],
            ['kode' => 'ASRAMA', 'name' => 'Pengelola Asrama & Layanan Kemahasiswaan'],
        ];
        $divisiIds = [];
        foreach ($departments as $dept) {
            $divisi = Divisi::firstOrCreate(['kode' => $dept['kode']], ['name' => $dept['name']]);
            $divisiIds[$dept['kode']] = $divisi->id;
        }

        // 4. Seed Master Data Kategori & Sub-Kategori Dokumen
        $categories = [
            [
                'kode' => 'ADM',
                'name' => 'A. Surat-Menyurat & Administrasi Usaha',
                'children' => [
                    ['kode' => 'SM-MASUK', 'name' => 'Surat Masuk'],
                    ['kode' => 'SM-KELUAR', 'name' => 'Surat Keluar'],
                    ['kode' => 'ND', 'name' => 'Nota Dinas / Memo Internal'],
                    ['kode' => 'SK', 'name' => 'Surat Keputusan (SK)'],
                    ['kode' => 'ST-SPPD', 'name' => 'Surat Tugas & SPPD'],
                ],
            ],
            [
                'kode' => 'AKD',
                'name' => 'B. Dokumen Akademik & Pengajaran',
                'children' => [
                    ['kode' => 'RPS', 'name' => 'Kurikulum & Silabus / RPS'],
                    ['kode' => 'MODUL', 'name' => 'Bahan Ajar & Modul'],
                    ['kode' => 'YUDISIUM', 'name' => 'Yudisium & Kelulusan'],
                    ['kode' => 'TA', 'name' => 'Tugas Akhir / Skripsi / Tesis'],
                ],
            ],
            [
                'kode' => 'MUTU',
                'name' => 'C. Dokumen Mutu & Legalitas',
                'children' => [
                    ['kode' => 'AKRED', 'name' => 'Akreditasi (BAN-PT / LAM)'],
                    ['kode' => 'KERJASAMA', 'name' => 'Kerja Sama / MoU / MoA'],
                    ['kode' => 'SOP', 'name' => 'Pedoman & SOP'],
                ],
            ],
            [
                'kode' => 'KEU',
                'name' => 'D. Keuangan, SDM & Aset',
                'children' => [
                    ['kode' => 'LAPKEU', 'name' => 'Laporan Keuangan & Audit'],
                    ['kode' => 'SDM', 'name' => 'Dokumen SDM / Kepegawaian'],
                    ['kode' => 'ASET', 'name' => 'Aset & Inventaris'],
                ],
            ],
        ];
        foreach ($categories as $group) {
            $parent = KategoriArsip::firstOrCreate(['kode' => $group['kode']], ['name' => $group['name']]);
            foreach ($group['children'] as $child) {
                KategoriArsip::firstOrCreate(['kode' => $child['kode']], [
                    'name' => $child['name'],
                    'parent_id' => $parent->id,
                ]);
            }
        }

        // 5. Seed Users

        // Superadmin (Full Access)
        $superadmin = User::updateOrCreate(
            ['email' => 'admin@sttni.ac.id'],
            [
                'name' => 'Super Admin STT',
                'password' => Hash::make('admin123'),
                'divisi_id' => null,
                'nik_nim' => 'ADM-001',
                'status_akun' => 'Aktif',
            ]
        );
        $superadmin->assignRole('Superadmin');

        // Operators (Staf TU is seeded below)
        $operatorBAAK = User::updateOrCreate(
            ['email' => 'operator_akademik@sttni.ac.id'],
            [
                'name' => 'Operator BAAK',
                'password' => Hash::make('operator123'),
                'divisi_id' => $divisiIds['BAAK'],
                'nik_nim' => 'OP-001',
                'status_akun' => 'Aktif',
            ]
        );
        $operatorBAAK->assignRole('Operator');

        $operatorBAUK = User::updateOrCreate(
            ['email' => 'operator_keuangan@sttni.ac.id'],
            [
                'name' => 'Operator BAUK',
                'password' => Hash::make('operator123'),
                'divisi_id' => $divisiIds['BAUK'],
                'nik_nim' => 'OP-002',
                'status_akun' => 'Aktif',
            ]
        );
        $operatorBAUK->assignRole('Operator');

        // Karyawan
        $karyawan1 = User::updateOrCreate(
            ['email' => 'karyawan1@sttni.ac.id'],
            [
                'name' => 'Budi Santoso (Asrama & Kemahasiswaan)',
                'password' => Hash::make('karyawan123'),
                'divisi_id' => $divisiIds['ASRAMA'],
                'nik_nim' => 'KY-001',
                'status_akun' => 'Aktif',
            ]
        );
        $karyawan1->assignRole('Karyawan');

        $karyawan2 = User::updateOrCreate(
            ['email' => 'karyawan2@sttni.ac.id'],
            [
                'name' => 'Siti Aminah (BAAK)',
                'password' => Hash::make('karyawan123'),
                'divisi_id' => $divisiIds['BAAK'],
                'nik_nim' => 'KY-002',
                'status_akun' => 'Aktif',
            ]
        );
        $karyawan2->assignRole('Karyawan');

        // Staf TU
        $stafftu = User::updateOrCreate(
            ['email' => 'stafftu@sttni.ac.id'],
            [
                'name' => 'Rina Staf TU BAAK',
                'password' => Hash::make('staff123'),
                'divisi_id' => $divisiIds['BAAK'],
                'nik_nim' => 'TU-001',
                'status_akun' => 'Aktif',
            ]
        );
        $stafftu->assignRole('Staf TU');

        // Kaprodi
        $kaprodi = User::updateOrCreate(
            ['email' => 'kaprodi.teologi@sttni.ac.id'],
            [
                'name' => 'Dr. Kaprodi S1 Teologi',
                'password' => Hash::make('kaprodi123'),
                'divisi_id' => $divisiIds['TEOL'],
                'nik_nim' => 'KP-001',
                'status_akun' => 'Aktif',
            ]
        );
        $kaprodi->assignRole('Kaprodi');

        // Ketua / Pimpinan (role: Dekan)
        $ketua = User::updateOrCreate(
            ['email' => 'ketua.stt@sttni.ac.id'],
            [
                'name' => 'Pimpinan / Ketua STT',
                'password' => Hash::make('dekan123'),
                'divisi_id' => $divisiIds['PIMP'],
                'nik_nim' => 'DK-001',
                'status_akun' => 'Aktif',
            ]
        );
        $ketua->assignRole('Dekan');

        // Dosen
        $dosen = User::updateOrCreate(
            ['email' => 'dosen.teologi@sttni.ac.id'],
            [
                'name' => 'Bpk. Dosen S1 Teologi',
                'password' => Hash::make('dosen123'),
                'divisi_id' => $divisiIds['TEOL'],
                'nik_nim' => 'DS-001',
                'status_akun' => 'Aktif',
            ]
        );
        $dosen->assignRole('Dosen');

        // Mahasiswa
        $mahasiswa = User::updateOrCreate(
            ['email' => 'mahasiswa@sttni.ac.id'],
            [
                'name' => 'Mahasiswa S1 Teologi',
                'password' => Hash::make('mahasiswa123'),
                'divisi_id' => $divisiIds['TEOL'],
                'nik_nim' => '2310001',
                'status_akun' => 'Aktif',
            ]
        );
        $mahasiswa->assignRole('Mahasiswa');
    }
}
