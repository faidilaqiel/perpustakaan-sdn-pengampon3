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
            'nip' => 'admin',
            'name' => 'Administrator',
            'gender' => 'P',
            'role' => 'petugas',
            'password' => Hash::make('070623'),
            'status' => 'aktif',
        ]);

        // ===== 2. KEPALA SEKOLAH =====
        User::create([
            'nip' => '197508142008011008',
            'name' => 'H. NONI RISANTO, M.Pd.I',
            'gender' => 'L',
            'role' => 'kepsek',
            'password' => Hash::make('197508142008011008'),
            'status' => 'aktif',
        ]);

        // ===== 3. SISWA CONTOH =====
        User::create([
            'nisn' => '0012345678',
            'name' => 'Adrian Marchell Sugianto',
            'gender' => 'L',
            'class' => 'VI-A',
            'role' => 'siswa',
            'password' => Hash::make('0012345678'),
            'status' => 'aktif',
        ]);

        // ===== 4. GURU CONTOH =====
        User::create([
            'nip' => '199001012015011002',
            'name' => 'Ibu Siti Aminah, S.Pd.',
            'gender' => 'P',
            'role' => 'guru',
            'password' => Hash::make('199001012015011002'),
            'status' => 'aktif',
        ]);
    }
}