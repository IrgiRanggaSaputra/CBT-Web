<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BankSoal extends Model
{
    protected $primaryKey = 'id_soal';

    protected $fillable = [
        'id_kategori',
        'pertanyaan',
        'pilihan_a',
        'pilihan_b',
        'pilihan_c',
        'pilihan_d',
        'pilihan_e',
        'jawaban_benar',
        'bobot',
        'gambar',
    ];

    public function kategori(): BelongsTo
    {
        return $this->belongsTo(KategoriSoal::class, 'id_kategori', 'id_kategori');
    }
}
