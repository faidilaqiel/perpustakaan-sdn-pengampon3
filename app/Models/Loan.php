<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'book_id', 'loan_date', 'due_date',
        'return_date', 'status', 'fine_amount', 'payment_status',
    ];

    // Relasi: 1 loan milik 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi: 1 loan milik 1 buku
    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    // Relasi: 1 loan bisa punya 1 bebas denda
    public function fineWaiver()
    {
        return $this->hasOne(FineWaiver::class);
    }
}