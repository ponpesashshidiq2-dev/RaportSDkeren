<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['1', '2']);
            $table->unsignedSmallInteger('sakit')->default(0);
            $table->unsignedSmallInteger('izin')->default(0);
            $table->unsignedSmallInteger('tanpa_keterangan')->default(0);
            $table->timestamps();

            $table->unique(['student_id', 'academic_year', 'semester'], 'attendance_unique');
            $table->index(['classroom_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
