<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Kolom path file Excel rekap untuk arsip hasil presensi.
     */
    public function up(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->string('file_path_excel')->nullable()->after('file_path');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->dropColumn('file_path_excel');
        });
    }
};
