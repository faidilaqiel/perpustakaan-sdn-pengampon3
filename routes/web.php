<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\ReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ScanController;
use App\Http\Controllers\KunjunganController; 
use Illuminate\Support\Facades\Auth;
use App\Models\User;

// ===== HALAMAN LOGIN =====
Route::get('/', [AuthController::class, 'showLogin']);
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

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

// Rute Riwayat Pinjam Siswa
Route::get('/siswa/riwayat', [LoanController::class, 'riwayat'])->name('siswa.riwayat');

// Rute Log Kunjungan (Buku Tamu)
Route::post('/kunjungan/simpan', [KunjunganController::class, 'simpan'])->name('kunjungan.simpan');


// ===== HALAMAN PETUGAS =====
Route::get('/petugas/dashboard', function () {
    return view('petugas.dashboard');
})->name('petugas.dashboard');

// Sirkulasi (Auto-Refresh)
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

// Scan QR
Route::get('/petugas/scan', [ScanController::class, 'index'])->name('petugas.scan');
Route::post('/petugas/scan', [ScanController::class, 'hasil'])->name('petugas.scan.hasil');
Route::get('/petugas/scan/qr-code/{id}', [ScanController::class, 'qrCode'])->name('petugas.scan.qrCode');

// ===== HALAMAN KEPSEK =====
Route::get('/kepsek/laporan', [ReportController::class, 'laporan'])->name('kepsek.laporan');
Route::get('/kepsek/laporan/cetak-pdf', [ReportController::class, 'cetakPdf'])->name('kepsek.cetakPdf');