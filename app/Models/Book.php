<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'code', 'title', 'author', 'publisher',
        'year', 'category_id', 'stock_total',
        'stock_available', 'cover',
    ];

    // Relasi: 1 buku milik 1 kategori
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Relasi: 1 buku bisa dipinjam banyak kali
    public function loans()
    {
        return $this->hasMany(Loan::class);
    }
}