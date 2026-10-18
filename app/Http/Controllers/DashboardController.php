<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\User;
use App\Models\Loan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function petugas()
    {
        // 1. Ambil data statistik
        $totalBuku = Book::count();
        $totalAnggota = User::whereIn('role', ['siswa', 'guru'])->count();
        $sedangDipinjam = Loan::where('status', 'dipinjam')->count();
        $terlambat = Loan::where('status', 'dipinjam')
                         ->where('due_date', '<', Carbon::now()->format('Y-m-d'))
                         ->count();
        
        // 2. Ambil data grafik peminjaman 7 hari terakhir
        $tujuhHariLalu = Carbon::now()->subDays(7);
        $grafikPeminjaman = Loan::selectRaw('DATE(created_at) as tanggal, count(*) as total')
            ->where('created_at', '>=', $tujuhHariLalu)
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // 3. Ambil data peminjaman terbaru (5 data terakhir)
        $peminjamanTerbaru = Loan::with(['user', 'book'])
                                 ->orderBy('created_at', 'desc')
                                 ->limit(5)
                                 ->get();

        return view('petugas.dashboard', compact(
            'totalBuku', 
            'totalAnggota', 
            'sedangDipinjam', 
            'terlambat', 
            'grafikPeminjaman', 
            'peminjamanTerbaru'
        ));
    }
}