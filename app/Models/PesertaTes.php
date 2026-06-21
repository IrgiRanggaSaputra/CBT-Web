<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PesertaTes extends Model
{
    protected $table = 'peserta_tes';

    protected $primaryKey = 'id_peserta_tes';

    protected $fillable = [
        'id_jadwal',
        'id_peserta',
        'token',
        'shuffle_seed',
        'status_tes',
        'waktu_mulai',
        'waktu_selesai',
        'nilai',
        'status_kelulusan',
    ];

    protected $casts = [
        'waktu_mulai' => 'datetime',
        'waktu_selesai' => 'datetime',
        'nilai' => 'decimal:2',
    ];

    public function jadwal(): BelongsTo
    {
        return $this->belongsTo(JadwalTes::class, 'id_jadwal', 'id_jadwal');
    }

    public function peserta(): BelongsTo
    {
        return $this->belongsTo(Peserta::class, 'id_peserta', 'id_peserta');
    }

    public function jawabanPesertas(): HasMany
    {
        return $this->hasMany(JawabanPeserta::class, 'id_peserta_tes', 'id_peserta_tes');
    }
}
