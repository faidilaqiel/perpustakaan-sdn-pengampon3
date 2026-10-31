<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('users')
            ->where('nip', '198902072024212008')
            ->orWhere('name', 'like', '%Aris%Khar%')
            ->orWhere('name', 'like', '%Khairunisah%')
            ->update(['name' => 'Anis Khairunisah, S.Pd.']);
    }

    public function down(): void
    {
    }
};
