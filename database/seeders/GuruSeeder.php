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
            ['nip' => '198310042009022003', 'name' => 'Sri Yulimawati, S.Pd.', 'gender' => 'P'],
            ['nip' => '196812032000032005', 'name' => 'Helderia Sianturi, S.Th', 'gender' => 'P'],
            ['nip' => '198804192019032007', 'name' => 'Elis Susilawati, S.Pd.', 'gender' => 'P'],
            ['nip' => '199411072020122010', 'name' => 'Gina Rahmania Sari, S.Pd.', 'gender' => 'P'],
            ['nip' => '198507022022212002', 'name' => 'Rizky Yulianti, S.Pd.SD.', 'gender' => 'P'],
            ['nip' => '199602222022212001', 'name' => 'Fitrias Hermawati, S.Pd.', 'gender' => 'P'],
            ['nip' => '197507172023212001', 'name' => 'Iin Asminah, S.Pd.', 'gender' => 'P'],
            ['nip' => '199002132023212004', 'name' => 'Ahmad, S.Pd.', 'gender' => 'L'],
            ['nip' => '198902072024212008', 'name' => 'Aris Kharurisah, S.Pd.I.', 'gender' => 'P'],
            ['nip' => '198903302024211008', 'name' => 'Nana Setiana, S.Pd.', 'gender' => 'L'],
            ['nip' => '7238770671130023', 'name' => 'Septi Nurjanah, S.Pd.', 'gender' => 'P'],
        ];

        foreach ($gurus as $guru) {
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