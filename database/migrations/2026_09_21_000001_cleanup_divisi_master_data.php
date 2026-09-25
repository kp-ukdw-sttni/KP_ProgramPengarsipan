<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Migration lama ini sudah digantikan oleh:
 * 2026_09_21_034751_remove_prodi_from_divisi_table.php
 *
 * File ini dikosongkan (no-op) agar tidak error karena
 * kolom 'nama_divisi' tidak ada (kolom yang benar adalah 'name').
 */
return new class extends Migration
{
    public function up(): void
    {
        // No-op: logika dipindah ke migration remove_prodi_from_divisi_table
    }

    public function down(): void
    {
        // No-op
    }
};
