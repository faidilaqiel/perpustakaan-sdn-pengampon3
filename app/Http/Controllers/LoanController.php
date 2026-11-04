<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Loan;
use App\Models\Book;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $loans = Loan::with(['user', 'book'])->orderBy('created_at', 'desc')->get();
        
        $menunggu = Loan::where('status', 'menunggu')->count();
        $dipinjam = Loan::where('status', 'dipinjam')->count();
        $dikembalikan = Loan::where('status', 'dikembalikan')->count();
        $total = $loans->count();
        $jumlahPengajuan = $menunggu;
        $initialFilter = $request->query('status', 'all');

        return view('petugas.sirkulasi', compact('loans', 'menunggu', 'dipinjam', 'dikembalikan', 'total', 'jumlahPengajuan', 'initialFilter'));
    }

    public function getLatestData()
    {
        $loans = Loan::with(['user', 'book'])->orderBy('created_at', 'desc')->get();
        $menunggu = Loan::where('status', 'menunggu')->count();
        $dipinjam = Loan::where('status', 'dipinjam')->count();
        $dikembalikan = Loan::where('status', 'dikembalikan')->count();
        
        $data = $loans->map(function($loan) {
            $user = $loan->user;
            $book = $loan->book;
            $dueDate = Carbon::parse($loan->due_date)->startOfDay();
            $today = Carbon::now()->startOfDay();
            $isLate = ($loan->status === 'dipinjam' && $today->greaterThan($dueDate));
            $daysLate = $isLate ? (int)$dueDate->diffInDays($today) : 0;
            $denda = $daysLate * 1000;

            $waLink = null;
            if ($user && $user->phone) {
                if ($isLate) {
                    $waLink = \App\Services\WhatsAppService::getWaLink($user->phone, \App\Services\WhatsAppService::getOverdueWarningText($loan, $daysLate, $denda));
                } elseif ($loan->status === 'dipinjam') {
                    $waLink = \App\Services\WhatsAppService::getWaLink($user->phone, \App\Services\WhatsAppService::getReminderH1Text($loan));
                }
            }

            return [
                'id' => $loan->id,
                'user_name' => $user->name ?? 'User Terhapus',
                'user_id' => $user->nisn ?? $user->nip ?? '-',
                'user_phone' => $user->phone ?? null,
                'book_title' => $book->title ?? 'Buku Terhapus',
                'book_code' => $book->code ?? '-',
                'loan_date' => Carbon::parse($loan->loan_date)->translatedFormat('d M Y'),
                'due_date' => Carbon::parse($loan->due_date)->translatedFormat('d M Y'),
                'status' => $loan->status,
                'is_late' => $isLate,
                'days_late' => $daysLate,
                'denda' => $denda,
                'wa_link' => $waLink,
            ];
        });

        return response()->json([
            'loans' => $data,
            'menunggu' => $menunggu,
            'dipinjam' => $dipinjam,
            'dikembalikan' => $dikembalikan,
            'total' => $loans->count(),
        ]);
    }

    /**
     * Jalankan pengecekan dan pengiriman pengingat WhatsApp secara on-demand oleh Petugas
     */
    public function sendWaReminders()
    {
        try {
            \Illuminate\Support\Facades\Artisan::call('wa:send-reminders');
            $output = \Illuminate\Support\Facades\Artisan::output();
            return back()->with('success', '✅ Notifikasi WhatsApp Otomatis: ' . trim($output));
        } catch (\Throwable $e) {
            return back()->with('error', '❌ Gagal memproses notifikasi WhatsApp: ' . $e->getMessage());
        }
    }

    public function riwayat()
    {
        $loans = Loan::with('book')
                    ->where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get();
                    
        return view('siswa.riwayat', compact('loans'));
    }

    public function apiRiwayat()
    {
        $loans = Loan::with('book')
                    ->where('user_id', Auth::id())
                    ->orderBy('created_at', 'desc')
                    ->get()
                    ->map(function ($loan) {
                        return [
                            'id' => $loan->id,
                            'title' => $loan->book->title ?? 'Buku Terhapus',
                            'author' => $loan->book->author ?? '-',
                            'loan_date' => Carbon::parse($loan->loan_date)->translatedFormat('d M Y'),
                            'due_date' => Carbon::parse($loan->due_date)->translatedFormat('d M Y'),
                            'status' => $loan->status,
                        ];
                    });

        return response()->json([
            'loans' => $loans
        ]);
    }

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

        // Saat siswa mengajukan, tanggal & jatuh tempo hanya placeholder (akan diubah petugas)
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

    // ===== PETUGAS MENYETUJUI PINJAMAN (INPUT MANUAL TANGGAL) =====
    public function setujui(Request $request, $id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'menunggu') {
            return back()->with('error', 'Status pinjaman ini sudah diproses.');
        }

        // Validasi input tanggal manual dari Petugas
        $request->validate([
            'loan_date' => 'required|date',
            'due_date' => 'required|date|after_or_equal:loan_date',
        ], [
            'loan_date.required' => 'Tanggal pinjam wajib diisi.',
            'due_date.required' => 'Tanggal jatuh tempo wajib diisi.',
            'due_date.after_or_equal' => 'Jatuh tempo tidak boleh sebelum tanggal pinjam.',
        ]);

        $book = Book::findOrFail($loan->book_id);

        if ($book->stock_available > 0) {
            $book->decrement('stock_available');
            
            // Update status dan tanggal sesuai inputan Petugas
            $loan->update([
                'status' => 'dipinjam',
                'loan_date' => $request->loan_date, 
                'due_date' => $request->due_date,
            ]);

            $user = $loan->user;
            $user->update([
                'notifikasi' => 'disetujui|Buku "' . $book->title . '" berhasil disetujui. Silakan ambil di perpustakaan. Jatuh tempo: ' . Carbon::parse($request->due_date)->translatedFormat('d M Y')
            ]);

            // Kirim Notifikasi WhatsApp Otomatis ke Anggota
            try {
                \App\Services\WhatsAppService::sendLoanConfirmation($loan);
            } catch (\Throwable $e) {
                \Illuminate\Support\Facades\Log::error('WA Loan Notification Error: ' . $e->getMessage());
            }

            return back()->with('success', '✅ Peminjaman disetujui! Notifikasi WhatsApp telah diproses.');
        }

        return back()->with('error', '❌ Gagal menyetujui, stok buku sudah habis.');
    }

    public function tolak($id)
    {
        $loan = Loan::findOrFail($id);

        if ($loan->status !== 'menunggu') {
            return back()->with('error', 'Status pinjaman ini sudah diproses.');
        }

        $loan->update(['status' => 'batal']);

        $user = $loan->user;
        $book = $loan->book;
        $user->update([
            'notifikasi' => 'ditolak|Mohon maaf, pengajuan pinjam buku "' . $book->title . '" ditolak oleh petugas.'
        ]);

        return back()->with('success', '✅ Pengajuan peminjaman telah ditolak.');
    }

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