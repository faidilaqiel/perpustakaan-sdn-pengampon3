<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('book_id')->constrained('books')->onDelete('cascade');
            $table->date('loan_date');                                // Tanggal pinjam
            $table->date('due_date');                                 // Tanggal jatuh tempo
            $table->date('return_date')->nullable();                  // Tanggal kembali (kosong jika belum)
            $table->enum('status', ['menunggu', 'dipinjam', 'dikembalikan', 'hilang'])->default('menunggu');
            $table->integer('fine_amount')->default(0);               // Total denda (Rp)
            $table->enum('payment_status', ['lunas', 'belum_lunas'])->default('lunas');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};