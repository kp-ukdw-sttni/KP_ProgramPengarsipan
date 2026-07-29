<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('arsip', function (Blueprint $table) {
            $table->id();
            $table->string('nomor_arsip')->unique();
            $table->string('judul');
            $table->text('deskripsi')->nullable();
            $table->foreignId('kategori_id')->constrained('kategori_arsip')->onDelete('restrict');
            $table->foreignId('divisi_id')->constrained('divisi')->onDelete('restrict');
            $table->string('file_path');
            $table->date('retention_date');
            $table->string('status')->default('Aktif'); // Aktif, Expired, Dimusnahkan
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('arsip');
    }
};
