<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Book;

// Endpoint koleksi buku untuk integrasi Botpress AI Knowledge Base
Route::get('/books', function () {
    $books = Book::with('category')->get()->map(function ($book) {
        $kategori = $book->category ? $book->category->name : 'Umum';
        $statusStok = $book->stock_available > 0 
            ? "Tersedia ({$book->stock_available} buku siap dipinjam)" 
            : "Sedang habis dipinjam";

        return [
            'id' => $book->id,
            'kode_buku' => $book->code,
            'judul' => $book->title,
            'penulis' => $book->author,
            'penerbit' => $book->publisher ?? '-',
            'tahun' => $book->year ?? '-',
            'kategori' => $kategori,
            'stok_tersedia' => $book->stock_available,
            'stok_total' => $book->stock_total,
            'status' => $statusStok,
            'deskripsi_ai' => "Buku '{$book->title}' karya {$book->author}, kategori {$kategori}, kode {$book->code}. Status: {$statusStok}.",
        ];
    });

    return response()->json([
        'status' => 'success',
        'total' => $books->count(),
        'data' => $books,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Endpoint informasi resmi perpustakaan untuk Botpress AI Context
Route::get('/library-info', function () {
    $info = [
        'nama_perpustakaan' => 'Perpustakaan Digital SDN Pengampon 3',
        'nama_bot' => 'PustakaAI',
        'sekolah' => 'SDN Pengampon 3 Cirebon',
        'kepala_sekolah' => 'Bapak H. Noni Risanto, M.Pd.I.',
        'jam_operasional' => 'Senin - Jumat: 07.30 - 14.00 WIB (Sabtu, Minggu & Hari Libur Nasional Tutup)',
        'aturan_peminjaman' => [
            'syarat' => 'Terdaftar sebagai siswa/guru/staf aktif SDN Pengampon 3.',
            'cara_pinjam' => 'Buka katalog di website, pilih buku, klik tombol Pinjam Buku, lalu konfirmasi ke petugas perpustakaan.',
            'batas_waktu_pinjam' => 'Maksimal 7 hari kalender per buku.',
            'maksimal_buku' => 'Siswa dapat meminjam maksimal 2 buku sekaligus.',
            'pengembalian' => 'Pengembalian buku diserahkan kepada petugas perpustakaan untuk di-scan QR/diverifikasi.',
        ],
        'kontak' => [
            'email' => 'sdpengampon3cirebon@gmail.com',
            'telepon' => '0231232048',
            'alamat' => 'SDN Pengampon 3, Kota Cirebon, Jawa Barat',
        ],
    ];

    return response()->json([
        'status' => 'success',
        'data' => $info,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});