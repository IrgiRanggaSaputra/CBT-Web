<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class JadwalTes extends Model
{
    protected $table = 'jadwal_tes';

    protected $primaryKey = 'id_jadwal';

    protected $fillable = [
        'nama_tes',
        'id_kategori',
        'tanggal_mulai',
        'tanggal_selesai',
        'durasi',
        'jumlah_soal',
        'passing_grade',
        'instruksi',
        'status',
    ];

    protected $casts = [
        'tanggal_mulai' => 'datetime',
        'tanggal_selesai' => 'datetime',
        'passing_grade' => 'decimal:2',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSoal::class, 'id_kategori', 'id_kategori');
    }

    public function pesertaTes(): HasMany
    {
        return $this->hasMany(PesertaTes::class, 'id_jadwal', 'id_jadwal');
    }

    public function soalTes(): HasMany
    {
        return $this->hasMany(SoalTes::class, 'id_jadwal', 'id_jadwal');
    }
}
