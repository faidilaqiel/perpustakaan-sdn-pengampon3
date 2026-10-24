<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        // Ubah pesan user menjadi huruf kecil agar mudah dicek
        $message = strtolower($request->input('message'));
        
        // Jawaban default jika pertanyaan tidak dikenali
        $reply = "Mohon maaf, saya adalah PustakaAI, asisten Perpustakaan Digital SDN Pengampon 3. Saya hanya bisa membantu menjawab pertanyaan seputar perpustakaan, buku, dan informasi sekolah. Ada yang bisa saya bantu terkait perpustakaan?";

        // Daftar kata kunci yang berkaitan dengan perpustakaan
        $kataKunciPerpus = [
            'buku', 'pinjam', 'kembali', 'denda', 'stok', 'katalog', 
            'perpustakaan', 'kepala sekolah', 'kepsek', 'jam', 'buka', 
            'anggota', 'siswa', 'guru', 'kartu', 'riwayat', 'cara', 'aturan'
        ];

        // Cek apakah pesan mengandung salah satu kata kunci di atas
        $isTentangPerpus = false;
        foreach ($kataKunciPerpus as $kata) {
            if (str_contains($message, $kata)) {
                $isTentangPerpus = true;
                break;
            }
        }

        // Jika pertanyaannya seputar perpustakaan, baru proses jawabannya
        if ($isTentangPerpus) {
            
            // 1. Logika jika user tanya daftar buku
            if (str_contains($message, 'buku') || str_contains($message, 'koleksi') || str_contains($message, 'stok')) {
                $books = Book::all();
                $reply = "Berikut adalah daftar buku yang tersedia di perpustakaan:\n\n";
                foreach ($books as $b) {
                    $reply .= "- {$b->title} oleh {$b->author} (Stok: {$b->stock_available})\n";
                }
            } 
            // 2. Logika jika user tanya kepala sekolah
            elseif (str_contains($message, 'kepala sekolah') || str_contains($message, 'kepsek')) {
                $reply = "Kepala Sekolah SDN Pengampon 3 saat ini adalah Bapak H. Noni Risanto, M.Pd.I.";
            }
            // 3. Logika jika user tanya jam buka
            elseif (str_contains($message, 'jam') || str_contains($message, 'buka')) {
                $reply = "Perpustakaan buka setiap hari Senin-Jumat, pukul 07.30 - 14.00 WIB.";
            }
            // 4. Logika jika user tanya cara pinjam
            elseif (str_contains($message, 'pinjam') || str_contains($message, 'cara')) {
                $reply = "Untuk meminjam buku, silakan cari buku di menu Katalog, klik 'Pinjam Buku Ini', lalu tunggu konfirmasi dari Petugas Perpustakaan.";
            }
        }

        return response()->json(['reply' => $reply]);
    }
}