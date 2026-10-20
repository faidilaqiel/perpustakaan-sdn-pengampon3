<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Book;

Route::get('/books', function () {
    $books = Book::all(['title', 'author', 'code', 'stock_available']);
    return response()->json($books);
});