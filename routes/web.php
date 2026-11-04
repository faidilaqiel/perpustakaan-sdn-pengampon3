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
Route::get('/siswa/api/riwayat', [LoanController::class, 'apiRiwayat'])->name('siswa.api.riwayat');

Route::post('/kunjungan/simpan', [KunjunganController::class, 'simpan'])->name('kunjungan.simpan');


// ===== HALAMAN PETUGAS =====
Route::get('/petugas/dashboard', [App\Http\Controllers\DashboardController::class, 'petugas'])->name('petugas.dashboard');
Route::get('/petugas/api/dashboard-stats', [App\Http\Controllers\DashboardController::class, 'apiStats'])->name('petugas.api.dashboardStats');

// Sirkulasi
Route::get('/petugas/sirkulasi', [LoanController::class, 'index'])->name('petugas.sirkulasi');
Route::get('/petugas/api/sirkulasi', [LoanController::class, 'getLatestData'])->name('petugas.api.sirkulasi'); 
Route::post('/petugas/setujui/{id}', [LoanController::class, 'setujui'])->name('petugas.setujui');
Route::post('/petugas/tolak/{id}', [LoanController::class, 'tolak'])->name('petugas.tolak');
Route::post('/petugas/kembalikan/{id}', [LoanController::class, 'kembalikan'])->name('petugas.kembalikan');
Route::post('/petugas/bebaskan-denda/{id}', [LoanController::class, 'bebaskanDenda'])->name('petugas.bebaskanDenda');
Route::post('/petugas/wa/send-reminders', [LoanController::class, 'sendWaReminders'])->name('petugas.wa.sendReminders');
Route::post('/petugas/wa/send-single/{id}', [LoanController::class, 'sendSingleWa'])->name('petugas.wa.sendSingle');

// API Notifikasi Petugas (polling)
Route::get('/petugas/api/cek-notifikasi', function () {
    $pending = \App\Models\Loan::with(['user', 'book'])
        ->where('status', 'menunggu')
        ->orderBy('created_at', 'desc')
        ->get()
        ->map(function ($loan) {
            return [
                'id' => $loan->id,
                'user_name' => $loan->user->name ?? 'User',
                'user_class' => $loan->user->class ?? '',
                'book_title' => $loan->book->title ?? 'Buku',
                'created_at' => $loan->created_at->diffForHumans(),
                'created_at_raw' => $loan->created_at->timestamp,
            ];
        });
    return response()->json([
        'count' => $pending->count(),
        'items' => $pending,
    ]);
})->name('petugas.api.cekNotifikasi');

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
Route::post('/petugas/scan/pinjam', [ScanController::class, 'pinjamBuku'])->name('petugas.scan.pinjam');
Route::post('/petugas/scan/cek-anggota', [ScanController::class, 'cekAnggota'])->name('petugas.scan.cekAnggota');
Route::get('/petugas/scan/qr-code/{id}', [ScanController::class, 'qrCode'])->name('petugas.scan.qrCode');

// ===== HALAMAN KEPSEK =====
Route::get('/kepsek/laporan', [ReportController::class, 'laporan'])->name('kepsek.laporan');
Route::get('/kepsek/laporan/cetak-pdf', [ReportController::class, 'cetakPdf'])->name('kepsek.cetakPdf');
Route::post('/api/chatbot', [App\Http\Controllers\ChatbotController::class, 'chat'])->name('api.chatbot');

// ===== HALAMAN DATA PUBLIK UNTUK KNOWLEDGE BASE BOTPRESS =====
// ⚡ FULLY DYNAMIC: Semua data diambil LANGSUNG dari database secara real-time.
// Tidak ada data yang di-hardcode. Setiap perubahan data anggota/buku
// akan langsung tercermin di sini saat Botpress mengakses URL ini.
Route::get('/informasi-perpustakaan', function () {

    // ── Data dasar ──────────────────────────────────────────────────────────
    $totalBuku     = \App\Models\Book::count();
    $members       = \App\Models\User::whereIn('role', ['siswa', 'guru'])->orderBy('role')->orderBy('name')->get();
    $totalAnggota  = $members->count();
    $totalSiswa    = $members->where('role', 'siswa')->count();
    $totalGuru     = $members->where('role', 'guru')->count();
    $activeLoans   = \App\Models\Loan::with(['book', 'user'])->where('status', 'dipinjam')->get();
    $totalDipinjam = $activeLoans->count();

    $html  = "<!DOCTYPE html><html lang='id'><head><meta charset='UTF-8'>";
    $html .= "<title>Informasi Perpustakaan SDN Pengampon 3</title></head><body>";
    $html .= "<h1>Informasi Resmi Perpustakaan Digital SDN Pengampon 3 Cirebon</h1>";
    $html .= "<p>Layanan informasi resmi perpustakaan sekolah SDN Pengampon 3 Kota Cirebon. ";
    $html .= "Kepala Sekolah: Bapak H. Noni Risanto, M.Pd.I. Jam buka: Senin-Jumat pukul 07.30-14.00 WIB.</p>";
    $html .= "<p><em>⚡ Data pada halaman ini diperbarui secara REAL-TIME langsung dari database perpustakaan.</em></p>";

    // ── Statistik ───────────────────────────────────────────────────────────
    $html .= "<h2>Statistik Terkini Perpustakaan</h2><ul>";
    $html .= "<li><strong>Total Buku:</strong> {$totalBuku} judul buku.</li>";
    $html .= "<li><strong>Total Anggota:</strong> {$totalAnggota} orang ({$totalSiswa} siswa dan {$totalGuru} guru).</li>";
    $html .= "<li><strong>Buku Sedang Dipinjam:</strong> {$totalDipinjam} buku.</li>";
    $html .= "</ul>";

    // ── Status Buku Dipinjam ─────────────────────────────────────────────────
    $html .= "<h2>Status Buku yang Sedang Dipinjam</h2>";
    if ($totalDipinjam === 0) {
        $html .= "<p>Saat ini tidak ada buku yang sedang dipinjam (0 buku). Semua koleksi tersedia dan siap dipinjam.</p>";
    } else {
        $html .= "<p>Saat ini terdapat {$totalDipinjam} buku yang sedang dipinjam:</p><ol>";
        foreach ($activeLoans as $loan) {
            $judul    = $loan->book->title ?? 'Buku';
            $peminjam = $loan->user->name  ?? 'Anggota';
            $kelas    = $loan->user->class  ? " (Kelas {$loan->user->class})" : "";
            $tgl      = \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y');
            $tempo    = \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d M Y');
            $html    .= "<li>Buku '{$judul}' dipinjam oleh {$peminjam}{$kelas}. Dipinjam: {$tgl}, jatuh tempo: {$tempo}.</li>";
        }
        $html .= "</ol>";
    }

    // ── Riwayat Peminjaman Terbaru ───────────────────────────────────────────
    $recentLoans = \App\Models\Loan::with(['book', 'user'])->orderBy('id', 'desc')->take(20)->get();
    $html .= "<h2>Riwayat Peminjaman Terbaru di Perpustakaan</h2>";
    $html .= "<p>Pertanyaan: Siapa yang pernah atau terakhir meminjam buku?</p>";
    if ($recentLoans->isNotEmpty()) {
        $html .= "<ol>";
        foreach ($recentLoans as $r) {
            $judul      = $r->book->title ?? 'Buku';
            $peminjam   = $r->user->name  ?? 'Anggota';
            $kelas      = $r->user->class  ? " (Kelas {$r->user->class})" : "";
            $tglPinjam  = \Carbon\Carbon::parse($r->loan_date)->translatedFormat('d M Y');
            $statusText = match($r->status) {
                'dipinjam' => 'Sedang Dipinjam',
                'kembali'  => 'Sudah Dikembalikan',
                'menunggu' => 'Menunggu Persetujuan',
                'ditolak'  => 'Ditolak',
                default    => ucfirst($r->status),
            };
            $tglKembali = $r->return_date
                ? " (dikembalikan: " . \Carbon\Carbon::parse($r->return_date)->translatedFormat('d M Y') . ")"
                : "";
            $html .= "<li>Buku <strong>'{$judul}'</strong> dipinjam oleh <strong>{$peminjam}{$kelas}</strong> pada {$tglPinjam}. Status: <strong>{$statusText}</strong>{$tglKembali}.</li>";
        }
        $html .= "</ol>";
    } else {
        $html .= "<p>Belum ada riwayat peminjaman.</p>";
    }

    // ── Riwayat & Tanggungan Per Anggota (DYNAMIC) ──────────────────────────
    $html .= "<h2>Riwayat dan Status Tanggungan Pinjaman Per Anggota</h2>";
    $html .= "<p>Pertanyaan: Apakah [nama] punya tanggungan buku? Buku apa yang terakhir dipinjam [nama]?</p><ul>";
    $semuaAnggota = \App\Models\User::whereIn('role', ['siswa', 'guru'])->orderBy('name')->get();
    foreach ($semuaAnggota as $anggota) {
        $pinjamAktif    = \App\Models\Loan::where('user_id', $anggota->id)->where('status', 'dipinjam')->count();
        $pinjamTerakhir = \App\Models\Loan::with('book')->where('user_id', $anggota->id)->orderBy('id', 'desc')->first();
        $kelasInfo      = $anggota->class ? " (Kelas {$anggota->class})" : "";
        $roleInfo       = $anggota->role === 'siswa' ? 'Siswa' : 'Guru';
        if ($pinjamTerakhir) {
            $judulTerakhir  = $pinjamTerakhir->book->title ?? 'Tidak diketahui';
            $statusTerakhir = match($pinjamTerakhir->status) {
                'dipinjam' => 'Sedang Dipinjam / BELUM DIKEMBALIKAN',
                'kembali'  => 'Sudah Dikembalikan',
                'menunggu' => 'Menunggu Persetujuan',
                'ditolak'  => 'Ditolak',
                default    => ucfirst($pinjamTerakhir->status),
            };
            $tanggungan = $pinjamAktif > 0
                ? "MEMILIKI TANGGUNGAN {$pinjamAktif} buku yang belum dikembalikan"
                : "Bebas tanggungan (0 buku aktif)";
            $html .= "<li><strong>{$anggota->name} ({$roleInfo}{$kelasInfo}):</strong> Buku terakhir: '{$judulTerakhir}', status: {$statusTerakhir}. Tanggungan: {$tanggungan}.</li>";
        } else {
            $html .= "<li><strong>{$anggota->name} ({$roleInfo}{$kelasInfo}):</strong> Belum pernah meminjam buku. Bebas tanggungan.</li>";
        }
    }
    $html .= "</ul>";

    // ── Rincian Siswa Per Kelas (DYNAMIC) ───────────────────────────────────
    $html .= "<h2>Rincian Jumlah Siswa Per Kelas (Real-Time dari Database)</h2>";
    $html .= "<p>Pertanyaan: Ada berapa banyak siswa setiap kelasnya? Siapa saja siswa kelas [X]? Apakah ada siswa bernama [nama]?</p>";
    $html .= "<p>Total siswa terdaftar: <strong>{$totalSiswa} siswa</strong>.</p><ul>";
    $siswaPerKelas = \App\Models\User::where('role', 'siswa')
        ->orderBy('class')->orderBy('name')->get()->groupBy('class');
    foreach ($siswaPerKelas as $kelas => $listSiswa) {
        $namaList   = $listSiswa->pluck('name')->join(', ');
        $jumlah     = $listSiswa->count();
        $kelasLabel = $kelas ? "Kelas {$kelas}" : "Kelas tidak diketahui";
        $html .= "<li><strong>{$kelasLabel}:</strong> {$jumlah} siswa, yaitu: {$namaList}.</li>";
    }
    $html .= "</ul>";

    // ── Daftar Guru (DYNAMIC) ────────────────────────────────────────────────
    $html .= "<h2>Daftar Guru Anggota Perpustakaan (Real-Time dari Database)</h2>";
    $html .= "<p>Pertanyaan: Siapa saja guru yang terdaftar sebagai anggota perpustakaan? Apakah ada guru bernama [nama]?</p>";
    $guruList = \App\Models\User::where('role', 'guru')->orderBy('name')->get();
    $html .= "<p>Terdapat {$totalGuru} guru yang terdaftar:</p><ol>";
    foreach ($guruList as $guru) {
        $html .= "<li><strong>{$guru->name}</strong></li>";
    }
    $html .= "</ol>";

    // ── Daftar Seluruh Anggota (DYNAMIC) ────────────────────────────────────
    $html .= "<h2>Daftar Seluruh Anggota Perpustakaan (Alfabetis, Real-Time)</h2>";
    $html .= "<p>Pertanyaan: Apakah ada anggota bernama [nama]? Siapa saja anggota perpustakaan?</p>";
    $html .= "<p>Total: {$totalAnggota} anggota.</p><ul>";
    foreach ($members as $m) {
        $roleInfo = ($m->role === 'siswa' && $m->class) ? "Siswa Kelas {$m->class}" : ucfirst($m->role);
        $html .= "<li>{$m->name} ({$roleInfo})</li>";
    }
    $html .= "</ul>";

    // ── Buku Terpopuler (DYNAMIC) ────────────────────────────────────────────
    $popularLoans = \App\Models\Loan::select('book_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_dipinjam'))
        ->groupBy('book_id')->orderByDesc('total_dipinjam')->with(['book.category'])->get();
    $html .= "<h2>Buku Paling Sering Dipinjam</h2><ol>";
    if ($popularLoans->isNotEmpty()) {
        foreach ($popularLoans as $p) {
            $buku = $p->book;
            if ($buku) {
                $kat   = $buku->category ? $buku->category->name : 'Umum';
                $html .= "<li><strong>{$buku->title}</strong> (Kategori: {$kat}, Penulis: {$buku->author}) - dipinjam <strong>{$p->total_dipinjam} kali</strong>.</li>";
            }
        }
    } else {
        $html .= "<li>Belum ada data peminjaman.</li>";
    }
    $html .= "</ol>";

    // ── Koleksi Buku Lengkap (DYNAMIC) ──────────────────────────────────────
    $html .= "<h2>Daftar Seluruh Koleksi Buku dan Stok Tersedia</h2>";
    $html .= "<p>Berikut adalah rincian lengkap seluruh koleksi buku di Perpustakaan SDN Pengampon 3:</p><ol>";
    $allBooks = \App\Models\Book::with('category')->orderBy('title')->get();
    foreach ($allBooks as $book) {
        $kategori   = $book->category ? $book->category->name : 'Umum';
        $statusStok = $book->stock_available > 0
            ? "Tersedia ({$book->stock_available} eksemplar siap dipinjam)"
            : "Sedang habis dipinjam";
        $html .= "<li><strong>{$book->title}</strong> (Kode: {$book->code}) — Penulis: {$book->author}, Kategori: {$kategori}, Penerbit: " . ($book->publisher ?? '-') . " ({$book->year}), Stok: {$book->stock_available}/{$book->stock_total}, Status: {$statusStok}.</li>";
    }
    $html .= "</ol>";

    // ── Buku per Tahun Terbit (DYNAMIC) ─────────────────────────────────────
    $html .= "<h2>Buku Berdasarkan Tahun Terbit</h2>";
    $bukuTahunBaru = \App\Models\Book::with('category')->where('year', '>=', 2020)->orderBy('year')->get();
    $bukuTahunLama = \App\Models\Book::with('category')->where('year', '<', 2020)->orderByDesc('year')->get();
    $html .= "<p>Buku terbitan tahun 2020 ke atas ({$bukuTahunBaru->count()} judul):</p><ol>";
    foreach ($bukuTahunBaru as $b) {
        $kat  = $b->category ? $b->category->name : 'Umum';
        $html .= "<li><strong>{$b->title}</strong> — Tahun: {$b->year}, Penulis: {$b->author}, Kategori: {$kat}, Stok: {$b->stock_available} eksemplar.</li>";
    }
    $html .= "</ol>";
    if ($bukuTahunLama->isNotEmpty()) {
        $html .= "<p>Buku terbitan sebelum 2020 ({$bukuTahunLama->count()} judul):</p><ul>";
        foreach ($bukuTahunLama as $b) {
            $html .= "<li><strong>{$b->title}</strong> — Tahun: {$b->year}, Penulis: {$b->author}, Stok: {$b->stock_available} eksemplar.</li>";
        }
        $html .= "</ul>";
    }

    // ── Kebijakan & Panduan Umum ─────────────────────────────────────────────
    $html .= "<h2>Durasi dan Batas Waktu Peminjaman Buku</h2>";
    $html .= "<p>Batas waktu peminjaman: maksimal <strong>7 (tujuh) hari kalender</strong> sejak disetujui petugas. Cek jatuh tempo di menu Riwayat.</p>";

    $html .= "<h2>Jumlah Maksimal Buku yang Boleh Dipinjam</h2>";
    $html .= "<p>Setiap anggota diperbolehkan meminjam maksimal <strong>2 (dua) buku</strong> sekaligus.</p>";

    $html .= "<h2>Sanksi dan Denda Keterlambatan Pengembalian Buku</h2>";
    $html .= "<p>Denda keterlambatan: <strong>Rp 1.000,- per hari</strong> per buku yang terlambat pada hari operasional sekolah.</p>";

    $html .= "<h2>Pengembalian Buku Pada Hari Libur Nasional (Bebas Denda)</h2>";
    $html .= "<p>Jika jatuh tempo bertepatan dengan hari libur nasional, tanggal merah, cuti bersama, atau akhir pekan (Sabtu-Minggu), <strong>TIDAK ADA DENDA</strong>. Pengembalian dilakukan pada hari kerja pertama berikutnya tanpa denda.</p>";

    $html .= "<h2>Ketentuan Peminjaman Berdasarkan Jenjang Kelas</h2>";
    $html .= "<p>Seluruh siswa dari kelas 1 sampai 6 boleh meminjam buku apa saja yang tersedia (maksimal 2 buku).</p>";

    $html .= "<h2>Prosedur Meminjam Buku</h2>";
    $html .= "<p>1. Buka menu Katalog → cari buku → klik 'Pinjam Buku Ini'.<br>";
    $html .= "2. Status jadi 'Menunggu'. 3. Datang ke petugas untuk verifikasi/scan QR dan ambil buku fisik.<br>";
    $html .= "4. Setelah disetujui, buku resmi dipinjam (cek jatuh tempo di menu Riwayat).</p>";

    $html .= "<h2>Jam Operasional dan Hari Buka Perpustakaan</h2>";
    $html .= "<p>Perpustakaan buka <strong>Senin-Jumat, pukul 07.30-14.00 WIB</strong>. Tutup pada Sabtu, Minggu, dan hari libur nasional.</p>";

    $html .= "<h2>Kehilangan atau Kerusakan Buku</h2>";
    $html .= "<p>Peminjam wajib melapor ke petugas dan bertanggung jawab mengganti buku dengan judul/edisi yang sama.</p>";

    $html .= "<h2>Panduan Fitur Scan QR</h2>";
    $html .= "<p>Fitur Scan QR digunakan petugas untuk memverifikasi kartu anggota. QR Code dipindai oleh petugas menggunakan kamera HP/laptop petugas atau scanner perpustakaan. Siswa/guru cukup menunjukkan kartu anggota mereka ke petugas. Siswa tidak memindai QR sendiri dari HP mereka.</p>";

    $html .= "</body></html>";

    return response($html)->header('Content-Type', 'text/html');
});

// Auto migration route
Route::get('/run-migration-auto', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
        return response()->json([
            'status' => 'success',
            'output' => \Illuminate\Support\Facades\Artisan::output(),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});

// ===== CRON TRIGGER: Pengingat Jatuh Tempo WA (dipanggil dari cron-job.org setiap jam 08:00 WIB) =====
// URL: https://perpustakaan-sdn-pengampon3-production.up.railway.app/cron/wa-reminders?key=sdn-pengampon3-cron-2026
// Daftarkan URL ini di https://cron-job.org dengan schedule: setiap hari jam 08:00 WIB (01:00 UTC)
Route::get('/cron/wa-reminders', function (\Illuminate\Http\Request $request) {
    // Proteksi sederhana dengan key
    if ($request->query('key') !== 'sdn-pengampon3-cron-2026') {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    try {
        \Illuminate\Support\Facades\Artisan::call('wa:send-reminders');
        $output = \Illuminate\Support\Facades\Artisan::output();

        return response()->json([
            'status' => 'success',
            'timestamp' => now()->timezone('Asia/Jakarta')->format('Y-m-d H:i:s T'),
            'output' => trim($output),
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => $e->getMessage(),
        ], 500);
    }
});
