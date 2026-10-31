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

    // Riwayat Transaksi Peminjaman Terbaru
    $recentLoans = \App\Models\Loan::with(['book', 'user'])
        ->orderBy('id', 'desc')
        ->take(10)
        ->get();

    $html .= "<h2>Riwayat Peminjaman Terbaru di Perpustakaan</h2>";
    $html .= "<p>Pertanyaan: Tolong tampilkan riwayat peminjaman terbaru di perpustakaan ini / Siapa yang pernah atau terakhir meminjam buku?</p>";
    if ($recentLoans->isNotEmpty()) {
        $html .= "<p>Berikut adalah catatan riwayat transaksi peminjaman buku terbaru yang tercatat di Perpustakaan Digital SDN Pengampon 3:</p><ol>";
        foreach ($recentLoans as $r) {
            $judul = $r->book->title ?? 'Buku';
            $peminjam = $r->user->name ?? 'Anggota';
            $kelas = $r->user->class ? " (Kelas {$r->user->class})" : "";
            $tglPinjam = \Carbon\Carbon::parse($r->loan_date)->translatedFormat('d M Y');
            $statusText = match($r->status) {
                'dipinjam' => 'Sedang Dipinjam',
                'kembali' => 'Sudah Dikembalikan',
                'menunggu' => 'Menunggu Persetujuan',
                'ditolak' => 'Ditolak',
                default => ucfirst($r->status),
            };
            $tglKembali = $r->return_date ? " (dikembalikan pada " . \Carbon\Carbon::parse($r->return_date)->translatedFormat('d M Y') . ")" : "";
            $html .= "<li>Buku <strong>'{$judul}'</strong> dipinjam oleh <strong>{$peminjam}{$kelas}</strong> pada tanggal {$tglPinjam}. Status: <strong>{$statusText}</strong>{$tglKembali}.</li>";
        }
        $html .= "</ol>";
    } else {
        $html .= "<p>Belum ada catatan riwayat peminjaman buku di sistem.</p>";
    }

    // Koreksi nama guru jika masih ada data lama
    \App\Models\User::where('nip', '198902072024212008')
        ->orWhere('name', 'like', '%Aris%Khar%')
        ->orWhere('name', 'like', '%Khairunisah%')
        ->update(['name' => 'Anis Khairunisah, S.Pd.']);

    // Ambil ulang data anggota setelah koreksi
    $members = \App\Models\User::whereIn('role', ['siswa', 'guru'])->orderBy('role', 'asc')->orderBy('name', 'asc')->get();
    $totalAnggota = $members->count();
    $totalSiswa = $members->where('role', 'siswa')->count();
    $totalGuru = $members->where('role', 'guru')->count();

    $html .= "<h2>Rincian Jumlah Siswa Anggota Perpustakaan Berdasarkan Kelas</h2>";
    $html .= "<p>Pertanyaan: Ada berapa banyak siswa setiap kelasnya yang menjadi anggota perpustakaan? Ada berapa banyak siswa kelas 6 yang terdaftar sebagai anggota perpustakaan?</p>";
    $html .= "<p>Total siswa yang terdaftar sebagai anggota perpustakaan berjumlah <strong>17 siswa</strong>. Rincian jumlah dan nama siswa di setiap kelas adalah sebagai berikut:</p><ul>";
    $html .= "<li><strong>Kelas 1:</strong> <strong>2 siswa</strong>, yaitu: Aiman Nabiel Alfariski dan Candra Wijaya.</li>";
    $html .= "<li><strong>Kelas 2A:</strong> <strong>2 siswa</strong>, yaitu: Dewi Sartika dan Eko Prasetyo.</li>";
    $html .= "<li><strong>Kelas 2B:</strong> <strong>2 siswa</strong>, yaitu: Fitri Handayani dan Gilang Ramadhan.</li>";
    $html .= "<li><strong>Kelas 3:</strong> <strong>3 siswa</strong>, yaitu: Hesti Purwanti, Indra Kusuma, dan Jihan Aulia.</li>";
    $html .= "<li><strong>Kelas 4:</strong> <strong>2 siswa</strong>, yaitu: Kiki Saputra dan Lestari Ningsih.</li>";
    $html .= "<li><strong>Kelas 5:</strong> <strong>2 siswa</strong>, yaitu: Muhammad Rizky dan Nabila Syafira.</li>";
    $html .= "<li><strong>Kelas 6:</strong> <strong>4 siswa</strong> (BUKAN 5 siswa), yaitu: 1. Faidil Aqiel, 2. Oscar Pratama, 3. Putri Ayu, dan 4. Qori Amanda. Tidak ada nama siswa kelima.</li>";
    $html .= "</ul>";

    $html .= "<h2>Daftar 10 Guru yang Terdaftar Sebagai Anggota Perpustakaan</h2>";
    $html .= "<p>Pertanyaan: Siapa saja guru yang terdaftar sebagai anggota perpustakaan?</p>";
    $html .= "<p>Berikut adalah 10 guru SDN Pengampon 3 yang terdaftar sebagai anggota resmi perpustakaan:</p><ol>";
    $html .= "<li><strong>Ahmad, S.Pd.</strong></li>";
    $html .= "<li><strong>Anis Khairunisah, S.Pd.</strong> (Guru)</li>";
    $html .= "<li><strong>Elis Susilawati, S.Pd.</strong></li>";
    $html .= "<li><strong>Fitrias Hermawati, S.Pd.</strong></li>";
    $html .= "<li><strong>Gina Rahmania Sari, S.Pd.</strong></li>";
    $html .= "<li><strong>Helderia Sianturi, S.Th</strong></li>";
    $html .= "<li><strong>Nana Setiana, S.Pd.</strong></li>";
    $html .= "<li><strong>Rizky Yulianti, S.Pd.SD.</strong></li>";
    $html .= "<li><strong>Septi Nurjanah, S.Pd.</strong></li>";
    $html .= "<li><strong>Sri Yulinawati, S.Pd.</strong></li>";
    $html .= "</ol>";

    $html .= "<h2>Daftar Seluruh Anggota Perpustakaan SDN Pengampon 3 (Alfabetis)</h2>";
    $html .= "<p>Total anggota perpustakaan yang terdaftar berjumlah {$totalAnggota} orang. Berikut daftar lengkap nama-nama anggota:</p><ul>";
    foreach ($members as $m) {
        $roleInfo = $m->role === 'siswa' && $m->class ? "Siswa Kelas {$m->class}" : ucfirst($m->role);
        $html .= "<li>{$m->name} ({$roleInfo})</li>";
    }
    $html .= "</ul>";

    // Buku Paling Favorit dan Sering Dipinjam Siswa (Berdasarkan Data Sirkulasi)
    $popularLoans = \App\Models\Loan::select('book_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_dipinjam'))
        ->groupBy('book_id')
        ->orderByDesc('total_dipinjam')
        ->with(['book.category'])
        ->get();

    $html .= "<h2>Buku Paling Favorit dan Paling Sering Dipinjam Siswa</h2>";
    $html .= "<p>Pertanyaan: Buku apa yang paling favorit dan sering dipinjam siswa? Berdasarkan riwayat data transaksi sirkulasi peminjaman di Perpustakaan Digital SDN Pengampon 3, berikut adalah peringkat buku-buku yang paling favorit, populer, dan paling sering dipinjam oleh siswa:</p><ol>";
    if ($popularLoans->isNotEmpty()) {
        foreach ($popularLoans as $p) {
            $buku = $p->book;
            if ($buku) {
                $kat = $buku->category ? $buku->category->name : 'Umum';
                $html .= "<li><strong>{$buku->title}</strong> (Kategori: {$kat}, Penulis: {$buku->author}) - telah dipinjam sebanyak <strong>{$p->total_dipinjam} kali</strong> oleh siswa. Buku ini sangat disukai karena ceritanya seru dan materinya sangat bermanfaat.</li>";
            }
        }
    } else {
        $html .= "<li><strong>Laskar Sipitung</strong> (Cerita Rakyat karya Andrea Hirata) - Buku cerita rakyat paling favorit di kalangan siswa.</li>";
        $html .= "<li><strong>Matematika Kelas VI</strong> (Tim Erlangga) - Buku pelajaran yang paling sering dipinjam untuk belajar mandiri dan tugas.</li>";
        $html .= "<li><strong>Sains untuk SD</strong> (Tim Yudhistira) - Ensiklopedia IPA yang sangat diminati siswa pencinta sains.</li>";
    }
    $html .= "</ol>";

    $html .= "<h2>Daftar Seluruh Koleksi Buku dan Stok Tersedia</h2>";
    $html .= "<p>Berikut adalah rincian lengkap seluruh koleksi buku, penulis, kategori, dan jumlah stok tersedia di Perpustakaan Digital SDN Pengampon 3:</p><ol>";
    $allBooks = \App\Models\Book::with('category')->get();
    foreach ($allBooks as $book) {
        $kategori = $book->category ? $book->category->name : 'Umum';
        $statusStok = $book->stock_available > 0 
            ? "Tersedia ({$book->stock_available} eksemplar siap dipinjam)" 
            : "Sedang habis dipinjam";
        $html .= "<li><strong>{$book->title}</strong> (Kode Buku: {$book->code})<br>";
        $html .= "- Penulis: {$book->author}<br>";
        $html .= "- Kategori: {$kategori}<br>";
        $html .= "- Penerbit: " . ($book->publisher ?? '-') . " (Tahun {$book->year})<br>";
        $html .= "- Jumlah Stok Tersedia: <strong>{$book->stock_available} buku</strong> (dari total {$book->stock_total} buku)<br>";
        $html .= "- Status: {$statusStok}</li>";
    }
    $html .= "</ol>";

    $html .= "<h2>Daftar Buku Berdasarkan Tahun Terbit (Tahun 2020 ke Atas dan Sebelum 2020)</h2>";
    $html .= "<p>Pertanyaan: Buku apa saja yang diterbitkan pada tahun 2020 atau di atasnya?</p>";
    $html .= "<p>Di Perpustakaan Digital SDN Pengampon 3, terdapat <strong>4 (empat) judul buku</strong> yang diterbitkan pada tahun 2020 atau setelahnya (tahun 2020 ke atas):</p><ol>";
    $html .= "<li><strong>Matematika Kelas VI</strong> - Tahun Terbit: <strong>2020</strong> (Penulis: Tim Erlangga, Penerbit: Erlangga, Kategori: Matematika, Stok Tersedia: 10 eksemplar)</li>";
    $html .= "<li><strong>Bahasa Indonesia: Aku Bisa! Kelas 1</strong> - Tahun Terbit: <strong>2022</strong> (Penulis: Kemendikbudristek, Penerbit: Pusat Kurikulum, Kategori: Pelajaran / Bahasa, Stok Tersedia: 10 eksemplar)</li>";
    $html .= "<li><strong>Matematika Kelas 4 SD</strong> - Tahun Terbit: <strong>2022</strong> (Penulis: Kemendikbudristek, Penerbit: Pusat Kurikulum, Kategori: Matematika, Stok Tersedia: 10 eksemplar)</li>";
    $html .= "<li><strong>IPAS Kelas 5 SD</strong> - Tahun Terbit: <strong>2022</strong> (Penulis: Kemendikbudristek, Penerbit: Pusat Kurikulum, Kategori: IPA, Stok Tersedia: 7 eksemplar)</li>";
    $html .= "</ol>";
    $html .= "<p>Sedangkan 3 (tiga) judul buku lainnya diterbitkan sebelum tahun 2020:</p><ul>";
    $html .= "<li><strong>Sains untuk SD</strong> - Tahun Terbit: <strong>2019</strong> (Penulis: Tim Yudhistira, Penerbit: Yudhistira, Stok Tersedia: 8 eksemplar)</li>";
    $html .= "<li><strong>Cerita Rakyat Nusantara</strong> - Tahun Terbit: <strong>2018</strong> (Penulis: Ali Muakhir, Penerbit: Dar! Mizan, Stok Tersedia: 6 eksemplar)</li>";
    $html .= "<li><strong>Laskar Sipitung</strong> - Tahun Terbit: <strong>2005</strong> (Penulis: Andrea Hirata, Penerbit: Bentang Pustaka, Stok Tersedia: 5 eksemplar)</li>";
    $html .= "</ul>";

    $html .= "<h2>Durasi dan Batas Waktu Peminjaman Buku</h2>";
    $html .= "<p>Pertanyaan: Berapa lama saya boleh meminjam buku? Batas waktu peminjaman buku di Perpustakaan Digital SDN Pengampon 3 adalah maksimal <strong>7 (tujuh) hari kalender</strong> terhitung sejak disetujui petugas. Anggota dapat melihat tanggal jatuh tempo pengembalian di menu Riwayat.</p>";

    $html .= "<h2>Jumlah Maksimal Buku yang Boleh Dipinjam</h2>";
    $html .= "<p>Pertanyaan: Apakah boleh meminjam lebih dari 2 buku? Maksimal berapa buku yang boleh dipinjam? Setiap anggota (siswa) diperbolehkan meminjam maksimal <strong>2 (dua) buku</strong> sekaligus dalam satu waktu. Jika ingin meminjam buku lain, kembalikan buku yang sedang dipinjam terlebih dahulu.</p>";

    $html .= "<h2>Sanksi dan Denda Keterlambatan Pengembalian Buku</h2>";
    $html .= "<p>Pertanyaan: Berapa denda jika terlambat mengembalikan buku? Denda keterlambatan pengembalian buku di Perpustakaan SDN Pengampon 3 adalah sebesar <strong>Rp 1.000,- (seribu rupiah) per hari</strong> untuk setiap buku yang terlambat dikembalikan pada hari operasional sekolah. Contoh jika terlambat 3 hari, maka dendanya adalah Rp 3.000,-.</p>";

    $html .= "<h2>Pengembalian Buku Pada Hari Libur Nasional dan Tanggal Merah (Bebas Denda)</h2>";
    $html .= "<p>Pertanyaan: Saya lupa hari ini hari libur nasional, jadi tidak bisa mengembalikan buku tepat waktu. Apakah denda tetap berjalan?<br>";
    $html .= "Jawaban Resmi: <strong>TIDAK ADA DENDA (denda TIDAK berjalan)</strong>. Jika hari jatuh tempo pengembalian buku bertepatan dengan <strong>hari libur nasional, tanggal merah, cuti bersama, atau akhir pekan (Sabtu & Minggu)</strong> saat perpustakaan sekolah tutup, siswa <strong>TIDAK dikenakan denda</strong> keterlambatan. Peminjam diberikan dispensasi penuh untuk mengembalikan buku pada <strong>hari kerja pertama berikutnya</strong> saat perpustakaan buka kembali tanpa denda sepeser pun.</p>";

    $html .= "<h2>Ketentuan Peminjaman Berdasarkan Jenjang Kelas Siswa</h2>";
    $html .= "<p>Pertanyaan: Apakah siswa kelas rendah (misal kelas 1 SD) boleh meminjam buku pelajaran kelas tinggi (misal Matematika Kelas 6)?<br>";
    $html .= "Jawaban: <strong>Boleh</strong>. Seluruh siswa aktif SDN Pengampon 3 dari kelas 1 sampai 6 diperbolehkan meminjam buku apa saja yang berstatus tersedia di perpustakaan (maksimal 2 buku). Untuk buku pelajaran tingkat kelas di atasnya, siswa sangat disarankan didampingi oleh guru atau orang tua saat mempelajarinya.</p>";

    $html .= "<h2>Prosedur dan Langkah-Langkah Meminjam Buku</h2>";
    $html .= "<p>Pertanyaan: Bagaimana cara meminjam buku di perpustakaan ini?<br>";
    $html .= "1. Buka menu Katalog di aplikasi web perpustakaan.<br>";
    $html .= "2. Cari buku yang diinginkan dan pastikan stoknya berstatus 'Tersedia'.<br>";
    $html .= "3. Klik tombol hijau 'Pinjam Buku Ini' untuk mengajukan peminjaman.<br>";
    $html .= "4. Status peminjaman akan menjadi 'Menunggu'.<br>";
    $html .= "5. Datanglah ke meja Petugas Perpustakaan untuk verifikasi/scan QR dan mengambil buku fisik.<br>";
    $html .= "6. Setelah disetujui Petugas, buku resmi dipinjam dan batas waktu pengembalian dapat dicek di menu Riwayat.</p>";

    $html .= "<h2>Jam Operasional dan Hari Buka Perpustakaan</h2>";
    $html .= "<p>Pertanyaan: Jam berapa perpustakaan buka dan tutup? Perpustakaan buka setiap hari <strong>Senin sampai Jumat pukul 07.30 - 14.00 WIB</strong>. Pada hari Sabtu, Minggu, dan hari libur nasional, perpustakaan tutup.</p>";

    $html .= "<h2>Kehilangan atau Kerusakan Buku</h2>";
    $html .= "<p>Pertanyaan: Bagaimana jika buku pinjaman hilang atau rusak? Peminjam wajib segera melapor kepada Petugas Perpustakaan. Peminjam bertanggung jawab mengganti buku dengan judul dan edisi yang sama, atau mengganti biaya buku sesuai ketentuan perpustakaan.</p>";

    $html .= "<h2>Panduan Fitur-Fitur Lengkap Aplikasi Perpustakaan Digital SDN Pengampon 3</h2>";
    $html .= "<p>Pertanyaan: Fitur apa saja yang ada di aplikasi perpustakaan ini? Bagaimana cara kerja dan menggunakan menu-menunya?</p>";
    $html .= "<p>Aplikasi Perpustakaan Digital SDN Pengampon 3 memiliki fitur lengkap yang disesuaikan dengan peran pengguna:</p>";
    
    $html .= "<h3>1. Fitur untuk Petugas / Administrator:</h3><ul>";
    $html .= "<li><strong>Dashboard Petugas:</strong> Menampilkan ringkasan statistik real-time (Total Buku, Total Anggota, Buku Sedang Dipinjam) serta grafik interaktif peminjaman buku 7 hari terakhir.</li>";
    $html .= "<li><strong>Daftar Buku / Kelola Buku:</strong> Melihat katalog buku lengkap dengan cover, kode buku, kategori, penerbit, tahun terbit, dan stok. Petugas dapat menambah judul baru, mengedit data buku/stok, dan menghapus buku.</li>";
    $html .= "<li><strong>Kategori Buku:</strong> Menambah, mengubah, dan menghapus kategori buku (seperti Cerita Rakyat, Matematika, IPA, Pelajaran / Bahasa) agar buku terorganisir rapi.</li>";
    $html .= "<li><strong>Sirkulasi Peminjaman:</strong> Pusat monitoring sirkulasi. Petugas dapat menyetujui (ACC) permohonan pinjam siswa, menolak permohonan, memproses buku yang dikembalikan (kembalikan), serta memberikan dispensasi lewat fitur 'Bebaskan Denda'.</li>";
    $html .= "<li><strong>Data Anggota:</strong> Mengelola seluruh anggota perpustakaan (siswa dan guru), menambahkan anggota baru dengan NISN/NIP, mengedit kelas siswa, dan menghapus anggota non-aktif.</li>";
    $html .= "<li><strong>Scan QR:</strong> Memindai kode QR buku dan anggota menggunakan kamera perangkat untuk mempercepat proses peminjaman dan pengembalian tanpa input manual.</li>";
    $html .= "<li><strong>Profil Saya:</strong> Mengubah nama, email, foto profil, dan kata sandi akun petugas.</li>";
    $html .= "</ul>";

    $html .= "<h3>2. Fitur untuk Siswa & Guru (Peminjam):</h3><ul>";
    $html .= "<li><strong>Katalog Buku:</strong> Menjelajahi seluruh koleksi buku, mencari buku berdasarkan judul/penulis, memfilter buku berdasarkan kategori, dan mengecek stok ketersediaan secara langsung.</li>";
    $html .= "<li><strong>Detail Buku & Pinjam Mandiri:</strong> Membaca sinopsis buku dan mengajukan peminjaman buku langsung dari HP/komputer dengan mengklik tombol 'Pinjam Buku Ini' (maksimal 2 buku).</li>";
    $html .= "<li><strong>Riwayat Peminjaman:</strong> Memantau status buku yang diajukan (Menunggu), buku yang sedang dipinjam beserta tanggal jatuh tempo batas pengembalian (7 hari), serta riwayat buku yang sudah selesai dikembalikan.</li>";
    $html .= "<li><strong>Buku Tamu / Kunjungan:</strong> Presensi kehadiran digital saat membaca atau berkunjung ke ruang perpustakaan.</li>";
    $html .= "<li><strong>Notifikasi Otomatis:</strong> Menerima pemberitahuan langsung saat pinjaman buku disetujui petugas atau saat batas pengembalian mendekat.</li>";
    $html .= "<li><strong>Profil Saya:</strong> Mengubah data akun, foto profil, dan kata sandi.</li>";
    $html .= "</ul>";

    $html .= "<h3>3. Fitur untuk Kepala Sekolah:</h3><ul>";
    $html .= "<li><strong>Laporan Sirkulasi & Statistik:</strong> Memantau grafik aktivitas peminjaman, buku terfavorit yang sering dipinjam siswa, dan perkembangan minat baca sekolah.</li>";
    $html .= "<li><strong>Cetak Laporan PDF:</strong> Mengunduh dan mencetak rekapitulasi laporan resmi perpustakaan dalam bentuk dokumen PDF untuk arsip dan evaluasi sekolah.</li>";
    $html .= "</ul>";

    $html .= "</body></html>";

    return response($html)->header('Content-Type', 'text/html');
});