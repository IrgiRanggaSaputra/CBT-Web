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
        Schema::create('peserta_tes', function (Blueprint $table) {
            $table->id('id_peserta_tes');
            $table->foreignId('id_jadwal')->constrained('jadwal_tes', 'id_jadwal')->cascadeOnDelete();
            $table->foreignId('id_peserta')->constrained('pesertas', 'id_peserta')->cascadeOnDelete();
            $table->string('token', 50)->unique()->nullable();
            $table->enum('status_tes', ['belum_mulai', 'sedang_tes', 'selesai'])->default('belum_mulai');
            $table->dateTime('waktu_mulai')->nullable();
            $table->dateTime('waktu_selesai')->nullable();
            $table->decimal('nilai', 5, 2)->nullable();
            $table->enum('status_kelulusan', ['lulus', 'tidak_lulus', 'belum_dinilai'])->default('belum_dinilai');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('peserta_tes');
    }
};
