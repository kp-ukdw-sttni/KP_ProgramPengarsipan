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
        Schema::table('arsip', function (Blueprint $table) {
            $table->string('nomor_surat')->nullable()->after('nomor_arsip');
            $table->string('tags')->nullable()->after('deskripsi');
            $table->enum('status_publikasi', ['Public', 'Restricted'])->default('Public')->after('status');
            $table->foreignId('uploader_id')->nullable()->after('divisi_id')->constrained('users')->onDelete('set null');
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->dropForeign(['uploader_id']);
            $table->dropColumn(['nomor_surat', 'tags', 'status_publikasi', 'uploader_id']);
            $table->dropSoftDeletes();
        });
    }
};
