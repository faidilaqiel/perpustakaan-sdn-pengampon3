<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Kunjungan extends Model
{
    use HasFactory;

    // Mengizinkan kolom ini untuk diisi secara massal
    protected $fillable = [
        'user_id', 
        'tanggal', 
        'jam_masuk', 
        'keterangan'
    ];

    // Relasi ke User (Siswa/Guru yang berkunjung)
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}