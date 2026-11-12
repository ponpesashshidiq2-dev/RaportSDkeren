<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('extracurricular_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('extracurricular_id')->constrained('extracurriculars')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['1', '2']);
            $table->string('predikat', 50)->comment('Predikat/nilai ekstrakurikuler');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'extracurricular_id', 'academic_year', 'semester'],
                'extracurricular_score_unique');
            $table->index(['classroom_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extracurricular_scores');
    }
};
