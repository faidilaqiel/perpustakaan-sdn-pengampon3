<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Models\Book;
use App\Models\User;
use App\Models\Loan;
use Carbon\Carbon;

// Endpoint koleksi buku untuk integrasi Botpress AI Knowledge Base
Route::get('/books', function () {
    $books = Book::with(['category', 'loans' => function ($q) {
        $q->where('status', 'dipinjam')->with('user');
    }])->get()->map(function ($book) {
        $kategori = $book->category ? $book->category->name : 'Umum';
        $activeLoans = $book->loans;
        $numLoaned = $activeLoans->count();

        if ($numLoaned > 0) {
            $peminjamList = $activeLoans->map(function ($l) {
                $nama = $l->user->name ?? 'Anggota';
                $kelas = $l->user->class ? ' Kelas ' . $l->user->class : '';
                return "{$nama}{$kelas}";
            })->implode(', ');
            $statusPinjam = "Sedang dipinjam sebanyak {$numLoaned} buku oleh: {$peminjamList}.";
        } else {
            $statusPinjam = "Tidak ada eksemplar buku ini yang sedang dipinjam.";
        }

        $statusStok = $book->stock_available > 0 
            ? "Tersedia ({$book->stock_available} buku siap dipinjam dari total {$book->stock_total} buku)" 
            : "Sedang habis dipinjam";

        return [
            'id' => $book->id,
            'kode_buku' => $book->code,
            'judul' => $book->title,
            'penulis' => $book->author,
            'penerbit' => $book->publisher ?? '-',
            'tahun' => $book->year ?? '-',
            'kategori' => $kategori,
            'stok_tersedia' => $book->stock_available,
            'stok_total' => $book->stock_total,
            'sedang_dipinjam' => $numLoaned,
            'status_peminjaman_terkini' => $statusPinjam,
            'status' => $statusStok,
            'deskripsi_ai' => "Buku '{$book->title}' karya {$book->author}, kategori {$kategori}, kode {$book->code}. Stok tersedia: {$book->stock_available} dari total {$book->stock_total}. {$statusPinjam}",
        ];
    });

    return response()->json([
        'status' => 'success',
        'total' => $books->count(),
        'data' => $books,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Endpoint informasi buku yang sedang dipinjam (sirkulasi aktif)
Route::get('/loans-active', function () {
    $activeLoans = Loan::with(['book', 'user'])
        ->where('status', 'dipinjam')
        ->orderBy('due_date', 'asc')
        ->get();

    $count = $activeLoans->count();

    if ($count === 0) {
        $narasi = "Saat ini tidak ada buku yang sedang dipinjam (0 buku sedang dipinjam). Semua koleksi buku perpustakaan berstatus tersedia untuk dipinjam.";
    } else {
        $items = $activeLoans->map(function ($loan, $index) {
            $nomor = $index + 1;
            $judul = $loan->book->title ?? 'Buku';
            $peminjam = $loan->user->name ?? 'Anggota';
            $kelas = $loan->user->class ? " (Kelas {$loan->user->class})" : '';
            $tglPinjam = Carbon::parse($loan->loan_date)->translatedFormat('d M Y');
            $jatuhTempo = Carbon::parse($loan->due_date)->translatedFormat('d M Y');
            return "{$nomor}. '{$judul}' dipinjam oleh {$peminjam}{$kelas}, dipinjam sejak {$tglPinjam}, jatuh tempo {$jatuhTempo}.";
        })->implode("\n");

        $narasi = "Saat ini terdapat {$count} buku yang sedang dipinjam di Perpustakaan SDN Pengampon 3:\n" . $items;
    }

    return response()->json([
        'status' => 'success',
        'total_sedang_dipinjam' => $count,
        'ringkasan_ai' => $narasi,
        'data' => $activeLoans->map(function ($loan) {
            return [
                'buku' => $loan->book->title ?? '-',
                'kode_buku' => $loan->book->code ?? '-',
                'peminjam' => $loan->user->name ?? '-',
                'peran' => $loan->user->role ?? '-',
                'kelas' => $loan->user->class ?? '-',
                'tanggal_pinjam' => $loan->loan_date,
                'jatuh_tempo' => $loan->due_date,
            ];
        }),
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Endpoint data anggota perpustakaan (siswa & guru)
Route::get('/members', function () {
    $members = User::whereIn('role', ['siswa', 'guru'])
        ->orderBy('role', 'asc')
        ->orderBy('class', 'asc')
        ->orderBy('name', 'asc')
        ->get(['id', 'name', 'role', 'class', 'nisn', 'nip']);

    $totalSiswa = $members->where('role', 'siswa')->count();
    $totalGuru = $members->where('role', 'guru')->count();
    $totalAnggota = $members->count();

    $namaAnggota = $members->map(function ($m) {
        $ket = $m->role === 'siswa' && $m->class ? " (Siswa Kelas {$m->class})" : " (" . ucfirst($m->role) . ")";
        return $m->name . $ket;
    })->implode(", ");

    $narasi = "Perpustakaan Digital SDN Pengampon 3 memiliki total {$totalAnggota} anggota terdaftar (terdiri dari {$totalSiswa} siswa dan {$totalGuru} guru). Anggota yang terdaftar antara lain: " . $namaAnggota . ".";

    return response()->json([
        'status' => 'success',
        'total_anggota' => $totalAnggota,
        'total_siswa' => $totalSiswa,
        'total_guru' => $totalGuru,
        'ringkasan_ai' => $narasi,
        'data' => $members,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Endpoint data statistik lengkap perpustakaan
Route::get('/statistics', function () {
    $totalBuku = Book::count();
    $totalAnggota = User::whereIn('role', ['siswa', 'guru'])->count();
    $sedangDipinjam = Loan::where('status', 'dipinjam')->count();
    $terlambat = Loan::where('status', 'dipinjam')
        ->where('due_date', '<', Carbon::now()->format('Y-m-d'))
        ->count();

    $narasi = "Statistik resmi Perpustakaan SDN Pengampon 3 saat ini: Total Koleksi Buku ada {$totalBuku} judul, Total Anggota aktif ada {$totalAnggota} orang, Buku yang sedang dipinjam ada {$sedangDipinjam} buku, dan Buku terlambat ada {$terlambat} buku.";

    return response()->json([
        'status' => 'success',
        'total_buku' => $totalBuku,
        'total_anggota' => $totalAnggota,
        'sedang_dipinjam' => $sedangDipinjam,
        'terlambat' => $terlambat,
        'ringkasan_ai' => $narasi,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Endpoint ringkasan menyeluruh (Knowledge Data Lengkap) untuk Botpress
Route::get('/perpustakaan-data', function () {
    $totalBuku = Book::count();
    $members = User::whereIn('role', ['siswa', 'guru'])->get();
    $totalAnggota = $members->count();
    $activeLoans = Loan::with(['book', 'user'])->where('status', 'dipinjam')->get();
    $totalDipinjam = $activeLoans->count();

    $pinjamText = $totalDipinjam === 0 
        ? "Saat ini tidak ada buku yang sedang dipinjam (0 buku dipinjam)." 
        : "Ada {$totalDipinjam} buku sedang dipinjam: " . $activeLoans->map(fn($l) => "'{$l->book->title}' oleh {$l->user->name}")->implode(', ');

    $anggotaText = "Total anggota perpustakaan adalah {$totalAnggota} orang. Daftar anggota: " . $members->pluck('name')->implode(', ');

    $allBooks = Book::with('category')->get();
    $bukuText = $allBooks->map(function ($b) {
        $kat = $b->category ? $b->category->name : 'Umum';
        return "Buku '{$b->title}' (Kode {$b->code}, Penulis {$b->author}, Kategori {$kat}): Stok tersedia {$b->stock_available} buku dari total {$b->stock_total} buku.";
    })->implode("\n");

    return response()->json([
        'status' => 'success',
        'nama_perpustakaan' => 'Perpustakaan Digital SDN Pengampon 3',
        'total_buku' => $totalBuku,
        'total_anggota' => $totalAnggota,
        'sedang_dipinjam' => $totalDipinjam,
        'status_peminjaman_terkini' => $pinjamText,
        'data_anggota_terkini' => $anggotaText,
        'koleksi_buku_dan_stok' => $bukuText,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});

// Endpoint informasi resmi perpustakaan untuk Botpress AI Context (terhubung ke Tool getLibraryInfo)
Route::get('/library-info', function () {
    $activeLoans = Loan::with(['book', 'user'])
        ->where('status', 'dipinjam')
        ->orderBy('due_date', 'asc')
        ->get();

    $totalDipinjam = $activeLoans->count();

    if ($totalDipinjam === 0) {
        $peminjamanRealtime = "Saat ini tidak ada buku yang sedang dipinjam (0 buku). Semua koleksi buku di perpustakaan dalam status tersedia dan siap dipinjam.";
    } else {
        $daftarPinjam = $activeLoans->map(function ($loan, $index) {
            $nomor = $index + 1;
            $judul = $loan->book->title ?? 'Buku';
            $peminjam = $loan->user->name ?? 'Anggota';
            $kelas = $loan->user->class ? " (Kelas {$loan->user->class})" : '';
            $tglPinjam = Carbon::parse($loan->loan_date)->translatedFormat('d M Y');
            $jatuhTempo = Carbon::parse($loan->due_date)->translatedFormat('d M Y');
            return "{$nomor}. Buku '{$judul}' sedang dipinjam oleh {$peminjam}{$kelas}, dipinjam sejak {$tglPinjam}, batas jatuh tempo pengembalian {$jatuhTempo}.";
        })->implode("\n");

        $peminjamanRealtime = "Saat ini terdapat {$totalDipinjam} buku yang sedang dipinjam di Perpustakaan SDN Pengampon 3:\n" . $daftarPinjam;
    }

    $members = User::whereIn('role', ['siswa', 'guru'])->get();
    $totalAnggota = $members->count();
    $totalSiswa = $members->where('role', 'siswa')->count();
    $totalGuru = $members->where('role', 'guru')->count();

    $info = [
        'nama_perpustakaan' => 'Perpustakaan Digital SDN Pengampon 3',
        'nama_bot' => 'PustakaAI',
        'sekolah' => 'SDN Pengampon 3 Cirebon',
        'kepala_sekolah' => 'Bapak H. Noni Risanto, M.Pd.I.',
        'jam_operasional' => 'Senin - Jumat: 07.30 - 14.00 WIB (Sabtu, Minggu & Hari Libur Nasional Tutup)',
        'statistik_perpustakaan' => [
            'total_buku' => Book::count(),
            'total_anggota' => $totalAnggota,
            'total_siswa' => $totalSiswa,
            'total_guru' => $totalGuru,
            'buku_sedang_dipinjam' => $totalDipinjam,
        ],
        'buku_yang_sedang_dipinjam_saat_ini' => $peminjamanRealtime,
        'rincian_peminjaman_aktif' => $activeLoans->map(function ($loan) {
            return [
                'judul_buku' => $loan->book->title ?? '-',
                'kode_buku' => $loan->book->code ?? '-',
                'nama_peminjam' => $loan->user->name ?? '-',
                'kelas' => $loan->user->class ?? '-',
                'tanggal_pinjam' => $loan->loan_date,
                'jatuh_tempo' => $loan->due_date,
            ];
        }),
        'aturan_peminjaman' => [
            'syarat' => 'Terdaftar sebagai siswa/guru/staf aktif SDN Pengampon 3.',
            'durasi_peminjaman' => 'Maksimal 7 (tujuh) hari kalender per buku.',
            'maksimal_buku' => 'Siswa dapat meminjam maksimal 2 (dua) buku sekaligus dalam satu waktu.',
            'denda_keterlambatan' => 'Denda keterlambatan adalah Rp 1.000,- (seribu rupiah) per hari untuk setiap buku yang terlambat.',
            'sanksi_kehilangan_atau_kerusakan' => 'Wajib segera melapor ke petugas perpustakaan dan mengganti buku dengan judul yang sama atau membayar biaya penggantian buku.',
            'pengembalian' => 'Pengembalian buku diserahkan kepada petugas perpustakaan untuk diverifikasi / scan QR.',
            'langkah_meminjam_di_aplikasi' => [
                '1. Buka menu Katalog di website/aplikasi perpustakaan.',
                '2. Cari dan klik buku yang ingin dipinjam (pastikan status stok tersedia).',
                '3. Klik tombol hijau "Pinjam Buku Ini" untuk mengajukan peminjaman.',
                '4. Status pinjaman akan menjadi "Menunggu". Datanglah ke ruang perpustakaan untuk mengambil buku fisik dan meminta konfirmasi Petugas.',
                '5. Setelah disetujui Petugas, buku resmi dipinjam dan batas pengembalian dapat dipantau di menu Riwayat.',
            ],
            'ringkasan_cara_pinjam' => 'Untuk meminjam buku: Buka menu Katalog -> Pilih buku yang tersedia -> Klik tombol "Pinjam Buku Ini" -> Datang ke perpustakaan untuk verifikasi dan ambil buku fisik dari Petugas.',
        ],
        'kontak' => [
            'email' => 'sdpengampon3cirebon@gmail.com',
            'telepon' => '0231232048',
            'alamat' => 'SDN Pengampon 3, Kota Cirebon, Jawa Barat',
        ],
    ];

    return response()->json([
        'status' => 'success',
        'data' => $info,
    ])->header('Access-Control-Allow-Origin', '*')
      ->header('Access-Control-Allow-Methods', 'GET, POST, OPTIONS')
      ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
});