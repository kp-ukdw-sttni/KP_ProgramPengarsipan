<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom nama_prodi (teks bebas) ke tabel arsip
     * sebagai input teks pengganti dropdown study_program_id (FK).
     * Kolom study_program_id tetap dipertahankan untuk kompatibilitas data lama.
     */
    public function up(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->string('nama_prodi', 255)->nullable()->after('study_program_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->dropColumn('nama_prodi');
        });
    }
};

