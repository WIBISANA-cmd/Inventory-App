<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BahanMasuk extends Model
{
    use HasFactory;

    protected $table = 'bahan_masuk';

    protected $fillable = [
        'tanggal_masuk',
        'kode_bahan',
        'nama_bahan',
        'jumlah_masuk',
        'tipe',
    ];

    public function bahan()
    {
        return $this->belongsTo(Bahan::class, 'kode_bahan', 'kode_bahan');
    }
    public function bahanProcess()
    {
        return $this->belongsTo(BahanProcess::class,'kode_bahan', 'kode_bahan');
    }
}

