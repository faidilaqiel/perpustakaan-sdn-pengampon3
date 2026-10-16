<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'nisn',
        'nip',
        'name',
        'gender',
        'class',
        'role',
        'password',
        'photo',
        'status',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'password' => 'hashed',
    ];

    // Relasi: 1 user punya banyak loans (peminjaman)
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }

    // Relasi: 1 user punya banyak visits (kunjungan)
    public function visits()
    {
        return $this->hasMany(Visit::class);
    }
}