<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('final_scores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained('students')->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['1', '2']);
            $table->decimal('avg_formatif', 5, 2)->nullable();
            $table->decimal('avg_sumatif', 5, 2)->nullable();
            $table->decimal('final_score', 5, 2)->nullable()->comment('Hasil gabungan formatif & sumatif');
            $table->decimal('previous_score', 5, 2)->nullable()->comment('Nilai semester/tahun sebelumnya');
            $table->boolean('is_decreased')->default(false)->comment('True = turun dari semester sebelumnya (merah)');
            $table->timestamp('calculated_at')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'subject_id', 'academic_year', 'semester'],
                'final_score_unique');
            $table->index(['classroom_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('final_scores');
    }
};
