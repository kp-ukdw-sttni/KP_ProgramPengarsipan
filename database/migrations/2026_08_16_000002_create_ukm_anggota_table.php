<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Anggota dari setiap UKM.
     */
    public function up(): void
    {
        Schema::create('ukm_anggota', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ukm_id')->constrained('ukm')->onDelete('cascade');
            $table->string('nama');
            $table->string('nim')->nullable();
            $table->string('jabatan')->nullable();
            $table->enum('status_keanggotaan', ['Aktif', 'Keluar'])->default('Aktif');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ukm_anggota');
    }
};
