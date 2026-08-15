<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\StudyProgram;
use App\Models\Ukm;
use App\Models\UkmAnggota;
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
            'Sie Kesiswaan',
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
            [
                'kode' => 'KEMA',
                'name' => 'E. Kemahasiswaan & Kegiatan Mahasiswa',
                'children' => [
                    ['kode' => 'PRESENSI', 'name' => 'Presensi & Rekap UKM'],
                    ['kode' => 'SK-BEM', 'name' => 'Kepengurusan BEM / Senat Mahasiswa'],
                    ['kode' => 'PRESTASI', 'name' => 'Prestasi & Penghargaan Mahasiswa'],
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

        // Sie Kesiswaan (pengisi presensi UKM)
        $sieKesiswaan = User::updateOrCreate(
            ['email' => 'sie.kesiswaan@sttni.ac.id'],
            [
                'name' => 'Sie Kesiswaan STTNI',
                'password' => Hash::make('kesiswaan123'),
                'divisi_id' => $divisiIds['BAAK'],
                'nik_nim' => 'SK-001',
                'status_akun' => 'Aktif',
            ]
        );
        $sieKesiswaan->assignRole('Sie Kesiswaan');

        // 6. Seed Master Data UKM & Anggota
        $ukmData = [
            [
                'kode' => 'UKM-PS',
                'name' => 'UKM Paduan Suara',
                'pembina' => 'Bpk. Daniel', 
                'ketua' => 'Maria S.',
                'divisi_id' => $divisiIds['BAAK'],
                'anggota' => [
                    ['nama' => 'Maria S.', 'nim' => '2310002', 'jabatan' => 'Ketua'],
                    ['nama' => 'Yohanes K.', 'nim' => '2310003', 'jabatan' => 'Sekretaris'],
                    ['nama' => 'Ruth W.', 'nim' => '2310004', 'jabatan' => 'Anggota'],
                    ['nama' => 'Titus L.', 'nim' => '2310005', 'jabatan' => 'Anggota'],
                    ['nama' => 'Esther P.', 'nim' => '2310006', 'jabatan' => 'Anggota'],
                ],
            ],
            [
                'kode' => 'UKM-OR',
                'name' => 'UKM Olahraga',
                'pembina' => 'Bpk. Yosua',
                'ketua' => 'Andreas M.',
                'divisi_id' => $divisiIds['BAAK'],
                'anggota' => [
                    ['nama' => 'Andreas M.', 'nim' => '2310007', 'jabatan' => 'Ketua'],
                    ['nama' => 'Timotius R.', 'nim' => '2310008', 'jabatan' => 'Wakil Ketua'],
                    ['nama' => 'Debora S.', 'nim' => '2310009', 'jabatan' => 'Anggota'],
                    ['nama' => 'Stefanus B.', 'nim' => '2310010', 'jabatan' => 'Anggota'],
                    ['nama' => 'Lidya N.', 'nim' => '2310011', 'jabatan' => 'Anggota'],
                ],
            ],
            [
                'kode' => 'UKM-MU',
                'name' => 'UKM Musik & Multimedia',
                'pembina' => 'Bpk. Petrus',
                'ketua' => 'Gabriel T.',
                'divisi_id' => $divisiIds['BAAK'],
                'anggota' => [
                    ['nama' => 'Gabriel T.', 'nim' => '2310012', 'jabatan' => 'Ketua'],
                    ['nama' => 'Salomo H.', 'nim' => '2310013', 'jabatan' => 'Anggota'],
                    ['nama' => 'Rafael W.', 'nim' => '2310014', 'jabatan' => 'Anggota'],
                    ['nama' => 'Dorcas P.', 'nim' => '2310015', 'jabatan' => 'Anggota'],
                ],
            ],
            [
                'kode' => 'UKM-ROH',
                'name' => 'UKM Kerohanian',
                'pembina' => 'Bpk. Yakobus',
                'ketua' => 'Nathanael K.',
                'divisi_id' => $divisiIds['BAAK'],
                'anggota' => [
                    ['nama' => 'Nathanael K.', 'nim' => '2310016', 'jabatan' => 'Ketua'],
                    ['nama' => 'Hana S.', 'nim' => '2310017', 'jabatan' => 'Sekretaris'],
                    ['nama' => 'Eben E.', 'nim' => '2310018', 'jabatan' => 'Anggota'],
                    ['nama' => 'Miryam P.', 'nim' => '2310019', 'jabatan' => 'Anggota'],
                    ['nama' => 'Zakheus T.', 'nim' => '2310020', 'jabatan' => 'Anggota'],
                ],
            ],
        ];

        foreach ($ukmData as $data) {
            $anggotaList = $data['anggota'];
            unset($data['anggota']);
            $ukm = Ukm::firstOrCreate(['kode' => $data['kode']], $data);

            foreach ($anggotaList as $anggota) {
                UkmAnggota::firstOrCreate(
                    ['ukm_id' => $ukm->id, 'nama' => $anggota['nama']],
                    $anggota
                );
            }
        }
    }
}
