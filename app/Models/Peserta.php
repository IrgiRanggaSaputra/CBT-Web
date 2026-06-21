<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Support\Facades\Hash;
use Laravel\Sanctum\HasApiTokens;

class Peserta extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $primaryKey = 'id_peserta';

    protected $fillable = [
        'nomor_peserta',
        'nama_lengkap',
        'email',
        'password',
        'firebase_uid',
        'jenis_kelamin',
        'tanggal_lahir',
        'telepon',
        'alamat',
        'status',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
    ];

    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = Hash::needsRehash($value) ? Hash::make($value) : $value;
    }

    public function pesertaTes(): HasMany
    {
        return $this->hasMany(PesertaTes::class, 'id_peserta', 'id_peserta');
    }
}
