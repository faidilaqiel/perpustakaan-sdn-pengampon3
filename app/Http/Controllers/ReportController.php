<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\User;
use App\Models\Category;
use App\Models\Loan;
use App\Models\Kunjungan; 
use Carbon\Carbon;

class ReportController extends Controller
{
    // ===== TAMPILKAN LAPORAN KEPSEK =====
    public function laporan(Request $request)
    {
        // Filter periode (default: semua)
        $periode = $request->input('periode', 'semua');

        // Query dasar
        $queryLoan = Loan::query();

        // Filter berdasarkan periode
        if ($periode === 'bulan_ini') {
            $queryLoan->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
        } elseif ($periode === 'bulan_lalu') {
            $bulanLalu = Carbon::now()->subMonth();
            $queryLoan->whereMonth('created_at', $bulanLalu->month)
                      ->whereYear('created_at', $bulanLalu->year);
        }

        // ===== STATISTIK UTAMA =====
        $totalBuku = Book::count();
        $totalAnggota = User::whereIn('role', ['siswa', 'guru'])->count();
        $totalKategori = Category::count();
        $sedangDipinjam = Loan::where('status', 'dipinjam')->count();
        $totalPeminjaman = (clone $queryLoan)->count();
        $totalDenda = (clone $queryLoan)->where('payment_status', 'lunas')->sum('fine_amount');

        // ===== DAFTAR BUKU SEDANG DIPINJAM =====
        $bukuDipinjam = Loan::with(['user', 'book'])
                            ->where('status', 'dipinjam')
                            ->orderBy('due_date', 'asc')
                            ->get();

        // ===== BUKU TERPOPULER =====
        $bukuTerpopuler = Loan::selectRaw('book_id, COUNT(*) as total')
                              ->groupBy('book_id')
                              ->orderBy('total', 'desc')
                              ->limit(5)
                              ->with('book')
                              ->get();

        // ===== TAMBAHAN BARU: DATA GRAFIK 7 HARI TERAKHIR =====
        $tujuhHariLalu = Carbon::now()->subDays(7);

        // Grafik Peminjaman (Loan)
        $grafikPeminjaman = Loan::selectRaw('DATE(created_at) as tanggal, count(*) as total')
            ->where('created_at', '>=', $tujuhHariLalu)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Grafik Kunjungan (Buku Tamu) -> SUDAH DIPERBAIKI AGAR AMAN DI MYSQL 8
        $grafikKunjungan = Kunjungan::selectRaw('DATE(tanggal) as tgl, count(*) as total')
            ->whereDate('tanggal', '>=', $tujuhHariLalu->toDateString())
            ->groupBy('tgl')
            ->orderBy('tgl')
            ->get();

        return view('kepsek.laporan', compact(
            'periode',
            'totalBuku',
            'totalAnggota',
            'totalKategori',
            'sedangDipinjam',
            'totalPeminjaman',
            'totalDenda',
            'bukuDipinjam',
            'bukuTerpopuler',
            'grafikPeminjaman', 
            'grafikKunjungan'   
        ));
    }

    // ===== CETAK PDF LAPORAN =====
    public function cetakPdf(Request $request)
    {
        // Filter periode (sama seperti laporan())
        $periode = $request->input('periode', 'semua');

        // Query dasar
        $queryLoan = Loan::query();

        // Filter berdasarkan periode
        if ($periode === 'bulan_ini') {
            $queryLoan->whereMonth('created_at', Carbon::now()->month)
                      ->whereYear('created_at', Carbon::now()->year);
        } elseif ($periode === 'bulan_lalu') {
            $bulanLalu = Carbon::now()->subMonth();
            $queryLoan->whereMonth('created_at', $bulanLalu->month)
                      ->whereYear('created_at', $bulanLalu->year);
        }

        // ===== STATISTIK =====
        $totalBuku = Book::count();
        $totalAnggota = User::whereIn('role', ['siswa', 'guru'])->count();
        $totalKategori = Category::count();
        $sedangDipinjam = Loan::where('status', 'dipinjam')->count();
        $totalPeminjaman = (clone $queryLoan)->count();
        $totalDenda = (clone $queryLoan)->where('payment_status', 'lunas')->sum('fine_amount');

        $bukuDipinjam = Loan::with(['user', 'book'])
                            ->where('status', 'dipinjam')
                            ->orderBy('due_date', 'asc')
                            ->get();

        $bukuTerpopuler = Loan::selectRaw('book_id, COUNT(*) as total')
                              ->groupBy('book_id')
                              ->orderBy('total', 'desc')
                              ->limit(5)
                              ->with('book')
                              ->get();

        // Tanggal cetak
        $tanggalCetak = Carbon::now()->translatedFormat('d F Y, H:i') . ' WIB';

        // Load view khusus PDF
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('kepsek.laporan-pdf', compact(
            'periode',
            'totalBuku',
            'totalAnggota',
            'totalKategori',
            'sedangDipinjam',
            'totalPeminjaman',
            'totalDenda',
            'bukuDipinjam',
            'bukuTerpopuler',
            'tanggalCetak'
        ));

        // Atur ukuran kertas & orientasi
        $pdf->setPaper('A4', 'portrait');

        // Tampilkan PDF di browser (preview, tidak langsung download)
        $namaFile = 'laporan-perpustakaan-' . date('Y-m-d-His') . '.pdf';
        return $pdf->stream($namaFile);
    }
}