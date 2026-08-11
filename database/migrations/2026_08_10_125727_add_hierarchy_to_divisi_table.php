<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add hierarchy (Fakultas -> Prodi) and short code to divisi.
     */
    public function up(): void
    {
        Schema::table('divisi', function (Blueprint $table) {
            $table->string('kode')->unique()->nullable()->after('id');
            $table->foreignId('parent_id')->nullable()->after('kode')
                ->constrained('divisi')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('divisi', function (Blueprint $table) {
            $table->dropForeign(['parent_id']);
            $table->dropColumn(['kode', 'parent_id']);
        });
    }
};
