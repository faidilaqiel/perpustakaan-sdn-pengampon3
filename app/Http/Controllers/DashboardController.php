<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        // Mencari peminjaman yang statusnya 'dipinjam' DAN tanggal jatuh temponya sudah lewat dari hari ini
        $bukuTerlambat = Loan::with(['user', 'book'])
            ->where('status', 'dipinjam')
            ->where('due_date', '<', Carbon::now()->toDateString())
            ->orderBy('due_date', 'asc') // Urutkan dari yang paling lama telat
            ->get();

        return view('petugas.dashboard', compact('bukuTerlambat'));
    }
}