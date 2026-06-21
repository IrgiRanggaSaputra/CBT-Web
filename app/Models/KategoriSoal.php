<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class KategoriSoal extends Model
{
    protected $primaryKey = 'id_kategori';

    protected $fillable = [
        'nama_kategori',
        'deskripsi',
    ];

    public function bankSoals(): HasMany
    {
        return $this->hasMany(BankSoal::class, 'id_kategori', 'id_kategori');
    }

    public function jadwalTes(): HasMany
    {
        return $this->hasMany(JadwalTes::class, 'id_kategori', 'id_kategori');
    }
}
