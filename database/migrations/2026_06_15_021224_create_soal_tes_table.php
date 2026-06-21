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
        Schema::create('soal_tes', function (Blueprint $table) {
            $table->id('id_soal_tes');
            $table->foreignId('id_jadwal')->constrained('jadwal_tes', 'id_jadwal')->cascadeOnDelete();
            $table->foreignId('id_soal')->constrained('bank_soals', 'id_soal')->cascadeOnDelete();
            $table->integer('nomor_urut')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('soal_tes');
    }
};
