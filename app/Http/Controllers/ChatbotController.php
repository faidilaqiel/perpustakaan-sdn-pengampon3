<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;

class ChatbotController extends Controller
{
    public function chat(Request $request)
    {
        $message = trim(strtolower($request->input('message') ?? $request->input('pesan') ?? ''));
        $userName = $request->input('userName') ?? 'Faidil Aqiel';
        $userRole = $request->input('userRole') ?? 'Petugas Perpustakaan';
        
        // 1. Logika perkenalan diri AI
        if (str_contains($message, 'siapa anda') || str_contains($message, 'siapa kamu') || $message === 'halo' || $message === 'hai') {
            return response()->json([
                'reply' => "Halo! Saya adalah PustakaAI, asisten kecerdasan buatan resmi Perpustakaan Digital SDN Pengampon 3. Saya siap membantu Anda mengenai informasi koleksi buku, peminjaman, data anggota, dan informasi perpustakaan sekolah. Ada yang bisa saya bantu? 😊",
                'jawaban' => "Halo! Saya adalah PustakaAI, asisten kecerdasan buatan resmi Perpustakaan Digital SDN Pengampon 3. Saya siap membantu Anda mengenai informasi koleksi buku, peminjaman, data anggota, dan informasi perpustakaan sekolah. Ada yang bisa saya bantu? 😊"
            ]);
        }

        // 2. Logika identitas pengguna (Siapa Saya)
        if (str_contains($message, 'siapa saya')) {
            $rep = "Halo! Anda adalah {$userName}, yang saat ini sedang login sebagai {$userRole} di Perpustakaan Digital SDN Pengampon 3.";
            return response()->json(['reply' => $rep, 'jawaban' => $rep]);
        }

        // Daftar kata kunci yang berkaitan dengan perpustakaan & sekolah
        $kataKunciPerpus = [
            'buku', 'pinjam', 'kembali', 'denda', 'stok', 'katalog', 
            'perpustakaan', 'kepala sekolah', 'kepsek', 'jam', 'buka', 
            'anggota', 'siswa', 'guru', 'kartu', 'riwayat', 'cara', 'aturan',
            'transaksi', 'total transaksi', 'sirkulasi', 'faidil', 'rekomendasi',
            'wali kelas', 'absen', 'kunjungan'
        ];

        // Cek apakah pesan mengandung kata kunci perpustakaan
        $isTentangPerpus = false;
        foreach ($kataKunciPerpus as $kata) {
            if (str_contains($message, $kata)) {
                $isTentangPerpus = true;
                break;
            }
        }

        // Jika pertanyaannya DI LUAR perpustakaan, tolak dengan sopan
        if (!$isTentangPerpus) {
            $reply = "Mohon maaf, saya adalah PustakaAI, asisten digital khusus Perpustakaan SDN Pengampon 3. Saya hanya dapat membantu pertanyaan seputar perpustakaan, koleksi buku, peminjaman, dan informasi sekolah. Ada yang bisa saya bantu mengenai buku atau perpustakaan?";
            return response()->json(['reply' => $reply, 'jawaban' => $reply]);
        }

        // Logika jawaban sesuai pertanyaan seputar perpustakaan:
        if (str_contains($message, 'transaksi')) {
            $totalTrans = \App\Models\Loan::count();
            $menunggu = \App\Models\Loan::where('status', 'menunggu')->count();
            $dipinjam = \App\Models\Loan::where('status', 'dipinjam')->count();
            $kembali = \App\Models\Loan::where('status', 'dikembalikan')->count();
            $reply = "Saat ini tercatat ada total {$totalTrans} transaksi peminjaman di Perpustakaan SDN Pengampon 3 (Rincian: {$menunggu} menunggu persetujuan, {$dipinjam} sedang dipinjam, dan {$kembali} selesai dikembalikan).";
        } elseif (str_contains($message, 'rekomendasi') || (str_contains($message, 'buku') && str_contains($message, 'favorit'))) {
            $books = Book::where('stock_available', '>', 0)->limit(3)->get();
            $reply = "Berikut beberapa rekomendasi buku seru di perpustakaan kami:\n";
            foreach ($books as $b) {
                $reply .= "📖 {$b->title} (Karya: {$b->author}, Tersedia: {$b->stock_available} buku)\n";
            }
            $reply .= "\nSilakan ajukan peminjaman melalui menu Katalog ya!";
        } elseif (str_contains($message, 'buku') || str_contains($message, 'koleksi') || str_contains($message, 'stok')) {
            $books = Book::all();
            $total = $books->count();
            $reply = "Terdapat total {$total} judul buku di perpustakaan kami. Beberapa di antaranya:\n\n";
            foreach ($books->take(5) as $b) {
                $reply .= "- {$b->title} oleh {$b->author} (Stok: {$b->stock_available})\n";
            }
        } elseif (str_contains($message, 'absen') || str_contains($message, 'kunjungan')) {
            $today = date('Y-m-d');
            $sudahAbsen = \App\Models\Kunjungan::where('tanggal', $today)->pluck('user_id');
            $belum = \App\Models\User::where('role', 'siswa')->whereNotIn('id', $sudahAbsen)->count();
            $reply = "Hari ini ({$today}), terdapat {$belum} siswa yang belum melakukan absen kunjungan ke perpustakaan.";
        } elseif (str_contains($message, 'kepala sekolah') || str_contains($message, 'kepsek')) {
            $reply = "Kepala Sekolah SDN Pengampon 3 saat ini adalah Bapak H. Noni Risanto, M.Pd.I.";
        } elseif (str_contains($message, 'jam') || str_contains($message, 'buka')) {
            $reply = "Perpustakaan buka setiap hari Senin-Jumat, pukul 07.30 - 14.00 WIB.";
        } elseif (str_contains($message, 'pinjam') || str_contains($message, 'cara')) {
            $reply = "Untuk meminjam buku: Buka menu Katalog -> Cari buku yang diinginkan -> Klik 'Pinjam Buku Ini' -> Datang ke ruang perpustakaan untuk verifikasi fisik oleh Petugas.";
        } else {
            $reply = "Ada yang bisa saya bantu seputar koleksi buku, data peminjaman, atau layanan perpustakaan SDN Pengampon 3?";
        }

        return response()->json(['reply' => $reply, 'jawaban' => $reply]);
    }
}