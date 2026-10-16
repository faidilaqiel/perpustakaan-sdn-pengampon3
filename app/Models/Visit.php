<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Visit extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'visit_date', 'purpose', 'notes'];

    // Relasi: 1 visit milik 1 user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}