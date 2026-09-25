<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds borrow_type (digital|physical) and return_date to support
     * the physical vs digital borrow UX flow.
     */
    public function up(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->string('borrow_type')->default('digital')->after('user_id'); // 'digital' | 'physical'
            $table->date('return_date')->nullable()->after('borrow_type');       // expected return date for physical loans
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('peminjaman', function (Blueprint $table) {
            $table->dropColumn(['borrow_type', 'return_date']);
        });
    }
};
