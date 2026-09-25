<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DivisiSeeder extends Seeder
{
    public function run(): void
    {
        $validUnits = [
            ['nama_divisi' => 'BAAK'],
            ['nama_divisi' => 'BAUK'],
            ['nama_divisi' => 'Kemahasiswaan'],
            ['nama_divisi' => 'LPM'],
            ['nama_divisi' => 'LPPM'],
            ['nama_divisi' => 'Perpustakaan'],
            ['nama_divisi' => 'Pimpinan/Rektorat'],
        ];

        foreach ($validUnits as $unit) {
            DB::table('divisi')->updateOrInsert(
                ['nama_divisi' => $unit['nama_divisi']],
                $unit
            );
        }
    }
}
