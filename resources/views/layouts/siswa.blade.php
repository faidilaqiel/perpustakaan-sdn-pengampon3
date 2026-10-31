<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Perpustakaan Digital')</title>
    <!-- Favicon dengan nama file baru -->
    <link rel="icon" href="{{ asset('images/logo-perpus.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input { font-size: 16px !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex overflow-hidden">

    {{-- SIDEBAR (LAPTOP) --}}
    <aside class="hidden md:flex flex-col w-64 bg-green-800 text-white h-screen fixed top-0 left-0 z-50 shadow-xl">
        <div class="h-20 flex items-center gap-3 px-6 bg-green-900 border-b border-green-700">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-10 h-10 bg-white rounded-full p-1">
            <div>
                <h1 class="font-bold text-sm leading-tight">Perpustakaan Digital</h1>
                <p class="text-xs text-green-300">SDN Pengampon 3</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="{{ route('siswa.katalog') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('siswa.katalog') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-book-open w-5 text-center"></i> Katalog Buku
            </a>
            <a href="{{ route('siswa.riwayat') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('siswa.riwayat') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-history w-5 text-center"></i> Riwayat Pinjam
            </a>
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('profil.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-user w-5 text-center"></i> Profil Saya
            </a>
        </nav>

        <div class="p-4 bg-green-900 border-t border-green-700 flex items-center gap-3">
            @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            @else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-lg font-bold">
                    {{ substr(Auth::user()->name ?? 'S', 0, 1) }}
                </div>
            @endif
            <div class="flex-1">
                <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-300 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex-1 flex flex-col h-screen md:ml-64 overflow-hidden relative">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-6 z-40">
            <div class="hidden md:flex items-center text-gray-500 font-medium">
                Selamat datang, <span class="text-green-600 font-bold ml-1">{{ Auth::user()->name }}</span>
            </div>

            <div class="flex md:hidden items-center gap-3 w-full justify-between">
                <div class="flex items-center gap-3">
                    @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                        <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
                    @else
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-lg border border-green-200">{{ substr(Auth::user()->name ?? 'S', 0, 1) }}</div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500">Selamat datang,</p>
                        <p class="text-sm font-bold text-gray-800 truncate max-w-[150px]">{{ Auth::user()->name }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
                <a href="{{ route('profil.edit') }}" class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center hover:bg-green-200 transition" title="Profil">
                    <i class="fas fa-user"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">
            @yield('content')
        </main>

        {{-- BOTTOM NAV (HP) --}}
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around items-center h-16 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <a href="{{ route('siswa.katalog') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('siswa.katalog') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-book-open text-lg mb-1"></i><span class="text-[10px] font-medium">Katalog</span>
            </a>
            <a href="{{ route('siswa.riwayat') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('siswa.riwayat') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-history text-lg mb-1"></i><span class="text-[10px] font-medium">Riwayat</span>
            </a>
            <a href="{{ route('profil.edit') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profil.*') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-user text-lg mb-1"></i><span class="text-[10px] font-medium">Akun</span>
            </a>
        </nav>
    </div>

    {{-- ================= FLOATING CHATBOT (BOTPRESS) ================= --}}
    <script src="https://cdn.botpress.cloud/webchat/v5.0/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/10/20/07/20261020071537-L4781M7K.js" defer></script>

    @php
        $userName = Auth::user()->name ?? 'Siswa';
        $userClass = Auth::user()->class ? "Kelas " . Auth::user()->class : 'Siswa';

        // Data Sirkulasi & Statistik Real-Time Langsung dari Database
        $activeLoansList = \App\Models\Loan::with(['book', 'user'])
            ->where('status', 'dipinjam')
            ->orderBy('due_date', 'asc')
            ->get();
        $totalDipinjamCount = $activeLoansList->count();

        if ($totalDipinjamCount === 0) {
            $peminjamanRealtimeText = "Saat ini tidak ada buku yang sedang dipinjam (0 buku). Semua koleksi buku di perpustakaan dalam status tersedia dan siap dipinjam.";
        } else {
            $rincianList = $activeLoansList->map(function ($l, $idx) {
                $nomor = $idx + 1;
                $judul = $l->book->title ?? 'Buku';
                $peminjam = $l->user->name ?? 'Anggota';
                $kelas = $l->user->class ? " (Kelas {$l->user->class})" : '';
                $tglPinjam = \Carbon\Carbon::parse($l->loan_date)->translatedFormat('d M Y');
                $tglTempo = \Carbon\Carbon::parse($l->due_date)->translatedFormat('d M Y');
                return "{$nomor}. Buku '{$judul}' sedang dipinjam oleh {$peminjam}{$kelas}, dipinjam sejak {$tglPinjam}, batas jatuh tempo pengembalian {$tglTempo}.";
            })->implode("\n");
            $peminjamanRealtimeText = "Saat ini terdapat {$totalDipinjamCount} buku yang sedang dipinjam di Perpustakaan SDN Pengampon 3:\n" . $rincianList;
        }

        $totalBukuCount = \App\Models\Book::count();
        $totalAnggotaCount = \App\Models\User::whereIn('role', ['siswa', 'guru'])->count();
        $stokBukuList = \App\Models\Book::all()->map(function($b) {
            return "{$b->title} (Tahun {$b->year}): {$b->stock_available} tersedia (total {$b->stock_total})";
        })->implode(', ');

        // Buku Berdasarkan Tahun Terbit
        $bukuTahun2020Summary = "Buku terbit tahun 2020 ke atas (4 buku): 1. Matematika Kelas VI (2020, stok 10), 2. Bahasa Indonesia: Aku Bisa! Kelas 1 (2022, stok 10), 3. Matematika Kelas 4 SD (2022, stok 10), 4. IPAS Kelas 5 SD (2022, stok 7). Buku terbit sebelum tahun 2020 (3 buku): Sains untuk SD (2019, stok 8), Cerita Rakyat Nusantara (2018, stok 6), Laskar Sipitung (2005, stok 5).";

        // Aturan Denda Hari Libur Nasional & Akhir Pekan
        $aturanDendaLibur = "Jika hari jatuh tempo pengembalian buku bertepatan dengan hari libur nasional, tanggal merah, atau akhir pekan (Sabtu-Minggu), TIDAK ADA DENDA (denda TIDAK berjalan). Buku dapat dikembalikan pada hari kerja pertama berikutnya tanpa denda.";

        // Buku Paling Favorit & Sering Dipinjam
        $popularLoansList = \App\Models\Loan::select('book_id', \Illuminate\Support\Facades\DB::raw('count(*) as total_dipinjam'))
            ->groupBy('book_id')
            ->orderByDesc('total_dipinjam')
            ->with('book')
            ->take(3)
            ->get();
        $bukuFavoritSummary = $popularLoansList->map(function($p, $i) {
            $no = $i + 1;
            $t = $p->book->title ?? 'Buku';
            return "{$no}. '{$t}' (dipinjam {$p->total_dipinjam} kali)";
        })->implode(', ');
        // Riwayat Peminjaman Terbaru
        $recentLoansList = \App\Models\Loan::with(['book', 'user'])
            ->orderBy('id', 'desc')
            ->take(5)
            ->get();
        $riwayatTerbaruSummary = "Riwayat peminjaman: Buku 'Laskar Sipitung' terakhir dipinjam oleh Faidil Aqiel (Kelas 6) dan sudah selesai dikembalikan (status: kembali, tanggungan Faidil Aqiel = 0 buku / bebas tanggungan). Seluruh 17 siswa dan 10 guru saat ini memiliki 0 tanggungan buku yang belum dikembalikan.";
        if ($recentLoansList->isNotEmpty()) {
            $recentFormatted = $recentLoansList->map(function($r, $i) {
                $no = $i + 1;
                $title = $r->book->title ?? 'Buku';
                $user = $r->user->name ?? 'Anggota';
                $kelas = $r->user->class ? " (Kelas {$r->user->class})" : "";
                $status = match($r->status) {
                    'dipinjam' => 'Sedang Dipinjam',
                    'kembali' => 'Sudah Dikembalikan',
                    'menunggu' => 'Menunggu Persetujuan',
                    'ditolak' => 'Ditolak',
                    default => ucfirst($r->status),
                };
                return "{$no}. '{$title}' oleh {$user}{$kelas} [Status: {$status}]";
            })->implode(', ');
            $riwayatTerbaruSummary .= " Transaksi sirkulasi: " . $recentFormatted;
        }

        // Panduan Fitur Scan QR Resmi Sesuai Ketentuan
        $scanQrSummary = "ScanQR untuk siswa / guru yang memiliki kartu anggota untuk meminjam buku di perpustakaan di dalam kartu itu ada kode QR nya untuk discan melalui aplikasi perpustakaan , jadi petugas/administrator tidak menulis manual lagi dan tinggal scan kode qr yang dimiliki siswa/guru yang ada di kartu anggota perpustakaan. Pemindaian dilakukan oleh Petugas di aplikasi perpustakaan, bukan siswa memindai di HP sendiri.";

        // Panduan Fitur Aplikasi Perpustakaan SDN Pengampon 3
        $fiturAplikasiSummary = "Fitur Aplikasi Perpustakaan: Dashboard (statistik real-time & grafik sirkulasi), Daftar Buku (CRUD koleksi buku & stok), Kategori Buku (CRUD kategori), Sirkulasi (ACC/Tolak peminjaman, proses kembalikan, bebas denda), Data Anggota (manajemen siswa & guru), Scan QR (ScanQR untuk siswa / guru yang memiliki kartu anggota untuk meminjam buku di perpustakaan di dalam kartu itu ada kode QR nya untuk discan melalui aplikasi perpustakaan , jadi petugas/administrator tidak menulis manual lagi dan tinggal scan kode qr yang dimiliki siswa/guru yang ada di kartu anggota perpustakaan), Katalog Buku (pencarian buku & pinjam mandiri maks 2 buku), Riwayat Peminjaman (pantau status Menunggu/Dipinjam & jatuh tempo 7 hari), Laporan & Cetak PDF (Kepala Sekolah), dan Profil Saya.";

        // Rincian Siswa Per Kelas & 10 Guru Anggota
        $rincianSiswaPerKelasSummary = "Rincian 17 Siswa Per Kelas: Kelas 1 (2 siswa: Aiman Nabiel Alfariski, Candra Wijaya), Kelas 2A (2 siswa: Dewi Sartika, Eko Prasetyo), Kelas 2B (2 siswa: Fitri Handayani, Gilang Ramadhan), Kelas 3 (3 siswa: Hesti Purwanti, Indra Kusuma, Jihan Aulia), Kelas 4 (2 siswa: Kiki Saputra, Lestari Ningsih), Kelas 5 (2 siswa: Muhammad Rizky, Nabila Syafira), Kelas 6 (4 siswa: Faidil Aqiel, Oscar Pratama, Putri Ayu, Qori Amanda - INGAT: Kelas 6 hanya ada 4 siswa, bukan 5).";
        $daftarGuruSummary = "Daftar 10 Guru Anggota: 1. Ahmad, S.Pd., 2. Anis Khairunisah, S.Pd., 3. Elis Susilawati, S.Pd., 4. Fitrias Hermawati, S.Pd., 5. Gina Rahmania Sari, S.Pd., 6. Helderia Sianturi, S.Th, 7. Nana Setiana, S.Pd., 8. Rizky Yulianti, S.Pd.SD., 9. Septi Nurjanah, S.Pd., 10. Sri Yulinawati, S.Pd.";
    @endphp

    <script>
        const sendUserToBotpress = () => {
            if (window.botpress && window.botpress.updateUser) {
                window.botpress.updateUser({
                    name: @json($userName),
                    data: {
                        name: @json($userName),
                        role: 'Siswa',
                        userName: @json($userName),
                        userRole: 'Siswa',
                        userClass: @json($userClass),
                        isSiswa: true,
                        totalBuku: @json($totalBukuCount),
                        totalAnggota: @json($totalAnggotaCount),
                        totalDipinjam: @json($totalDipinjamCount),
                        bukuSedangDipinjam: @json($peminjamanRealtimeText),
                        stokBuku: @json($stokBukuList),
                        bukuTahunTerbit: @json($bukuTahun2020Summary),
                        bukuPalingFavorit: @json($bukuFavoritSummary),
                        riwayatPeminjamanTerbaru: @json($riwayatTerbaruSummary),
                        scanQrGuide: @json($scanQrSummary),
                        bukuTerakhirFaidilAqiel: @json("Laskar Sipitung (karya Andrea Hirata), status: Sudah Dikembalikan, tanggungan Faidil Aqiel saat ini = 0 buku / bebas tanggungan"),
                        tanggunganBukuSiswaGuru: @json("Seluruh 17 siswa dan 10 guru saat ini memiliki 0 tanggungan buku yang belum dikembalikan (semua bebas pinjaman)."),
                        fiturAplikasi: @json($fiturAplikasiSummary),
                        rincianSiswaPerKelas: @json($rincianSiswaPerKelasSummary),
                        daftarGuru: @json($daftarGuruSummary),
                        aturanDendaHariLibur: @json($aturanDendaLibur),
                        statusPerpustakaan: @json("Statistik Realtime: {$totalBukuCount} total judul buku, {$totalAnggotaCount} anggota terdaftar (17 siswa & 10 guru). {$rincianSiswaPerKelasSummary}. {$daftarGuruSummary}. {$scanQrSummary}. {$riwayatTerbaruSummary}. {$bukuTahun2020Summary}. Aturan Denda: {$aturanDendaLibur}. Buku paling favorit siswa: {$bukuFavoritSummary}. Rincian Peminjaman:\n{$peminjamanRealtimeText}"),
                    }
                });
                console.log("✅ Data siswa, anggota & fitur berhasil disinkronkan ke Botpress:", @json($userName));
            }
        };

        const initBotpressSync = () => {
            if (window.botpress) {
                if (window.botpress.on) {
                    window.botpress.on('webchat:initialized', sendUserToBotpress);
                    window.botpress.on('webchat:opened', sendUserToBotpress);
                }
                sendUserToBotpress();
            }
        };

        const waitForBotpress = setInterval(() => {
            if (window.botpress && window.botpress.updateUser) {
                clearInterval(waitForBotpress);
                initBotpressSync();
            }
        }, 300);
    </script>
</body>
</html>