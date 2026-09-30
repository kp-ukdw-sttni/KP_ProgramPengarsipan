<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Persetujuan akses berubah dari "pinjaman berjangka waktu" menjadi
 * "izin akses permanen".
 *
 * 1. Kolom `reviewed_at` mencatat kapan keputusan diambil, terpisah dari
 *    `updated_at` yang juga berubah saat baris diedit lain.
 * 2. Persetujuan lama yang dulu berjangka waktu dinormalisasi menjadi permanen
 *    (`expired_at` = null) supaya hanya ada satu aturan: `Approved` berarti
 *    dokumen terbuka. Baris `Expired` tetap histórico dan tidak dihidupkan.
 * 3. Indeks unik (arsip_id, user_id) menutup duplikasi yang sebelumnya hanya
 *    dicegah di kode aplikasi dan bisa lolos bila dua request datang bersamaan.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            if (! Schema::hasColumn('peminjaman', 'reviewed_at')) {
                $table->dateTime('reviewed_at')->nullable()->after('expired_at');
            }
        });

        $this->normalkanPersetujuanLama();
        $this->buangDuplikat();

        $indexName = 'peminjaman_arsip_user_unique';

        // Name only: hasIndex() takes the index type as its third argument, so
        // passing the name there would always return false.
        if (! Schema::hasIndex('peminjaman', $indexName)) {
            Schema::table('peminjaman', function (Blueprint $table) use ($indexName) {
                $table->unique(['arsip_id', 'user_id'], $indexName);
            });
        }
    }

    /**
     * Persetujuan lama yang masih punya `expired_at` diubah jadi permanen.
     */
    private function normalkanPersetujuanLama(): void
    {
        DB::table('peminjaman')
            ->where('status_approval', 'Approved')
            ->whereNotNull('expired_at')
            ->update(['expired_at' => null]);
    }

    /**
     * Buang baris kembar per (arsip_id, user_id) sebelum unique index dipasang.
     *
     * Baris terbaru (id terbesar) yang dipertahankan karena mewakili keputusan
     * terakhir. `expired_at` sengaja tidak ikut di-update supaya nilai null di
     * atas tetap berarti "permanen".
     */
    private function buangDuplikat(): void
    {
        $duplikat = DB::table('peminjaman')
            ->select('arsip_id', 'user_id')
            ->groupBy('arsip_id', 'user_id')
            ->havingRaw('COUNT(*) > 1')
            ->get();

        foreach ($duplikat as $pasangan) {
            $pivot = DB::table('peminjaman')
                ->where('arsip_id', $pasangan->arsip_id)
                ->where('user_id', $pasangan->user_id)
                ->max('id');

            DB::table('peminjaman')
                ->where('arsip_id', $pasangan->arsip_id)
                ->where('user_id', $pasangan->user_id)
                ->where('id', '!=', $pivot)
                ->delete();
        }
    }

    public function down(): void
    {
        // InnoDB reuses the leading column of a unique index to satisfy a
        // foreign key, so the composite index cannot be dropped while the
        // `arsip_id` / `user_id` constraints still point at it. The FKs are
        // dropped and recreated with the exact definition from the original
        // create_peminjaman_table migration.
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropForeign(['arsip_id']);
            $table->dropForeign(['user_id']);
        });

        Schema::table('peminjaman', function (Blueprint $table) {
            if (Schema::hasIndex('peminjaman', 'peminjaman_arsip_user_unique')) {
                $table->dropUnique('peminjaman_arsip_user_unique');
            }

            if (Schema::hasColumn('peminjaman', 'reviewed_at')) {
                $table->dropColumn('reviewed_at');
            }
        });

        Schema::table('peminjaman', function (Blueprint $table) {
            $table->foreign('arsip_id')->references('id')->on('arsip')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
        });
    }
};
