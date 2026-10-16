<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Book;
use App\Models\FineWaiver;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanController extends Controller
{
    // ===== TAMPILKAN DAFTAR SIRKULASI =====
    public function index()
    {
        $loans = Loan::with(['user', 'book'])
                     ->orderBy('created_at', 'desc')
                     ->get();

        $totalMenunggu = Loan::where('status', 'menunggu')->count();
        $totalDipinjam = Loan::where('status', 'dipinjam')->count();
        $totalDikembalikan = Loan::where('status', 'dikembalikan')->count();

        return view('petugas.sirkulasi', compact(
            'loans', 'totalMenunggu', 'totalDipinjam', 'totalDikembalikan'
        ));
    }

    // ===== SETUJUI PEMINJAMAN =====
    public function setujui($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'menunggu') {
            return back()->with('error', 'Peminjaman ini tidak dalam status menunggu.');
        }

        $book = Book::findOrFail($loan->book_id);
        if ($book->stock_available <= 0) {
            return back()->with('error', 'Stok buku habis! Tidak bisa disetujui.');
        }

        $loan->update(['status' => 'dipinjam']);
        $book->decrement('stock_available');

        return back()->with('success', 'Peminjaman disetujui! Stok buku berkurang 1.');
    }

    // ===== TOLAK PEMINJAMAN =====
    public function tolak($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'menunggu') {
            return back()->with('error', 'Peminjaman ini tidak dalam status menunggu.');
        }

        $loan->delete();
        return back()->with('success', 'Peminjaman ditolak dan dihapus.');
    }

    // ===== PROSES PENGEMBALIAN BUKU =====
    public function kembalikan($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'dipinjam') {
            return back()->with('error', 'Peminjaman ini tidak dalam status dipinjam.');
        }

        // Hitung denda otomatis
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        $returnDate = Carbon::now()->startOfDay();
        $fine = 0;
        $daysLate = 0;

        if ($returnDate->greaterThan($dueDate)) {
            // Terlambat! Hitung selisih hari × Rp 1.000
            // PENTING: dueDate harus di depan supaya hasil positif
            $daysLate = $dueDate->diffInDays($returnDate);
            $fine = $daysLate * 1000;
        }

        // Update data loan
        $loan->update([
            'return_date' => $returnDate->toDateString(),
            'status' => 'dikembalikan',
            'fine_amount' => $fine,
            'payment_status' => $fine > 0 ? 'belum_lunas' : 'lunas',
        ]);

        // Kembalikan stok buku
        $book = Book::findOrFail($loan->book_id);
        $book->increment('stock_available');

        // Pesan sukses
        if ($fine > 0) {
            return back()->with('success', "Buku dikembalikan. Terlambat {$daysLate} hari! Denda: Rp " . number_format($fine, 0, ',', '.') . " (Belum Lunas).");
        } else {
            return back()->with('success', 'Buku dikembalikan tepat waktu. Tidak ada denda.');
        }
    }

    // ===== BEBASKAN DENDA =====
    public function bebaskanDenda(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->fine_amount <= 0) {
            return back()->with('error', 'Tidak ada denda yang perlu dibebaskan.');
        }

        // Validasi alasan
        $request->validate([
            'reason' => 'required|min:5',
        ], [
            'reason.required' => 'Alasan bebas denda wajib diisi!',
            'reason.min' => 'Alasan minimal 5 karakter.',
        ]);

        // Simpan catatan bebas denda
        FineWaiver::create([
            'loan_id' => $loan->id,
            'petugas_id' => Auth::id(),
            'amount_waived' => $loan->fine_amount,
            'reason' => $request->reason,
        ]);

        // Update loan: denda jadi 0, lunas
        $loan->update([
            'fine_amount' => 0,
            'payment_status' => 'lunas',
        ]);

        return back()->with('success', 'Denda berhasil dibebaskan dengan alasan: ' . $request->reason);
    }
}