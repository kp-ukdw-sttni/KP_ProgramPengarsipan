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
        Schema::table('kategori_arsip', function (Blueprint $table) {
            $table->string('kode')->unique()->nullable()->after('id');
            $table->text('deskripsi')->nullable()->after('name');
            $table->foreignId('parent_id')->nullable()->after('id')->constrained('kategori_arsip')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('kategori_arsip', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['kode', 'deskripsi', 'parent_id']);
        });
    }
};
