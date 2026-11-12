<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('classrooms', function (Blueprint $table) {
            $table->id();
            $table->string('name', 30)->comment('Contoh: 1A, 2B, 6C');
            $table->unsignedTinyInteger('grade')->comment('Kelas 1 s/d 6');
            $table->string('academic_year', 20)->comment('Contoh: 2025/2026');
            $table->foreignId('wali_kelas_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['name', 'grade', 'academic_year']);
            $table->index('grade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('classrooms');
    }
};
