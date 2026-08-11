<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
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

        // 2. Seed Fakultas -> Prodi hierarchy (Divisi)
        $ftis = Divisi::firstOrCreate(['name' => 'Fakultas Teknik, Informatika & Sains', 'kode' => 'FTIS']);
        $fbh = Divisi::firstOrCreate(['name' => 'Fakultas Bisnis & Humaniora', 'kode' => 'FBH']);

        $prodiInformatika = Divisi::firstOrCreate(['name' => 'Informatika', 'kode' => 'IF', 'parent_id' => $ftis->id]);
        $prodiSistemInformasi = Divisi::firstOrCreate(['name' => 'Sistem Informasi', 'kode' => 'SI', 'parent_id' => $ftis->id]);
        $prodiManajemen = Divisi::firstOrCreate(['name' => 'Manajemen', 'kode' => 'MN', 'parent_id' => $fbh->id]);
        $prodiPsikologi = Divisi::firstOrCreate(['name' => 'Psikologi', 'kode' => 'PS', 'parent_id' => $fbh->id]);

        // 3. Seed unit organizational (staf) divisions
        $divisiAkademik = Divisi::firstOrCreate(['name' => 'Akademik']);
        $divisiKeuangan = Divisi::firstOrCreate(['name' => 'Keuangan']);
        $divisiPerpustakaan = Divisi::firstOrCreate(['name' => 'Perpustakaan']);
        $divisiKemahasiswaan = Divisi::firstOrCreate(['name' => 'Kemahasiswaan']);
        $divisiSdm = Divisi::firstOrCreate(['name' => 'SDM']);

        // 4. Seed KategoriArsip (parent + children)
        $sk = KategoriArsip::firstOrCreate(['name' => 'Surat Keputusan (SK)', 'kode' => 'SK']);
        KategoriArsip::firstOrCreate(['name' => 'Sertifikat', 'kode' => 'SERT']);
        KategoriArsip::firstOrCreate(['name' => 'Laporan Keuangan', 'kode' => 'LAPKEU']);
        $akreditasi = KategoriArsip::firstOrCreate(['name' => 'Dokumen Akreditasi', 'kode' => 'AKREDITASI']);
        $ijazah = KategoriArsip::firstOrCreate(['name' => 'Transkrip & Ijazah', 'kode' => 'IJAZAH']);
        KategoriArsip::firstOrCreate(['name' => 'Berkas Kepegawaian', 'kode' => 'KEPEGAWAIAN']);

        // Sub-kategori examples
        KategoriArsip::firstOrCreate(['name' => 'SK Pengangkatan', 'kode' => 'SK-PENGANGKATAN', 'parent_id' => $sk->id]);
        KategoriArsip::firstOrCreate(['name' => 'SK Mutasi', 'kode' => 'SK-MUTASI', 'parent_id' => $sk->id]);
        KategoriArsip::firstOrCreate(['name' => 'Laporan Akreditasi Prodi', 'kode' => 'LAP-AKR', 'parent_id' => $akreditasi->id]);
        KategoriArsip::firstOrCreate(['name' => 'Ijazah & Transkrip Alumni', 'kode' => 'IJZ-ALUMNI', 'parent_id' => $ijazah->id]);

        // 5. Seed Users

        // Superadmin (Full Access)
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@sttni.ac.id'],
            [
                'name' => 'Super Admin STTNI',
                'password' => Hash::make('admin123'),
                'divisi_id' => null,
                'nik_nim' => 'ADM-001',
                'status_akun' => 'Aktif',
            ]
        );
        $superadmin->assignRole('Superadmin');

        // Operators (Staf TU is seeded below)
        $operatorAkademik = User::firstOrCreate(
            ['email' => 'operator_akademik@sttni.ac.id'],
            [
                'name' => 'Operator Akademik',
                'password' => Hash::make('operator123'),
                'divisi_id' => $divisiAkademik->id,
                'nik_nim' => 'OP-001',
                'status_akun' => 'Aktif',
            ]
        );
        $operatorAkademik->assignRole('Operator');

        $operatorKeuangan = User::firstOrCreate(
            ['email' => 'operator_keuangan@sttni.ac.id'],
            [
                'name' => 'Operator Keuangan',
                'password' => Hash::make('operator123'),
                'divisi_id' => $divisiKeuangan->id,
                'nik_nim' => 'OP-002',
                'status_akun' => 'Aktif',
            ]
        );
        $operatorKeuangan->assignRole('Operator');

        // Karyawan
        $karyawan1 = User::firstOrCreate(
            ['email' => 'karyawan1@sttni.ac.id'],
            [
                'name' => 'Budi Santoso (Kemahasiswaan)',
                'password' => Hash::make('karyawan123'),
                'divisi_id' => $divisiKemahasiswaan->id,
                'nik_nim' => 'KY-001',
                'status_akun' => 'Aktif',
            ]
        );
        $karyawan1->assignRole('Karyawan');

        $karyawan2 = User::firstOrCreate(
            ['email' => 'karyawan2@sttni.ac.id'],
            [
                'name' => 'Siti Aminah (Akademik)',
                'password' => Hash::make('karyawan123'),
                'divisi_id' => $divisiAkademik->id,
                'nik_nim' => 'KY-002',
                'status_akun' => 'Aktif',
            ]
        );
        $karyawan2->assignRole('Karyawan');

        // Staf TU
        $stafftu = User::firstOrCreate(
            ['email' => 'stafftu@sttni.ac.id'],
            [
                'name' => 'Rina Staf TU',
                'password' => Hash::make('staff123'),
                'divisi_id' => $divisiAkademik->id,
                'nik_nim' => 'TU-001',
                'status_akun' => 'Aktif',
            ]
        );
        $stafftu->assignRole('Staf TU');

        // Kaprodi
        $kaprodi = User::firstOrCreate(
            ['email' => 'kaprodi.ti@sttni.ac.id'],
            [
                'name' => 'Dr. Kaprodi Informatika',
                'password' => Hash::make('kaprodi123'),
                'divisi_id' => $prodiInformatika->id,
                'nik_nim' => 'KP-001',
                'status_akun' => 'Aktif',
            ]
        );
        $kaprodi->assignRole('Kaprodi');

        // Dekan
        $dekan = User::firstOrCreate(
            ['email' => 'dekan.ftis@sttni.ac.id'],
            [
                'name' => 'Prof. Dekan FTIS',
                'password' => Hash::make('dekan123'),
                'divisi_id' => $ftis->id,
                'nik_nim' => 'DK-001',
                'status_akun' => 'Aktif',
            ]
        );
        $dekan->assignRole('Dekan');

        // Dosen
        $dosen = User::firstOrCreate(
            ['email' => 'dosen.ti@sttni.ac.id'],
            [
                'name' => 'Bpk. Dosen Informatika',
                'password' => Hash::make('dosen123'),
                'divisi_id' => $prodiInformatika->id,
                'nik_nim' => 'DS-001',
                'status_akun' => 'Aktif',
            ]
        );
        $dosen->assignRole('Dosen');

        // Mahasiswa
        $mahasiswa = User::firstOrCreate(
            ['email' => 'mahasiswa@sttni.ac.id'],
            [
                'name' => 'Mahasiswa STTNI',
                'password' => Hash::make('mahasiswa123'),
                'divisi_id' => $prodiInformatika->id,
                'nik_nim' => '2310001',
                'status_akun' => 'Aktif',
            ]
        );
        $mahasiswa->assignRole('Mahasiswa');
    }
}
