<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('visits', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->dateTime('visit_date');                                    // Tanggal & jam kunjungan
            $table->enum('purpose', ['baca_di_tempat', 'pinjam_buku', 'kembalikan_buku']);
            $table->text('notes')->nullable();                                 // Catatan tambahan (opsional)
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('visits');
    }
};