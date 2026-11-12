<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Penanda kepemilikan siswa: hanya pengguna yang membuat/mengimpor data siswa
 * (dan Super Admin / Kepala Sekolah) yang dapat melihat & mengelola siswa tersebut.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->foreignId('created_by')
                ->nullable()
                ->after('photo')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by');
        });
    }
};
