<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('summative_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['1', '2']);
            $table->string('lingkup_materi')->comment('Lingkup materi / akhir semester');
            $table->decimal('score', 5, 2);
            $table->date('assessed_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'lingkup_materi', 'academic_year', 'semester'],
                'summative_score_unique');
            $table->index(['subject_id', 'classroom_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('summative_scores');
    }
};
