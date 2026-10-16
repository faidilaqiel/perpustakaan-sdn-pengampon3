<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineWaiver extends Model
{
    use HasFactory;

    protected $fillable = ['loan_id', 'petugas_id', 'amount_waived', 'reason'];

    // Relasi: 1 waiver milik 1 loan
    public function loan()
    {
        return $this->belongsTo(Loan::class);
    }

    // Relasi: 1 waiver dibuat oleh 1 petugas
    public function petugas()
    {
        return $this->belongsTo(User::class, 'petugas_id');
    }
}