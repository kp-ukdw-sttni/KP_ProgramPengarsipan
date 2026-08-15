<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Tambahkan kolom verifikasi arsip hasil presensi UKM.
     */
    public function up(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->enum('verification_status', ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'])
                ->nullable()
                ->after('status');
            $table->foreignId('presensi_ukm_id')
                ->nullable()
                ->after('uploader_id')
                ->constrained('presensi_ukm')
                ->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->dropForeign(['presensi_ukm_id']);
            $table->dropColumn(['verification_status', 'presensi_ukm_id']);
        });
    }
};
