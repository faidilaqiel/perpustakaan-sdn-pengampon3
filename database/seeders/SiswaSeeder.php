<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class SiswaSeeder extends Seeder
{
    public function run(): void
    {
        // Data Dummy (Contoh) Siswa SDN Pengampon 3
        $dataSiswa = [
            // ===== KELAS 1 =====
            ['name' => 'Aditya Pratama', 'gender' => 'L', 'class' => '1', 'nisn' => '231001'],
            ['name' => 'Bunga Citra', 'gender' => 'P', 'class' => '1', 'nisn' => '231002'],
            ['name' => 'Candra Wijaya', 'gender' => 'L', 'class' => '1', 'nisn' => '231003'],
            
            // ===== KELAS 2A =====
            ['name' => 'Dewi Sartika', 'gender' => 'P', 'class' => '2A', 'nisn' => '232001'],
            ['name' => 'Eko Prasetyo', 'gender' => 'L', 'class' => '2A', 'nisn' => '232002'],
            
            // ===== KELAS 2B =====
            ['name' => 'Fitri Handayani', 'gender' => 'P', 'class' => '2B', 'nisn' => '232003'],
            ['name' => 'Gilang Ramadhan', 'gender' => 'L', 'class' => '2B', 'nisn' => '232004'],

            // ===== KELAS 3 =====
            ['name' => 'Hesti Purwanti', 'gender' => 'P', 'class' => '3', 'nisn' => '233001'],
            ['name' => 'Indra Kusuma', 'gender' => 'L', 'class' => '3', 'nisn' => '233002'],
            ['name' => 'Jihan Aulia', 'gender' => 'P', 'class' => '3', 'nisn' => '233003'],

            // ===== KELAS 4 =====
            ['name' => 'Kiki Saputra', 'gender' => 'L', 'class' => '4', 'nisn' => '234001'],
            ['name' => 'Lestari Ningsih', 'gender' => 'P', 'class' => '4', 'nisn' => '234002'],

            // ===== KELAS 5 =====
            ['name' => 'Muhammad Rizky', 'gender' => 'L', 'class' => '5', 'nisn' => '235001'],
            ['name' => 'Nabila Syafira', 'gender' => 'P', 'class' => '5', 'nisn' => '235002'],

            // ===== KELAS 6 =====
            ['name' => 'Oscar Pratama', 'gender' => 'L', 'class' => '6', 'nisn' => '236001'],
            ['name' => 'Putri Ayu', 'gender' => 'P', 'class' => '6', 'nisn' => '236002'],
            ['name' => 'Qori Amanda', 'gender' => 'P', 'class' => '6', 'nisn' => '236003'],
        ];

        foreach ($dataSiswa as $siswa) {
            User::create([
                'role' => 'siswa',
                'name' => $siswa['name'],
                'gender' => $siswa['gender'],
                'class' => $siswa['class'],
                'nisn' => $siswa['nisn'],
                'nip' => null,
                // PASSWORD OTOMATIS DISAMAKAN DENGAN NISN
                'password' => Hash::make($siswa['nisn']), 
                'status' => 'aktif',
                'photo' => null,
            ]);
        }
    }
}