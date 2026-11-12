<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('students', function (Blueprint $table) {
            $table->id();
            $table->string('nisn', 20)->nullable()->unique()->comment('Nomor Induk Siswa Nasional');
            $table->string('nis', 30)->nullable()->comment('Nomor Induk Siswa internal');
            $table->string('nama')->comment('Nama peserta didik');
            $table->enum('jenis_kelamin', ['L', 'P']);
            $table->string('tempat_lahir', 100);
            $table->date('tanggal_lahir');
            $table->text('alamat');
            $table->string('no_hp', 20)->nullable();
            $table->string('nama_ayah')->nullable();
            $table->string('nama_ibu')->nullable();
            $table->string('nama_wali')->nullable()->comment('Orang tua/wali yang menandatangani rapor');
            $table->foreignId('classroom_id')->nullable()->constrained('classrooms')->nullOnDelete();
            $table->string('photo')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index('nama');
            $table->index(['classroom_id', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('students');
    }
};
