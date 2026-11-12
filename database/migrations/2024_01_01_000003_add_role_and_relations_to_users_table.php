<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone', 20)->nullable()->after('name');
            $table->string('role', 30)->default('guru')->after('email')
                ->comment('super_admin|guru|wali_kelas|kepala_sekolah|siswa_orangtua');
            $table->foreignId('classroom_id')->nullable()->after('role')->constrained('classrooms')->nullOnDelete();
            $table->foreignId('student_id')->nullable()->after('classroom_id')->constrained('students')->nullOnDelete();

            $table->index('role');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('classroom_id');
            $table->dropConstrainedForeignId('student_id');
            $table->dropIndex(['role']);
            $table->dropColumn(['phone', 'role']);
        });
    }
};
