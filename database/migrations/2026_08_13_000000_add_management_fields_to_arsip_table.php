<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add document lifecycle, organization relation and physical file fields.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('arsip', 'tanggal_dokumen')) {
            Schema::table('arsip', function (Blueprint $table) {
                $table->date('tanggal_dokumen')->nullable()->after('tahun');
                $table->date('tanggal_diterima')->nullable()->after('tanggal_dokumen');
                $table->string('pengirim')->nullable()->after('tanggal_diterima');
                $table->string('penerima')->nullable()->after('pengirim');
                $table->string('lokasi_fisik')->nullable()->after('file_path');
                $table->bigInteger('file_size')->nullable()->after('lokasi_fisik');
                $table->string('file_mime')->nullable()->after('file_size');
            });
        }

        // status is a plain string column, safe to remap anytime.
        DB::table('arsip')->where('status', 'Expired')->update(['status' => 'Inaktif']);

        // Step 1: expand the enum keeping the old values so existing rows stay valid.
        Schema::table('arsip', function (Blueprint $table) {
            $table->enum('status_publikasi', ['Public', 'Restricted', 'Internal', 'Confidential'])->default('Public')->change();
        });

        // Step 2: migrate the old value now that 'Internal' is accepted.
        DB::table('arsip')->where('status_publikasi', 'Restricted')->update(['status_publikasi' => 'Internal']);

        // Step 3: drop the retired value from the enum.
        Schema::table('arsip', function (Blueprint $table) {
            $table->enum('status_publikasi', ['Public', 'Internal', 'Confidential'])->default('Public')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('arsip')->whereIn('status_publikasi', ['Internal', 'Confidential'])->update(['status_publikasi' => 'Restricted']);

        Schema::table('arsip', function (Blueprint $table) {
            $table->enum('status_publikasi', ['Public', 'Restricted'])->default('Public')->change();
            $table->dropColumn([
                'tanggal_dokumen',
                'tanggal_diterima',
                'pengirim',
                'penerima',
                'lokasi_fisik',
                'file_size',
                'file_mime',
            ]);
        });
    }
};
