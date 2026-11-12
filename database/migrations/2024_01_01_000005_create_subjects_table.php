<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('subjects', function (Blueprint $table) {
            $table->id();
            $table->string('code', 30)->nullable()->unique();
            $table->string('name')->comment('Nama mata pelajaran');
            $table->enum('muatan', ['agama', 'nasional', 'lokal'])->comment('3 muatan kurikulum');
            $table->decimal('kkm', 5, 2)->nullable()->comment('KKM; kosong = mapel TIDAK dicetak di rapor');
            $table->string('phase', 5)->nullable()->comment('Fase (opsional per mapel)');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['muatan', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('subjects');
    }
};
