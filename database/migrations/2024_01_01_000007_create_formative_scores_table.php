<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('formative_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('learning_objective_id')->constrained('learning_objectives')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['1', '2']);
            $table->decimal('score', 5, 2)->comment('Nilai formatif per TP (0-100)');
            $table->date('assessed_at')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'learning_objective_id', 'academic_year', 'semester'],
                'formative_score_unique');
            $table->index(['subject_id', 'classroom_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('formative_scores');
    }
};
