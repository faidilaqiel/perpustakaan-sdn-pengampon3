<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Book;
use App\Models\Loan;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index(Request $request)
    {
        $books = Book::where('stock_available', '>', 0)
                     ->with('category')
                     ->orderBy('title')
                     ->get();

        $selectedMember = null;
        $activeLoansCount = 0;

        if ($request->has('user_id')) {
            $selectedMember = User::find($request->user_id);
            if ($selectedMember) {
                $activeLoansCount = Loan::where('user_id', $selectedMember->id)
                                        ->where('status', 'dipinjam')
                                        ->count();
            }
        }

        return view('petugas.scan.index', compact('books', 'selectedMember', 'activeLoansCount'));
    }

    // Proses setelah QR discan atau NISN diketik via Form
    public function hasil(Request $request)
    {
        $request->validate([
            'qr_code' => 'required'
        ]);

        $qrCode = trim($request->qr_code);

        // Cari user berdasarkan NISN, NIP, atau ID
        $user = User::where('nisn', $qrCode)
                    ->orWhere('nip', $qrCode)
                    ->first();

        if (!$user && is_numeric($qrCode)) {
            $user = User::find($qrCode);
        }

        if (!$user) {
            return back()->with('error', '❌ QR Code / NISN / NIP tidak dikenali! Data anggota tidak ditemukan.');
        }

        // Catat kunjungan ke database (Buku Tamu)
        Kunjungan::create([
            'user_id' => $user->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_masuk' => Carbon::now()->toTimeString(),
            'keterangan' => 'Datang ke Perpustakaan (Scan Kartu)',
        ]);

        $books = Book::where('stock_available', '>', 0)
                     ->with('category')
                     ->orderBy('title')
                     ->get();

        $activeLoansCount = Loan::where('user_id', $user->id)
                                ->where('status', 'dipinjam')
                                ->count();

        return view('petugas.scan.index', [
            'books' => $books,
            'selectedMember' => $user,
            'activeLoansCount' => $activeLoansCount,
            'success' => 'Berhasil, ' . $user->name,
        ]);
    }

    // Endpoint AJAX untuk scan kamera langsung tanpa reload
    public function cekAnggota(Request $request)
    {
        $request->validate([
            'qr_code' => 'required'
        ]);

        $qrCode = trim($request->qr_code);

        $user = User::where('nisn', $qrCode)
                    ->orWhere('nip', $qrCode)
                    ->first();

        if (!$user && is_numeric($qrCode)) {
            $user = User::find($qrCode);
        }

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => '❌ QR Code tidak dikenali! Data anggota tidak ditemukan.',
            ], 404);
        }

        // Catat kunjungan
        Kunjungan::create([
            'user_id' => $user->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_masuk' => Carbon::now()->toTimeString(),
            'keterangan' => 'Datang ke Perpustakaan (Scan Kartu)',
        ]);

        $activeLoansCount = Loan::where('user_id', $user->id)
                                ->where('status', 'dipinjam')
                                ->count();

        $books = Book::where('stock_available', '>', 0)
                     ->with('category')
                     ->orderBy('title')
                     ->get()
                     ->map(function ($b) {
                         return [
                             'id' => $b->id,
                             'code' => $b->code,
                             'title' => $b->title,
                             'author' => $b->author,
                             'category' => $b->category ? $b->category->name : 'Umum',
                             'stock_available' => $b->stock_available,
                         ];
                     });

        return response()->json([
            'success' => true,
            'message' => 'Berhasil, ' . $user->name,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'role' => $user->role,
                'class' => $user->class,
                'nisn' => $user->nisn,
                'nip' => $user->nip,
                'active_loans' => $activeLoansCount,
            ],
            'books' => $books,
        ]);
    }

    // Eksekusi Peminjaman Buku dari Hasil Scan
    public function pinjamBuku(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'book_id' => 'required|exists:books,id',
            'loan_date' => 'nullable|date',
            'due_date' => 'nullable|date',
        ]);

        $user = User::findOrFail($request->user_id);
        $book = Book::findOrFail($request->book_id);

        // 1. Cek stok buku
        if ($book->stock_available <= 0) {
            $msg = '❌ Stok buku "' . $book->title . '" sudah habis!';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 2. Cek apakah anggota sedang meminjam buku yang sama
        $cekPinjamSama = Loan::where('user_id', $user->id)
                             ->where('book_id', $book->id)
                             ->where('status', 'dipinjam')
                             ->first();
        if ($cekPinjamSama) {
            $msg = '❌ ' . $user->name . ' sedang meminjam buku ini dan belum mengembalikannya.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        // 3. Cek batas maksimal pinjaman (misal 2 buku untuk siswa)
        $activeLoans = Loan::where('user_id', $user->id)->where('status', 'dipinjam')->count();
        if ($activeLoans >= 2 && $user->role === 'siswa') {
            $msg = '❌ ' . $user->name . ' sudah meminjam batas maksimal 2 buku aktif.';
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            return back()->with('error', $msg);
        }

        $loanDate = $request->loan_date ? Carbon::parse($request->loan_date)->format('Y-m-d') : Carbon::now()->format('Y-m-d');
        $dueDate = $request->due_date ? Carbon::parse($request->due_date)->format('Y-m-d') : Carbon::now()->addDays(7)->format('Y-m-d');

        // Kurangi stok buku
        $book->decrement('stock_available');

        // Buat loan langsung berstatus 'dipinjam'
        $loan = Loan::create([
            'user_id' => $user->id,
            'book_id' => $book->id,
            'loan_date' => $loanDate,
            'due_date' => $dueDate,
            'status' => 'dipinjam',
        ]);

        // Catat/update kunjungan
        Kunjungan::firstOrCreate([
            'user_id' => $user->id,
            'tanggal' => Carbon::now()->toDateString(),
        ], [
            'jam_masuk' => Carbon::now()->toTimeString(),
            'keterangan' => 'Meminjam buku: ' . $book->title,
        ]);

        // Update notifikasi siswa
        $user->update([
            'notifikasi' => 'disetujui|Buku "' . $book->title . '" berhasil dipinjamkan oleh petugas. Jatuh tempo: ' . Carbon::parse($dueDate)->translatedFormat('d F Y')
        ]);

        $popupMessage = 'Buku ' . $book->title . ' berhasil dipinjamkan kepada ' . $user->name;

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => $popupMessage,
                'book_title' => $book->title,
                'user_name' => $user->name,
                'due_date' => Carbon::parse($dueDate)->translatedFormat('d F Y'),
            ]);
        }

        return redirect()->route('petugas.scan')->with([
            'loan_success' => true,
            'loan_book_title' => $book->title,
            'loan_user_name' => $user->name,
            'loan_due_date' => Carbon::parse($dueDate)->translatedFormat('d F Y'),
            'success' => $popupMessage,
        ]);
    }

    // Tampilkan QR Code Anggota (untuk dicetak)
    public function qrCode($id)
    {
        $user = User::findOrFail($id);

        // Pengaman jika NISN/NIP kosong
        $qrContent = $user->nisn ?? $user->nip;
        if (empty($qrContent)) {
            return back()->with('error', 'Gagal membuat QR Code: Siswa/Guru ini belum memiliki NISN atau NIP.');
        }

        // Generate QR Code SVG langsung via BaconQrCode (tidak bergantung facade/CDN)
        try {
            $fill = \BaconQrCode\Renderer\RendererStyle\Fill::uniformColor(
                new \BaconQrCode\Renderer\Color\Rgb(255, 255, 255),  // background putih
                new \BaconQrCode\Renderer\Color\Rgb(6, 95, 70)       // modul hijau gelap
            );
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(280, 1, null, null, $fill),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $svgRaw  = $writer->writeString($qrContent);

            // Hapus XML declaration agar bisa di-embed inline di HTML
            $qrSvg = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $svgRaw);
        } catch (\Exception $e) {
            $qrSvg = null;
        }

        return view('petugas.scan.qr-code', compact('user', 'qrContent', 'qrSvg'));
    }
}
