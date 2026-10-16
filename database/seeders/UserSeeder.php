<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // ===== 1. ADMIN / PETUGAS =====
        User::create([
            'nip' => '197703172009022003',
            'name' => 'Sumirah, S.Pd.',
            'gender' => 'P',
            'role' => 'petugas',
            'password' => Hash::make('20222312'),
        ]);

        // ===== 2. KEPALA SEKOLAH =====
        User::create([
            'nip' => '19750814 200801 1 008',
            'name' => 'H. NONI RISANTO, M.Pd.I',
            'gender' => 'L',
            'role' => 'kepsek',
            'password' => Hash::make('19750814 200801 1 008'),
        ]);

        // ===== 3. SISWA CONTOH =====
        User::create([
            'nisn' => '0012345678',
            'name' => 'Adrian Marchell Sugianto',
            'gender' => 'L',
            'class' => 'VI-A',
            'role' => 'siswa',
            'password' => Hash::make('0012345678'),
        ]);

        // ===== 4. GURU CONTOH =====
        User::create([
            'nip' => '199001012015011002',
            'name' => 'Ibu Siti Aminah, S.Pd.',
            'gender' => 'P',
            'role' => 'guru',
            'password' => Hash::make('199001012015011002'),
        ]);
    }
}