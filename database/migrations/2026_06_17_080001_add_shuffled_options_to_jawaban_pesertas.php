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
        Schema::table('jawaban_pesertas', function (Blueprint $table) {
            $table->json('shuffled_options')->nullable()->after('id_soal');
            $table->enum('jawaban_asli', ['A', 'B', 'C', 'D', 'E'])->nullable()->after('jawaban');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('jawaban_pesertas', function (Blueprint $table) {
            $table->dropColumn(['shuffled_options', 'jawaban_asli']);
        });
    }
};
