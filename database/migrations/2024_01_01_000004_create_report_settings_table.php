<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_settings', function (Blueprint $table) {
            $table->id();
            $table->string('school_name')->default('SD Kurikulum Merdeka');
            $table->string('npsn', 30)->nullable();
            $table->text('address')->nullable();
            $table->string('city', 100)->nullable()->comment('Kota untuk tanda tangan rapor');
            $table->string('phone', 30)->nullable();
            $table->string('email')->nullable();
            $table->string('kepala_sekolah_name')->nullable();
            $table->string('kepala_sekolah_nip')->nullable();
            $table->string('active_academic_year', 20)->default('2025/2026');
            $table->enum('active_semester', ['1', '2'])->default('1')->comment('1=Ganjil, 2=Genap');
            $table->unsignedDecimal('formatif_weight', 5, 2)->default(40)->comment('Bobot nilai formatif (%)');
            $table->unsignedDecimal('sumatif_weight', 5, 2)->default(60)->comment('Bobot nilai sumatif (%)');
            $table->string('default_phase', 5)->default('A')->comment('Fase Kurikulum Merdeka');
            $table->string('report_footer', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_settings');
    }
};
