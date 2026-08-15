<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Sesi presensi pertemuan UKM.
     */
    public function up(): void
    {
        Schema::create('presensi_ukm', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ukm_id')->constrained('ukm')->onDelete('cascade');
            $table->string('judul_kegiatan')->nullable();
            $table->date('tanggal_kegiatan');
            $table->unsignedInteger('pertemuan_ke')->nullable();
            $table->foreignId('pengisi_id')->nullable()->constrained('users')->onDelete('set null');
            $table->enum('status_arsip', ['Menunggu Verifikasi', 'Terverifikasi', 'Ditolak'])->default('Menunggu Verifikasi');
            $table->foreignId('arsip_id')->nullable()->constrained('arsip')->onDelete('set null');
            $table->text('catatan_pengisi')->nullable();
            $table->text('catatan_reviewer')->nullable();
            $table->foreignId('reviewer_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('reviewed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('presensi_ukm');
    }
};
