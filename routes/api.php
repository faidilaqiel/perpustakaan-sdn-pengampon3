<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Book;

Route::get('/books', function () {
    $books = Book::all(['title', 'author', 'code', 'stock_available']);
    
    // Tambahkan header CORS agar bisa diakses Botpress
    return response()->json($books)->header('Access-Control-Allow-Origin', '*')
                                   ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
                                   ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});