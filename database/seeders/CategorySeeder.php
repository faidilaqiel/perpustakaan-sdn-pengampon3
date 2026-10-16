<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;

class CategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            'Cerita Rakyat',
            'Matematika',
            'IPA',
            'Agama',
            'Umum',
        ];

        foreach ($categories as $name) {
            Category::create(['name' => $name]);
        }
    }
}