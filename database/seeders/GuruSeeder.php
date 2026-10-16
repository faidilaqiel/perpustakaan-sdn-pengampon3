<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class GuruSeeder extends Seeder
{
    public function run(): void
    {
        $gurus = [
            // No 2
            [
                'nip' => '198310042009022003',
                'name' => 'Sri Yulimawati, S.Pd.',
                'gender' => 'P',
            ],
            // No 3
            [
                'nip' => '196812032000032005',
                'name' => 'Helderia Sianturi, S.Th',
                'gender' => 'P',
            ],
            // No 4
            [
                'nip' => '198804192019032007',
                'name' => 'Elis Susilawati, S.Pd.',
                'gender' => 'P',
            ],
            // No 5
            [
                'nip' => '199411072020122010',
                'name' => 'Gina Rahmania Sari, S.Pd.',
                'gender' => 'P',
            ],
            // No 6
            [
                'nip' => '198507022022212002',
                'name' => 'Rizky Yulianti, S.Pd.SD.',
                'gender' => 'P',
            ],
            // No 7
            [
                'nip' => '199602222022212001',
                'name' => 'Fitrias Hermawati, S.Pd.',
                'gender' => 'P',
            ],
            // No 8
            [
                'nip' => '197507172023212001',
                'name' => 'Iin Asminah, S.Pd.',
                'gender' => 'P',
            ],
            // No 9
            [
                'nip' => '199002132023212004',
                'name' => 'Ahmad, S.Pd.',
                'gender' => 'L',
            ],
            // No 10
            [
                'nip' => '198902072024212008',
                'name' => 'Aris Kharurisah, S.Pd.I.',
                'gender' => 'P',
            ],
            // No 11
            [
                'nip' => '198903302024211008',
                'name' => 'Nana Setiana, S.Pd.',
                'gender' => 'L',
            ],
            // No 12 (tanpa NIP, pakai NIK)
            [
                'nip' => '7238770671130023',
                'name' => 'Septi Nurjanah, S.Pd.',
                'gender' => 'P',
            ],
        ];

        foreach ($gurus as $guru) {
            // Cek apakah sudah ada (untuk hindari duplikat)
            $exists = User::where('nip', $guru['nip'])->exists();
            
            if (!$exists) {
                User::create([
                    'nip' => $guru['nip'],
                    'name' => $guru['name'],
                    'gender' => $guru['gender'],
                    'role' => 'guru',
                    'password' => Hash::make($guru['nip']),
                    'status' => 'aktif',
                ]);
            }
        }
    }
}