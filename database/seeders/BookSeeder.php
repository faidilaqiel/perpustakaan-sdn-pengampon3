<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Book;

class BookSeeder extends Seeder
{
    public function run(): void
    {
        $books = [
            [
                'code' => 'BK-001',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'year' => 2005,
                'category_id' => 1,
                'stock_total' => 5,
                'stock_available' => 5,
            ],
            [
                'code' => 'BK-002',
                'title' => 'Matematika Kelas VI',
                'author' => 'Tim Erlangga',
                'publisher' => 'Erlangga',
                'year' => 2020,
                'category_id' => 2,
                'stock_total' => 10,
                'stock_available' => 10,
            ],
            [
                'code' => 'BK-003',
                'title' => 'Sains untuk SD',
                'author' => 'Tim Yudhistira',
                'publisher' => 'Yudhistira',
                'year' => 2019,
                'category_id' => 3,
                'stock_total' => 8,
                'stock_available' => 8,
            ],
            [
                'code' => 'BK-004',
                'title' => 'Cerita Rakyat Nusantara',
                'author' => 'Ali Muakhir',
                'publisher' => 'Dar! Mizan',
                'year' => 2018,
                'category_id' => 1,
                'stock_total' => 6,
                'stock_available' => 6,
            ],
            [
                'code' => 'BK-005',
                'title' => 'Pendidikan Agama Islam',
                'author' => 'Tim Kemdikbud',
                'publisher' => 'Kemdikbud',
                'year' => 2021,
                'category_id' => 4,
                'stock_total' => 12,
                'stock_available' => 12,
            ],
        ];

        foreach ($books as $book) {
            Book::create($book);
        }
    }
}