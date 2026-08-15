<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add study program relation to the archives table.
     */
    public function up(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->foreignId('study_program_id')->nullable()->after('divisi_id')
                ->constrained('study_programs')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('arsip', function (Blueprint $table) {
            $table->dropForeign(['study_program_id']);
            $table->dropColumn('study_program_id');
        });
    }
};
