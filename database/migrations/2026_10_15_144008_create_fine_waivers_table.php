<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fine_waivers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->foreignId('petugas_id')->constrained('users')->onDelete('cascade');
            $table->integer('amount_waived');                       // Nominal yang dibebaskan
            $table->text('reason');                                 // Alasan bebas denda
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fine_waivers');
    }
};