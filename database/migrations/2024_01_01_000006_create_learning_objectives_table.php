<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('learning_objectives', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained('subjects')->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained('classrooms')->cascadeOnDelete();
            $table->string('academic_year', 20);
            $table->enum('semester', ['1', '2']);
            $table->string('code', 30)->comment('Kode TP, misal: TP-1.1');
            $table->text('description')->comment('Deskripsi capaian/tujuan pembelajaran');
            $table->text('approach')->nullable()->comment('Pendekatan pemahaman bermakna / deep learning');
            $table->unsignedSmallInteger('order_no')->default(0);
            $table->timestamps();

            $table->unique(['subject_id', 'classroom_id', 'academic_year', 'semester', 'code']);
            $table->index(['classroom_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('learning_objectives');
    }
};
