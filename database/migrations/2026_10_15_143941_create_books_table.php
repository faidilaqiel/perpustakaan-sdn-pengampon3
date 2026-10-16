<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('code', 20)->unique();                    // Contoh: BK-001
            $table->string('title', 200);                            // Judul buku
            $table->string('author', 100);                           // Penulis
            $table->string('publisher', 100);                        // Penerbit
            $table->year('year');                                    // Tahun terbit
            $table->foreignId('category_id')->constrained('categories')->onDelete('cascade');
            $table->integer('stock_total')->default(0);              // Stok total
            $table->integer('stock_available')->default(0);          // Stok tersedia
            $table->string('cover')->nullable();                     // Nama file gambar cover
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};