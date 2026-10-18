<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanController extends Controller
{
    // ===== HALAMAN SIRKULASI PETUGAS =====
    public function index()
    {
        $loans = Loan::with(['user', 'book'])->orderBy('created_at', 'desc')->get();
        
        $menunggu = Loan::where('status', 'menunggu')->count();
        $dipinjam = Loan::where('status', 'dipinjam')->count();
        $dikembalikan = Loan::where('status', 'dikembalikan')->count();
        $jumlahPengajuan = $menunggu;

        return view('petugas.sirkulasi', compact('loans', 'menunggu', 'dipinjam', 'dikembalikan', 'jumlahPengajuan'));
    }

    // ===== PROSES SISWA MENGAJUKAN PINJAM =====
    public function pinjam($id)
    {
        $book = Book::findOrFail($id);

        if ($book->stock_available <= 0) {
            return back()->with('error', '❌ Stok buku habis!');
        }

        $cekPinjam = Loan::where('user_id', Auth::id())
                         ->where('book_id', $book->id)
                         ->whereIn('status', ['menunggu', 'dipinjam'])
                         ->first();

        if ($cekPinjam) {
            return back()->with('error', '❌ Anda masih meminjam buku ini dan belum mengembalikannya.');
        }

        Loan::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'loan_date' => Carbon::now()->format('Y-m-d'), 
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'status' => 'menunggu',
        ]);

        return redirect()->route('siswa.katalog')
                         ->with('success', '✅ Pengajuan pinjam berhasil! Silakan tunggu konfirmasi dari Petugas.');
    }

    // ===== PETUGAS MENYETUJUI PINJAMAN =====
    public function setujui($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'menunggu') {
            return back()->with('error', 'Status pinjaman ini sudah diproses.');
        }

        $book = Book::findOrFail($loan->book_id);

        if ($book->stock_available > 0) {
            $book->decrement('stock_available');
            
            $loan->update([
                'status' => 'dipinjam',
                'loan_date' => Carbon::now()->format('Y-m-d'), 
                'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            ]);

            // SIMPAN NOTIFIKASI UNTUK SISWA
            $user = $loan->user;
            $user->update([
                'notifikasi' => 'disetujui|Buku "' . $book->title . '" berhasil disetujui. Silakan ambil di perpustakaan. Jatuh tempo: ' . Carbon::now()->addDays(7)->translatedFormat('d M Y')
            ]);

            return back()->with('success', '✅ Peminjaman disetujui! Stok buku berkurang 1.');
        }

        return back()->with('error', '❌ Gagal menyetujui, stok buku sudah habis.');
    }

    // ===== PETUGAS MENOLAK PINJAMAN =====
    public function tolak($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'menunggu') {
            return back()->with('error', 'Status pinjaman ini sudah diproses.');
        }

        $loan->update(['status' => 'batal']);

        // SIMPAN NOTIFIKASI UNTUK SISWA
        $user = $loan->user;
        $book = $loan->book;
        $user->update([
            'notifikasi' => 'ditolak|Mohon maaf, pengajuan pinjam buku "' . $book->title . '" ditolak oleh petugas.'
        ]);

        return back()->with('success', '✅ Pengajuan peminjaman telah ditolak.');
    }

    // ===== PETUGAS MENGEMBALIKAN BUKU =====
    public function kembalikan($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'dipinjam') {
            return back()->with('error', 'Buku ini tidak dalam status dipinjam.');
        }

        $book = Book::findOrFail($loan->book_id);

        $dueDate = Carbon::parse($loan->due_date);
        $hariIni = Carbon::now();
        
        $hariTerlambat = $hariIni->greaterThan($dueDate) ? $dueDate->diffInDays($hariIni) : 0;
        $tarifDenda = 1000; 
        $denda = $hariTerlambat * $tarifDenda;

        $book->increment('stock_available');

        $loan->update([
            'status' => 'dikembalikan',
            'return_date' => Carbon::now()->format('Y-m-d'),
            'fine_amount' => $denda,
            'payment_status' => $denda > 0 ? 'belum_lunas' : 'lunas',
        ]);

        $pesan = '✅ Buku berhasil dikembalikan.';
        if ($denda > 0) {
            $pesan .= ' Denda: Rp ' . number_format($denda, 0, ',', '.') . ' (Terlambat ' . $hariTerlambat . ' hari).';
        }

        return back()->with('success', $pesan);
    }
}