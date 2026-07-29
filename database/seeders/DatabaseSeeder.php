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
        $superadminRole = Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        $operatorRole = Role::firstOrCreate(['name' => 'Operator', 'guard_name' => 'web']);
        $karyawanRole = Role::firstOrCreate(['name' => 'Karyawan', 'guard_name' => 'web']);

        // 2. Seed Divisi
        $divisiAkademik = Divisi::firstOrCreate(['name' => 'Akademik']);
        $divisiKeuangan = Divisi::firstOrCreate(['name' => 'Keuangan']);
        $divisiPerpustakaan = Divisi::firstOrCreate(['name' => 'Perpustakaan']);
        $divisiKemahasiswaan = Divisi::firstOrCreate(['name' => 'Kemahasiswaan']);
        $divisiSdm = Divisi::firstOrCreate(['name' => 'SDM']);

        // 3. Seed KategoriArsip
        KategoriArsip::firstOrCreate(['name' => 'Surat Keputusan (SK)']);
        KategoriArsip::firstOrCreate(['name' => 'Sertifikat']);
        KategoriArsip::firstOrCreate(['name' => 'Laporan Keuangan']);
        KategoriArsip::firstOrCreate(['name' => 'Dokumen Akreditasi']);
        KategoriArsip::firstOrCreate(['name' => 'Transkrip & Ijazah']);
        KategoriArsip::firstOrCreate(['name' => 'Berkas Kepegawaian']);

        // 4. Seed Users
        
        // Superadmin (Full Access, no specific division)
        $superadmin = User::firstOrCreate(
            ['email' => 'admin@sttni.ac.id'],
            [
                'name' => 'Super Admin STTNI',
                'password' => Hash::make('admin123'),
                'divisi_id' => null,
            ]
        );
        $superadmin->assignRole($superadminRole);

        // Operator Akademik
        $operatorAkademik = User::firstOrCreate(
            ['email' => 'operator_akademik@sttni.ac.id'],
            [
                'name' => 'Operator Akademik',
                'password' => Hash::make('operator123'),
                'divisi_id' => $divisiAkademik->id,
            ]
        );
        $operatorAkademik->assignRole($operatorRole);

        // Operator Keuangan
        $operatorKeuangan = User::firstOrCreate(
            ['email' => 'operator_keuangan@sttni.ac.id'],
            [
                'name' => 'Operator Keuangan',
                'password' => Hash::make('operator123'),
                'divisi_id' => $divisiKeuangan->id,
            ]
        );
        $operatorKeuangan->assignRole($operatorRole);

        // Karyawan 1 (Kemahasiswaan)
        $karyawan1 = User::firstOrCreate(
            ['email' => 'karyawan1@sttni.ac.id'],
            [
                'name' => 'Budi Santoso (Kemahasiswaan)',
                'password' => Hash::make('karyawan123'),
                'divisi_id' => $divisiKemahasiswaan->id,
            ]
        );
        $karyawan1->assignRole($karyawanRole);

        // Karyawan 2 (Akademik)
        $karyawan2 = User::firstOrCreate(
            ['email' => 'karyawan2@sttni.ac.id'],
            [
                'name' => 'Siti Aminah (Akademik)',
                'password' => Hash::make('karyawan123'),
                'divisi_id' => $divisiAkademik->id,
            ]
        );
        $karyawan2->assignRole($karyawanRole);
    }
}
