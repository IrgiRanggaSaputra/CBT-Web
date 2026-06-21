<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SoalTes extends Model
{
    protected $table = 'soal_tes';

    protected $primaryKey = 'id_soal_tes';

    protected $fillable = [
        'id_jadwal',
        'id_soal',
        'nomor_urut',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalTes::class, 'id_jadwal', 'id_jadwal');
    }

    public function soal(): BelongsTo
    {
        return $this->belongsTo(BankSoal::class, 'id_soal', 'id_soal');
    }
}
