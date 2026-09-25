<?php

namespace Database\Seeders;

use App\Models\Arsip;
use App\Models\ArsipVersion;
use App\Models\AuditLog;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\Peminjaman;
use App\Models\PresensiUkm;
use App\Models\PresensiUkmDetail;
use App\Models\StudyProgram;
use App\Models\Ukm;
use App\Models\UkmAnggota;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database with internal campus master data and clean dummy data.
     */
    public function run(): void
    {
        // 1. Seed Roles (Peran Pengguna Murni Internal Kampus)
        $roles = [
            'Admin',
            'Dosen',
        ];
        foreach ($roles as $roleName) {
            Role::firstOrCreate(['name' => $roleName, 'guard_name' => 'web']);
        }

        // 2. Seed Master Data Program Studi (Prodi)
        $studyPrograms = [
            ['kode' => 'TEOL', 'name' => 'S1 Teologi'],
            ['kode' => 'PAK', 'name' => 'S1 Pendidikan Agama Kristen (PAK)'],
            ['kode' => 'MTEOL', 'name' => 'S2 Magister Teologi'],
            ['kode' => 'UMUM', 'name' => 'Non-Prodi / Umum / Institusional'],
        ];
        $prodiMap = [];
        foreach ($studyPrograms as $program) {
            $sp = StudyProgram::firstOrCreate(['kode' => $program['kode']], ['name' => $program['name']]);
            $prodiMap[$program['kode']] = $sp->id;
        }

        // 3. Seed Master Data Unit Kerja (Divisi/Bagian Administrasi & Struktural)
        // PENTING: Unit Kerja HANYA berisi unit administrasi/struktural.
        // Nama Program Studi (Prodi) TIDAK dimasukkan di sini — lihat tabel study_programs.
        $departments = [
            ['kode' => 'PIMP',   'name' => 'Pimpinan / Rektorat / Ketua STT'],
            ['kode' => 'BAAK',   'name' => 'BAAK (Bagian Administrasi Akademik & Kemahasiswaan)'],
            ['kode' => 'BAUK',   'name' => 'BAUK (Bagian Administrasi Umum & Keuangan)'],
            ['kode' => 'KMS',    'name' => 'Bagian Kemahasiswaan & Pelayanan Mahasiswa'],
            ['kode' => 'LPM',    'name' => 'LPM (Lembaga Penjaminan Mutu)'],
            ['kode' => 'LPPM',   'name' => 'LPPM (Lembaga Penelitian & Pengabdian Masyarakat)'],
            ['kode' => 'PERPUS', 'name' => 'Perpustakaan & Sumber Pustaka'],
        ];
        $divisiIds = [];
        foreach ($departments as $dept) {
            $divisi = Divisi::firstOrCreate(
                ['kode' => $dept['kode']],
                ['name' => $dept['name']]
            );
            $divisiIds[$dept['kode']] = $divisi->id;
        }

        // 4. Seed Master Data Jenis Dokumen (Kategori Dokumen)
        $categories = [
            [
                'kode' => 'ADM',
                'name' => 'A. Surat-Menyurat & Administrasi',
                'children' => [
                    ['kode' => 'SM-MASUK', 'name' => 'Surat Masuk'],
                    ['kode' => 'SM-KELUAR', 'name' => 'Surat Keluar'],
                    ['kode' => 'SK', 'name' => 'Surat Keputusan (SK) Pimpinan'],
                    ['kode' => 'NOTULEN', 'name' => 'Notulen Rapat Staff & Dosen'],
                ],
            ],
            [
                'kode' => 'AKD',
                'name' => 'B. Dokumen Akademik & Pengajaran',
                'children' => [
                    ['kode' => 'RPS', 'name' => 'Kurikulum & Silabus / RPS'],
                    ['kode' => 'MODUL', 'name' => 'Bahan Ajar & Modul Kuliah'],
                    ['kode' => 'YUDISIUM', 'name' => 'Yudisium & Kelulusan'],
                    ['kode' => 'TA', 'name' => 'Tugas Akhir / Skripsi / Tesis'],
                ],
            ],
            [
                'kode' => 'MUTU',
                'name' => 'C. Dokumen Mutu & Legalitas',
                'children' => [
                    ['kode' => 'AKRED', 'name' => 'Akreditasi (BAN-PT / LAMDIK)'],
                    ['kode' => 'KERJASAMA', 'name' => 'Kerja Sama / MoU / MoA'],
                    ['kode' => 'SOP', 'name' => 'Pedoman, Statuta & SOP'],
                ],
            ],
            [
                'kode' => 'KEU',
                'name' => 'D. Keuangan, SDM & Aset',
                'children' => [
                    ['kode' => 'LAPKEU', 'name' => 'Laporan Keuangan & Audit'],
                    ['kode' => 'SDM', 'name' => 'Dokumen SDM / Kepegawaian Dosen'],
                    ['kode' => 'ASET', 'name' => 'Inventaris & Aset Kampus'],
                ],
            ],
            [
                'kode' => 'KEMHSW',
                'name' => 'E. Kemahasiswaan & Presensi Kegiatan',
                'children' => [
                    ['kode' => 'PRESENSI', 'name' => 'Rekap Presensi UKM & Kegiatan Mahasiswa'],
                    ['kode' => 'BEASISWA', 'name' => 'Beasiswa & Prestasi Mahasiswa'],
                    ['kode' => 'KEGIATAN-MHS', 'name' => 'Laporan Pertanggungjawaban (LPJ) Organisasi Mahasiswa'],
                ],
            ],
        ];
        $kategoriMap = [];
        foreach ($categories as $group) {
            $parent = KategoriArsip::firstOrCreate(['kode' => $group['kode']], ['name' => $group['name']]);
            $kategoriMap[$group['kode']] = $parent->id;
            foreach ($group['children'] as $child) {
                $c = KategoriArsip::firstOrCreate(['kode' => $child['kode']], [
                    'name' => $child['name'],
                    'parent_id' => $parent->id,
                ]);
                $kategoriMap[$child['kode']] = $c->id;
            }
        }

        // 5. Seed Users (Admin & Dosen / Staf Internal)
        $admin = User::updateOrCreate(
            ['email' => 'admin@sttni.ac.id'],
            [
                'name' => 'Administrator Pengarsipan',
                'password' => Hash::make('admin123'),
                'divisi_id' => $divisiIds['PIMP'],
                'nik_nim' => 'ADM-001',
                'status_akun' => 'Aktif',
            ]
        );
        $admin->syncRoles(['Admin']);

        $dosenKemahasiswaan = User::updateOrCreate(
            ['email' => 'dosen.kemahasiswaan@sttni.ac.id'],
            [
                'name' => 'Pdt. Samuel Hendra, M.Th. (Kemahasiswaan)',
                'password' => Hash::make('dosen123'),
                'divisi_id' => $divisiIds['KMS'],
                'nik_nim' => 'DSN-003',
                'status_akun' => 'Aktif',
            ]
        );
        $dosenKemahasiswaan->syncRoles(['Dosen']);

        $dosen1 = User::updateOrCreate(
            ['email' => 'dosen.teologi@sttni.ac.id'],
            [
                'name'        => 'Dr. Stefanus Kristianto, M.Th.',
                'password'    => Hash::make('dosen123'),
                'divisi_id'   => $divisiIds['BAAK'], // Dosen — unit administrasi akademik
                'nik_nim'     => 'DSN-001',
                'status_akun' => 'Aktif',
            ]
        );
        $dosen1->syncRoles(['Dosen']);

        $dosen2 = User::updateOrCreate(
            ['email' => 'dosen.pak@sttni.ac.id'],
            [
                'name'        => 'Dr. Maria Natalia, M.Pd.K.',
                'password'    => Hash::make('dosen123'),
                'divisi_id'   => $divisiIds['BAAK'], // Dosen — unit administrasi akademik
                'nik_nim'     => 'DSN-002',
                'status_akun' => 'Aktif',
            ]
        );
        $dosen2->syncRoles(['Dosen']);

        $stafInternal = User::updateOrCreate(
            ['email' => 'staf.akademik@sttni.ac.id'],
            [
                'name' => 'Rina Wijaya, S.Kom. (Staf BAAK)',
                'password' => Hash::make('dosen123'),
                'divisi_id' => $divisiIds['BAAK'],
                'nik_nim' => 'STF-001',
                'status_akun' => 'Aktif',
            ]
        );
        $stafInternal->syncRoles(['Dosen']);

        // 6. Seed Master Data UKM & Anggota untuk Presensi (Berkebun, Futsal, Musik)
        $ukmBerkebun = Ukm::firstOrCreate(
            ['kode' => 'UKM-KEBUN'],
            [
                'name' => 'UKM Berkebun & Pertanian Kampus',
                'deskripsi' => 'Pengembangan budidaya tanaman dan penghijauan lingkungan kampus STTNI.',
                'pembina' => 'Pdt. Samuel Hendra, M.Th.',
                'ketua' => 'Emanuel Kristianto',
                'divisi_id' => $divisiIds['KMS'],
                'status' => 'Aktif',
            ]
        );

        $ukmFutsal = Ukm::firstOrCreate(
            ['kode' => 'UKM-FUTSAL'],
            [
                'name' => 'UKM Futsal STTNI',
                'deskripsi' => 'Pembinaan kebugaran dan turnamen olahraga futsal mahasiswa STTNI.',
                'pembina' => 'Dr. Stefanus Kristianto, M.Th.',
                'ketua' => 'Samuel Wibowo',
                'divisi_id' => $divisiIds['KMS'],
                'status' => 'Aktif',
            ]
        );

        $ukmMusik = Ukm::firstOrCreate(
            ['kode' => 'UKM-MUSIK'],
            [
                'name' => 'UKM Musik & Paduan Suara STTNI',
                'deskripsi' => 'Pengembangan bakat musik gerejawi dan paduan suara mahasiswa STTNI.',
                'pembina' => 'Pdt. Samuel Hendra, M.Th.',
                'ketua' => 'Jonathan Siregar',
                'divisi_id' => $divisiIds['KMS'],
                'status' => 'Aktif',
            ]
        );

        $anggotaList = [
            // Anggota UKM Berkebun
            ['nama' => 'Emanuel Kristianto', 'nim' => '202301011', 'prodi' => 'S1 Teologi', 'jabatan' => 'Ketua', 'ukm_id' => $ukmBerkebun->id],
            ['nama' => 'Maria Kristina', 'nim' => '202301012', 'prodi' => 'S1 PAK', 'jabatan' => 'Sekretaris', 'ukm_id' => $ukmBerkebun->id],
            ['nama' => 'Yosua Pratama', 'nim' => '202301013', 'prodi' => 'S1 Teologi', 'jabatan' => 'Bendahara', 'ukm_id' => $ukmBerkebun->id],

            // Anggota UKM Futsal
            ['nama' => 'Samuel Wibowo', 'nim' => '202302002', 'prodi' => 'S1 Teologi', 'jabatan' => 'Ketua', 'ukm_id' => $ukmFutsal->id],
            ['nama' => 'Andreas Kurnia', 'nim' => '202302003', 'prodi' => 'S1 PAK', 'jabatan' => 'Kapten Tim', 'ukm_id' => $ukmFutsal->id],
            ['nama' => 'Daniel Setiawan', 'nim' => '202302005', 'prodi' => 'S1 Teologi', 'jabatan' => 'Anggota', 'ukm_id' => $ukmFutsal->id],

            // Anggota UKM Musik
            ['nama' => 'Jonathan Siregar', 'nim' => '202301001', 'prodi' => 'S1 Teologi', 'jabatan' => 'Ketua', 'ukm_id' => $ukmMusik->id],
            ['nama' => 'Yohana Christine', 'nim' => '202301002', 'prodi' => 'S1 PAK', 'jabatan' => 'Sekretaris', 'ukm_id' => $ukmMusik->id],
            ['nama' => 'Ruth Simanjuntak', 'nim' => '202301004', 'prodi' => 'S1 Teologi', 'jabatan' => 'Anggota', 'ukm_id' => $ukmMusik->id],
        ];

        foreach ($anggotaList as $agt) {
            UkmAnggota::firstOrCreate(
                ['nim' => $agt['nim'], 'ukm_id' => $agt['ukm_id']],
                [
                    'nama' => $agt['nama'],
                    'prodi' => $agt['prodi'] ?? 'S1 Teologi',
                    'jabatan' => $agt['jabatan'],
                    'status_keanggotaan' => 'Aktif',
                ]
            );
        }

        // 7. Seed Berkas / Arsip Dokumen Kampus
        $arsipDummy = [
            [
                'nomor_arsip' => 'ARS-2026-001',
                'nomor_surat' => '001/STTNI/SK/2026',
                'judul' => 'Surat Keputusan Pedoman Pengarsipan Digital STTNI 2026',
                'deskripsi' => 'Pedoman resmi tata kelola dan klasifikasi dokumen di lingkungan STTNI.',
                'kategori_id' => $kategoriMap['SK'],
                'divisi_id' => $divisiIds['PIMP'],
                'study_program_id' => $prodiMap['UMUM'],
                'tahun' => 2026,
                'tanggal_dokumen' => '2026-01-05',
                'tanggal_diterima' => '2026-01-06',
                'pengirim' => 'Ketua STTNI',
                'penerima' => 'Seluruh Dosen & Staf Internal',
                'file_path' => 'arsip/001_SK_Pedoman_Pengarsipan_2026.pdf',
                'file_size' => 2450112,
                'file_mime' => 'application/pdf',
                'status' => 'Aktif',
                'status_publikasi' => 'Public',
                'tags' => 'sk, pedoman, pengarsipan',
                'retention_date' => '2031-01-05',
                'uploader_id' => $admin->id,
            ],
            [
                'nomor_arsip' => 'ARS-2026-002',
                'nomor_surat' => '015/BAAK/RPS/2026',
                'judul' => 'Rencana Pembelajaran Semester (RPS) Hermeneutika Alkitab S1 Teologi',
                'deskripsi' => 'Silabus dan kurikulum lengkap mata kuliah Hermeneutika Alkitab Semester Genap 2025/2026.',
                'kategori_id' => $kategoriMap['RPS'],
                'divisi_id' => $divisiIds['BAAK'],
                'study_program_id' => $prodiMap['TEOL'],
                'tahun' => 2026,
                'tanggal_dokumen' => '2026-01-15',
                'tanggal_diterima' => '2026-01-16',
                'pengirim' => 'Prodi S1 Teologi',
                'penerima' => 'BAAK & Dosen Pengampu',
                'file_path' => 'arsip/002_RPS_Hermeneutika_2026.pdf',
                'file_size' => 1230400,
                'file_mime' => 'application/pdf',
                'status' => 'Aktif',
                'status_publikasi' => 'Public',
                'tags' => 'rps, kurikulum, teologi',
                'retention_date' => '2031-01-15',
                'uploader_id' => $dosen1->id,
            ],
            [
                'nomor_arsip' => 'ARS-2026-003',
                'nomor_surat' => '008/BAUK/AUDIT/2025',
                'judul' => 'Laporan Audit Keuangan Internal dan Inventarisasi Aset 2025',
                'deskripsi' => 'Hasil audit komprehensif keuangan yayasan dan neraca aset.',
                'kategori_id' => $kategoriMap['LAPKEU'],
                'divisi_id' => $divisiIds['BAUK'],
                'study_program_id' => $prodiMap['UMUM'],
                'tahun' => 2025,
                'tanggal_dokumen' => '2025-12-28',
                'tanggal_diterima' => '2025-12-30',
                'pengirim' => 'Kepala BAUK',
                'penerima' => 'Ketua STTNI',
                'file_path' => 'arsip/004_Laporan_Audit_Keuangan_2025.pdf',
                'file_size' => 4500000,
                'file_mime' => 'application/pdf',
                'status' => 'Aktif',
                'status_publikasi' => 'Confidential',
                'tags' => 'audit, keuangan, bauk, rahasia',
                'retention_date' => '2035-12-28',
                'uploader_id' => $admin->id,
            ],
            [
                'nomor_arsip' => 'ARS-2026-004',
                'nomor_surat' => '102/LPM/AKRED/2026',
                'judul' => 'Dokumen Evaluasi Diri (DED) Akreditasi Program Studi S1 PAK',
                'deskripsi' => 'Berkas evaluasi diri dan dokumen pendukung re-akreditasi program studi PAK.',
                'kategori_id' => $kategoriMap['AKRED'],
                'divisi_id' => $divisiIds['LPM'],
                'study_program_id' => $prodiMap['PAK'],
                'tahun' => 2026,
                'tanggal_dokumen' => '2026-02-10',
                'tanggal_diterima' => '2026-02-12',
                'pengirim' => 'Tim Akreditasi LPM',
                'penerima' => 'LAMDIK',
                'file_path' => 'arsip/005_DED_Akreditasi_S1_PAK.pdf',
                'file_size' => 5600200,
                'file_mime' => 'application/pdf',
                'status' => 'Aktif',
                'status_publikasi' => 'Internal',
                'tags' => 'akreditasi, pak, lpm',
                'retention_date' => '2031-02-10',
                'uploader_id' => $admin->id,
            ],
        ];

        $createdArsips = [];
        foreach ($arsipDummy as $item) {
            $createdArsips[] = Arsip::create($item);
        }

        // Seed Sample Access Request (Peminjaman / Persetujuan Akses)
        Peminjaman::create([
            'arsip_id' => $createdArsips[3]->id, // Dokumen Akreditasi (Terbatas)
            'user_id' => $dosen2->id, // Dosen PAK
            'status_approval' => 'Pending',
            'notes' => 'Memerlukan acuan dokumen akreditasi untuk evaluasi kurikulum PAK.',
        ]);

        // 8. Seed Dummy Presensi UKM
        $presensi1 = PresensiUkm::create([
            'ukm_id' => $ukmMusik->id,
            'judul_kegiatan' => 'Pertemuan 1 - Latihan Rutin Paduan Suara',
            'tanggal_kegiatan' => '2026-02-15',
            'pertemuan_ke' => 1,
            'pengisi_id' => $dosenKemahasiswaan->id,
            'status_arsip' => 'Terverifikasi',
            'catatan_pengisi' => 'Latihan perdana paduan suara dihadiri seluruh anggota inti.',
        ]);

        $anggotaMusik = UkmAnggota::where('ukm_id', $ukmMusik->id)->get();
        foreach ($anggotaMusik as $agt) {
            PresensiUkmDetail::create([
                'presensi_id' => $presensi1->id,
                'anggota_id' => $agt->id,
                'nama' => $agt->nama,
                'nim' => $agt->nim,
                'status_kehadiran' => 'Hadir',
                'keterangan' => 'Tepat waktu',
            ]);
        }

        // 9. Seed Audit Logs / Riwayat Aktivitas
        AuditLog::create([
            'user_id' => $admin->id,
            'action' => 'Create Archive',
            'arsip_id' => $createdArsips[0]->id,
            'ip_address' => '127.0.0.1',
            'details' => 'Mengarsipkan SK Pedoman Pengarsipan Digital STTNI 2026.',
        ]);
    }
}
