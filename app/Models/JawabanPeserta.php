<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JawabanPeserta extends Model
{
    protected $primaryKey = 'id_jawaban';

    protected $fillable = [
        'id_peserta_tes',
        'id_soal',
        'shuffled_options',
        'jawaban',
        'jawaban_asli',
        'is_correct',
        'waktu_jawab',
    ];

    protected $casts = [
        'is_correct' => 'boolean',
        'waktu_jawab' => 'datetime',
        'shuffled_options' => 'array',
    ];

    public function pesertaTes(): BelongsTo
    {
        return $this->belongsTo(PesertaTes::class, 'id_peserta_tes', 'id_peserta_tes');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'id_soal', 'id_soal');
    }
}
