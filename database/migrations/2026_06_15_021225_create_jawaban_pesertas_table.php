<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('jawaban_pesertas', function (Blueprint $table) {
            $table->id('id_jawaban');
            $table->foreignId('id_peserta_tes')->constrained('peserta_tes', 'id_peserta_tes')->cascadeOnDelete();
            $table->foreignId('id_soal')->constrained('bank_soals', 'id_soal')->cascadeOnDelete();
            $table->enum('jawaban', ['A', 'B', 'C', 'D', 'E'])->nullable();
            $table->boolean('is_correct')->default(false);
            $table->timestamp('waktu_jawab')->useCurrent();
            $table->unique(['id_peserta_tes', 'id_soal']);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('jawaban_pesertas');
    }
};
