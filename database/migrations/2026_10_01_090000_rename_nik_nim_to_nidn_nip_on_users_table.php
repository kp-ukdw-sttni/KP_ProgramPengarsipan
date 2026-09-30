<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The users table stores the staff identifier in `nik_nim`, which no longer
 * fits: NIK is a population id and NIM belongs to students, while every account
 * here is a lecturer (NIDN) or an employee (NIP).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nik_nim', 'nidn_nip');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->renameColumn('nidn_nip', 'nik_nim');
        });
    }
};
