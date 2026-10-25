<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\KunjunganController; 
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// ===== HALAMAN LOGIN =====
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// ===== HALAMAN PROFIL (SEMUA ROLE) =====
Route::get('/profil', [ProfileController::class, 'edit'])->name('profil.edit');
Route::put('/profil', [ProfileController::class, 'update'])->name('profil.update');

// ===== RUTE NOTIFIKASI SISWA =====
Route::get('/siswa/cek-notifikasi', function () {
    if (Auth::check()) {
        return response()->json(['notifikasi' => Auth::user()->notifikasi]);
    }
    return response()->json(['notifikasi' => null]);
})->name('siswa.cekNotif');

Route::post('/siswa/hapus-notifikasi', function () {
    User::where('id', Auth::id())->update(['notifikasi' => null]);
    return response()->json(['success' => true]);
})->name('siswa.hapusNotif');

// ===== HALAMAN SISWA & GURU =====
Route::get('/siswa/katalog', [BookController::class, 'katalog'])->name('siswa.katalog');
Route::get('/siswa/api/katalog', [BookController::class, 'getLatestKatalog'])->name('siswa.api.katalog'); 
Route::get('/siswa/buku/{id}', [BookController::class, 'detail'])->name('siswa.detail');
Route::post('/siswa/pinjam/{id}', [BookController::class, 'pinjam'])->name('siswa.pinjam');
Route::get('/siswa/riwayat', [LoanController::class, 'riwayat'])->name('siswa.riwayat');

Route::post('/kunjungan/simpan', [KunjunganController::class, 'simpan'])->name('kunjungan.simpan');


// ===== HALAMAN PETUGAS =====
Route::get('/petugas/dashboard', [App\Http\Controllers\DashboardController::class, 'petugas'])->name('petugas.dashboard');

// Sirkulasi
Route::get('/petugas/sirkulasi', [LoanController::class, 'index'])->name('petugas.sirkulasi');
Route::get('/petugas/api/sirkulasi', [LoanController::class, 'getLatestData'])->name('petugas.api.sirkulasi'); 
Route::post('/petugas/setujui/{id}', [LoanController::class, 'setujui'])->name('petugas.setujui');
Route::post('/petugas/tolak/{id}', [LoanController::class, 'tolak'])->name('petugas.tolak');
Route::post('/petugas/kembalikan/{id}', [LoanController::class, 'kembalikan'])->name('petugas.kembalikan');
Route::post('/petugas/bebaskan-denda/{id}', [LoanController::class, 'bebaskanDenda'])->name('petugas.bebaskanDenda');

// Kelola Buku (CRUD)
Route::get('/petugas/kelola-buku', [BookController::class, 'kelola'])->name('petugas.kelolaBuku');
Route::get('/petugas/kelola-buku/create', [BookController::class, 'create'])->name('petugas.buku.create');
Route::post('/petugas/kelola-buku', [BookController::class, 'store'])->name('petugas.buku.store');
Route::get('/petugas/kelola-buku/{id}/edit', [BookController::class, 'edit'])->name('petugas.buku.edit');
Route::put('/petugas/kelola-buku/{id}', [BookController::class, 'update'])->name('petugas.buku.update');
Route::delete('/petugas/kelola-buku/{id}', [BookController::class, 'destroy'])->name('petugas.buku.destroy');

// Kelola Anggota (CRUD)
Route::get('/petugas/kelola-anggota', [UserController::class, 'index'])->name('petugas.kelolaAnggota');
Route::get('/petugas/kelola-anggota/create', [UserController::class, 'create'])->name('petugas.anggota.create');
Route::post('/petugas/kelola-anggota', [UserController::class, 'store'])->name('petugas.anggota.store');
Route::get('/petugas/kelola-anggota/{id}/edit', [UserController::class, 'edit'])->name('petugas.anggota.edit');
Route::put('/petugas/kelola-anggota/{id}', [UserController::class, 'update'])->name('petugas.anggota.update');
Route::delete('/petugas/kelola-anggota/{id}', [UserController::class, 'destroy'])->name('petugas.anggota.destroy');

// Kelola Kategori (CRUD Baru)
Route::resource('/petugas/kategori', CategoryController::class)->names([
    'index' => 'petugas.kategori.index',
    'create' => 'petugas.kategori.create',
    'store' => 'petugas.kategori.store',
    'edit' => 'petugas.kategori.edit',
    'update' => 'petugas.kategori.update',
    'destroy' => 'petugas.kategori.destroy',
]);

// Scan QR
Route::get('/petugas/scan', [ScanController::class, 'index'])->name('petugas.scan');
Route::post('/petugas/scan', [ScanController::class, 'hasil'])->name('petugas.scan.hasil');
Route::get('/petugas/scan/qr-code/{id}', [ScanController::class, 'qrCode'])->name('petugas.scan.qrCode');

// ===== HALAMAN KEPSEK =====
Route::get('/kepsek/laporan', [ReportController::class, 'laporan'])->name('kepsek.laporan');
Route::get('/kepsek/laporan/cetak-pdf', [ReportController::class, 'cetakPdf'])->name('kepsek.cetakPdf');
Route::post('/api/chatbot', [App\Http\Controllers\ChatbotController::class, 'chat'])->name('api.chatbot');

// ===== HALAMAN DATA PUBLIK UNTUK KNOWLEDGE BASE BOTPRESS =====
Route::get('/informasi-perpustakaan', function () {
    $totalBuku = \App\Models\Book::count();
    $members = \App\Models\User::whereIn('role', ['siswa', 'guru'])->orderBy('role', 'asc')->orderBy('name', 'asc')->get();
    $totalAnggota = $members->count();
    $totalSiswa = $members->where('role', 'siswa')->count();
    $totalGuru = $members->where('role', 'guru')->count();

    $activeLoans = \App\Models\Loan::with(['book', 'user'])->where('status', 'dipinjam')->get();
    $totalDipinjam = $activeLoans->count();

    $html = "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'><title>Informasi Publik Perpustakaan SDN Pengampon 3</title></head><body>";
    $html .= "<h1>Informasi Resmi Perpustakaan Digital SDN Pengampon 3 Cirebon</h1>";
    $html .= "<p>Layanan informasi resmi perpustakaan sekolah SDN Pengampon 3 Kota Cirebon. Kepala Sekolah: Bapak H. Noni Risanto, M.Pd.I. Jam buka: Senin - Jumat pukul 07.30 - 14.00 WIB.</p>";

    $html .= "<h2>Statistik Terkini Perpustakaan</h2>";
    $html .= "<ul>";
    $html .= "<li><strong>Total Buku:</strong> {$totalBuku} judul buku.</li>";
    $html .= "<li><strong>Total Anggota:</strong> {$totalAnggota} orang (terdiri dari {$totalSiswa} siswa dan {$totalGuru} guru).</li>";
    $html .= "<li><strong>Buku Sedang Dipinjam:</strong> {$totalDipinjam} buku.</li>";
    $html .= "</ul>";

    $html .= "<h2>Status Buku yang Sedang Dipinjam</h2>";
    if ($totalDipinjam === 0) {
        $html .= "<p>Saat ini tidak ada buku yang sedang dipinjam (0 buku). Semua koleksi buku di perpustakaan dalam status tersedia dan siap dipinjam oleh siswa maupun guru.</p>";
    } else {
        $html .= "<p>Saat ini terdapat {$totalDipinjam} buku yang sedang dipinjam:</p><ol>";
        foreach ($activeLoans as $loan) {
            $judul = $loan->book->title ?? 'Buku';
            $peminjam = $loan->user->name ?? 'Anggota';
            $kelas = $loan->user->class ? " (Kelas {$loan->user->class})" : "";
            $tgl = \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y');
            $tempo = \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d M Y');
            $html .= "<li>Buku '{$judul}' dipinjam oleh {$peminjam}{$kelas}. Dipinjam tanggal {$tgl}, batas pengembalian {$tempo}.</li>";
        }
        $html .= "</ol>";
    }

    $html .= "<h2>Daftar Anggota Perpustakaan SDN Pengampon 3</h2>";
    $html .= "<p>Total anggota perpustakaan yang terdaftar berjumlah {$totalAnggota} orang. Berikut daftar nama-nama anggota:</p><ul>";
    foreach ($members as $m) {
        $roleInfo = $m->role === 'siswa' && $m->class ? "Siswa Kelas {$m->class}" : ucfirst($m->role);
        $html .= "<li>{$m->name} ({$roleInfo})</li>";
    }
    $html .= "</ul>";

    $html .= "<h2>Tata Cara Peminjaman Buku</h2>";
    $html .= "<p>1. Buka menu Katalog di website perpustakaan. 2. Pilih buku yang diinginkan. 3. Klik tombol Pinjam Buku Ini. 4. Status pinjaman Menunggu konfirmasi Petugas. 5. Datang ke perpustakaan untuk ambil buku fisik dan konfirmasi ke Petugas. Batas waktu pinjam 7 hari, maksimal 2 buku.</p>";
    $html .= "</body></html>";

    return response($html)->header('Content-Type', 'text/html');
});