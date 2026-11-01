{{-- ================= FLOATING CHATBOT (BOTPRESS REAL-TIME SYNC) ================= --}}
<script src="https://cdn.botpress.cloud/webchat/v5.0/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/10/20/07/20261020071537-L4781M7K.js" defer></script>

@php
use App\Models\Book;
use App\Models\User;
use App\Models\Loan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

// ── 1. Identifikasi Pengguna yang Sedang Login ───────────────────
$loggedInUser = Auth::user();
$userName     = $loggedInUser ? $loggedInUser->name : 'Pengunjung';
$userClass    = $loggedInUser?->class;

$userRole = match (strtolower($loggedInUser?->role ?? '')) {
    'petugas' => 'Petugas Perpustakaan',
    'kepsek'  => 'Kepala Sekolah',
    'guru'    => 'Guru',
    'siswa'   => 'Siswa Kelas ' . ($userClass ?? '-'),
    default   => 'Pengguna',
};

$roleLabel = $userRole;
$isPetugas = $loggedInUser?->role === 'petugas';
$isKepsek  = $loggedInUser?->role === 'kepsek';
$isGuru    = $loggedInUser?->role === 'guru';
$isSiswa   = $loggedInUser?->role === 'siswa';

$currentUserGreeting = "Pengguna yang sedang menggunakan website saat ini bernama {$userName}, dengan peran resmi: {$userRole}.";
if ($isPetugas) {
    $currentUserGreeting .= " PENTING: {$userName} adalah PETUGAS PERPUSTAKAAN yang mengelola sirkulasi dan data perpustakaan, BUKAN siswa!";
} elseif ($isKepsek) {
    $currentUserGreeting .= " PENTING: {$userName} adalah KEPALA SEKOLAH SDN Pengampon 3.";
}

// ── 2. Data Anggota Real-Time dari Database ─────────────────────
try {
    $allMembers        = User::whereIn('role', ['siswa', 'guru'])->orderBy('role')->orderBy('name')->get();
    $totalAnggotaCount = $allMembers->count();
    $siswaList         = $allMembers->where('role', 'siswa');
    $guruList          = $allMembers->where('role', 'guru');
    $totalSiswaCount   = $siswaList->count();
    $totalGuruCount    = $guruList->count();

    // Rincian Siswa Per Kelas (100% Dynamic dari DB)
    $siswaPerKelas = $siswaList->sortBy('class')->groupBy('class');
    $rincianParts  = [];
    foreach ($siswaPerKelas as $kelas => $listSiswa) {
        $jumlah   = $listSiswa->count();
        $namaList = $listSiswa->sortBy('name')->pluck('name')->implode(', ');
        $kelasLabel = $kelas ? "Kelas {$kelas}" : "Kelas tidak terdata";
        $rincianParts[] = "{$kelasLabel} ({$jumlah} siswa: {$namaList})";
    }
    $rincianSiswaPerKelasSummary = "Rincian {$totalSiswaCount} Siswa Per Kelas (Real-Time DB): " . implode('; ', $rincianParts) . ".";

    // Daftar Guru (100% Dynamic dari DB)
    $guruNames = $guruList->sortBy('name')->pluck('name')->values();
    $daftarGuruParts = $guruNames->map(function($name, $i) {
        return ($i + 1) . ". {$name}";
    })->implode(', ');
    $daftarGuruSummary = "Daftar {$totalGuruCount} Guru Anggota (Real-Time DB): {$daftarGuruParts}.";

    // Daftar Seluruh Anggota (Alfabetis)
    $daftarAnggotaList = $allMembers->map(function($m) {
        $r = $m->role === 'siswa' ? ('Siswa' . ($m->class ? " Kelas {$m->class}" : '')) : 'Guru';
        return "{$m->name} ({$r})";
    })->implode(', ');

    // ── 3. Koleksi Buku & Stok (Real-Time) ──────────────────────────
    $totalBukuCount = Book::count();
    $allBooks       = Book::with('category')->orderBy('title')->get();
    $stokBukuList   = $allBooks->map(function($b) {
        return "{$b->title} (Tahun {$b->year}): {$b->stock_available} tersedia (total {$b->stock_total})";
    })->implode(', ');

    // Buku Berdasarkan Tahun Terbit (Dynamic)
    $bukuBaru = $allBooks->where('year', '>=', 2020)->sortBy('year');
    $bukuLama = $allBooks->where('year', '<', 2020)->sortByDesc('year');
    $bukuTahun2020Summary = "Buku terbit tahun 2020 ke atas ({$bukuBaru->count()} judul): "
        . $bukuBaru->map(fn($b) => "{$b->title} ({$b->year}, stok {$b->stock_available})")->values()->implode(', ')
        . ". Buku terbit sebelum tahun 2020 ({$bukuLama->count()} judul): "
        . $bukuLama->map(fn($b) => "{$b->title} ({$b->year}, stok {$b->stock_available})")->values()->implode(', ');

    // ── 4. Status Sirkulasi & Buku Sedang Dipinjam ──────────────────
    $activeLoansList    = Loan::with(['book', 'user'])->where('status', 'dipinjam')->orderBy('due_date', 'asc')->get();
    $totalDipinjamCount = $activeLoansList->count();

    if ($totalDipinjamCount === 0) {
        $peminjamanRealtimeText = "Saat ini tidak ada buku yang sedang dipinjam (0 buku). Semua koleksi buku di perpustakaan dalam status tersedia dan siap dipinjam.";
    } else {
        $rincianList = $activeLoansList->map(function ($l, $idx) {
            $nomor    = $idx + 1;
            $judul    = $l->book->title ?? 'Buku';
            $peminjam = $l->user->name ?? 'Anggota';
            $kelas    = $l->user->class ? " (Kelas {$l->user->class})" : '';
            $tglPinjam = Carbon::parse($l->loan_date)->translatedFormat('d M Y');
            $tglTempo  = Carbon::parse($l->due_date)->translatedFormat('d M Y');
            return "{$nomor}. Buku '{$judul}' sedang dipinjam oleh {$peminjam}{$kelas}, dipinjam sejak {$tglPinjam}, batas jatuh tempo pengembalian {$tglTempo}.";
        })->implode("\n");
        $peminjamanRealtimeText = "Saat ini terdapat {$totalDipinjamCount} buku yang sedang dipinjam di Perpustakaan SDN Pengampon 3:\n" . $rincianList;
    }

    // ── 5. Buku Paling Favorit & Riwayat Transaksi ──────────────────
    $popularLoansList = Loan::select('book_id', DB::raw('count(*) as total_dipinjam'))
        ->groupBy('book_id')->orderByDesc('total_dipinjam')->with('book')->take(5)->get();
    $bukuFavoritSummary = $popularLoansList->isEmpty()
        ? "Belum ada data peminjaman buku."
        : $popularLoansList->map(function($p, $i) {
            $no = $i + 1;
            $t  = $p->book->title ?? 'Buku';
            return "{$no}. '{$t}' (dipinjam {$p->total_dipinjam} kali)";
        })->implode(', ');

    $recentLoansList = Loan::with(['book', 'user'])->orderBy('id', 'desc')->take(10)->get();
    if ($recentLoansList->isNotEmpty()) {
        $recentFormatted = $recentLoansList->map(function($r, $i) {
            $no    = $i + 1;
            $title = $r->book->title ?? 'Buku';
            $user  = $r->user->name ?? 'Anggota';
            $kelas = $r->user->class ? " (Kelas {$r->user->class})" : "";
            $status = match($r->status) {
                'dipinjam' => 'Sedang Dipinjam',
                'kembali'  => 'Sudah Dikembalikan',
                'menunggu' => 'Menunggu Persetujuan',
                'ditolak'  => 'Ditolak',
                default    => ucfirst($r->status),
            };
            return "{$no}. '{$title}' oleh {$user}{$kelas} [Status: {$status}]";
        })->implode(', ');
        $riwayatTerbaruSummary = "Transaksi sirkulasi terbaru: " . $recentFormatted;
    } else {
        $riwayatTerbaruSummary = "Belum ada riwayat transaksi peminjaman di perpustakaan.";
    }

    // ── 6. Tanggungan Peminjaman Per Anggota ────────────────────────
    $tanggunganParts = [];
    $adaTanggungan   = false;
    foreach ($allMembers as $m) {
        $aktif = Loan::where('user_id', $m->id)->where('status', 'dipinjam')->count();
        $lastLoan = Loan::with('book')->where('user_id', $m->id)->orderBy('id', 'desc')->first();
        $roleLbl = $m->role === 'siswa' ? 'Siswa' : 'Guru';
        $kelasLbl = $m->class ? " Kelas {$m->class}" : "";

        if ($aktif > 0) {
            $adaTanggungan = true;
            $lastTitle = $lastLoan->book->title ?? 'Tidak diketahui';
            $tanggunganParts[] = "{$m->name} ({$roleLbl}{$kelasLbl}): MEMILIKI TANGGUNGAN {$aktif} buku belum dikembalikan, terakhir pinjam '{$lastTitle}'";
        }
    }
    $tanggunganBukuSummary = $adaTanggungan
        ? implode('. ', $tanggunganParts) . "."
        : "Seluruh {$totalSiswaCount} siswa dan {$totalGuruCount} guru saat ini memiliki 0 tanggungan buku aktif (semua bebas pinjaman).";
} catch (\Throwable $e) {
    $totalAnggotaCount = 0;
    $totalSiswaCount = 0;
    $totalGuruCount = 0;
    $totalBukuCount = 0;
    $totalDipinjamCount = 0;
    $stokBukuList = '';
    $bukuTahun2020Summary = '';
    $peminjamanRealtimeText = 'Semua buku tersedia.';
    $bukuFavoritSummary = 'Belum ada data.';
    $riwayatTerbaruSummary = 'Belum ada data.';
    $tanggunganBukuSummary = 'Bebas tanggungan.';
    $rincianSiswaPerKelasSummary = '';
    $daftarGuruSummary = '';
    $daftarAnggotaList = '';
}

// ── 7. Panduan Fitur & Kebijakan Perpustakaan ───────────────────
$aturanDendaLibur = "Jika hari jatuh tempo pengembalian buku bertepatan dengan hari libur nasional, tanggal merah, atau akhir pekan (Sabtu-Minggu), TIDAK ADA DENDA (denda TIDAK berjalan). Buku dapat dikembalikan pada hari kerja pertama berikutnya tanpa denda.";

$scanQrSummary = "Fitur Scan QR di perpustakaan digunakan oleh Petugas untuk meminjamkan buku secara otomatis kepada siswa atau guru yang memiliki kartu anggota fisik dengan kode QR. Siswa/guru tidak memindai sendiri di HP; petugas yang memindai QR di meja sirkulasi sehingga tidak perlu menulis manual di buku besar, dan rekapannya otomatis tersimpan serta masuk ke laporan Kepala Sekolah.";

$fiturAplikasiSummary = "Fitur Perpustakaan Digital: Dashboard Petugas, Sirkulasi (ACC/Tolak/Kembalikan buku), Kelola Buku, Kelola Kategori, Kelola Anggota, Scan QR Meja Sirkulasi, Katalog & Pinjam Mandiri Siswa, Riwayat Peminjaman Siswa, Buku Tamu/Kunjungan, Laporan & Cetak PDF Kepala Sekolah, Profil Pengguna.";

$statusPerpustakaan = "Statistik Terkini: {$totalBukuCount} total buku, {$totalAnggotaCount} anggota aktif ({$totalSiswaCount} siswa & {$totalGuruCount} guru). "
    . "{$currentUserGreeting} "
    . "{$rincianSiswaPerKelasSummary} {$daftarGuruSummary} "
    . "Daftar seluruh anggota: {$daftarAnggotaList}. "
    . "{$scanQrSummary} {$riwayatTerbaruSummary} "
    . "{$bukuTahun2020Summary}. {$aturanDendaLibur} "
    . "Peminjaman Terkini: {$peminjamanRealtimeText}";
@endphp

@php
    $botpressUserName = $userName ?? 'Pengguna';
    if ($isPetugas) {
        $botpressUserName = ($userName ?? 'Petugas') . ' (Petugas Perpustakaan)';
    } elseif ($isKepsek) {
        $botpressUserName = ($userName ?? 'Kepala Sekolah') . ' (Kepala Sekolah)';
    } elseif ($isGuru) {
        $botpressUserName = ($userName ?? 'Guru') . ' (Guru)';
    }
@endphp

<script>
    const sendUserToBotpress = () => {
        if (window.botpress && typeof window.botpress.updateUser === 'function') {
            window.botpress.updateUser({
                name: @json($botpressUserName),
                data: {
                    name: @json($botpressUserName),
                    realName: @json($userName ?? 'Pengguna'),
                    role: @json($userRole ?? 'Pengguna'),
                    userName: @json($userName ?? 'Pengguna'),
                    userRole: @json($userRole ?? 'Pengguna'),
                    peranResmi: @json($isPetugas ? 'Petugas / Administrator Perpustakaan' : ($userRole ?? 'Pengguna')),
                    bukanSiswa: @json($isPetugas || $isKepsek || $isGuru),
                    userClass: @json($userClass ?? ''),
                    isPetugas: @json($isPetugas ?? false),
                    isKepsek: @json($isKepsek ?? false),
                    isGuru: @json($isGuru ?? false),
                    isSiswa: @json($isSiswa ?? false),
                    currentUserGreeting: @json($currentUserGreeting ?? ''),
                    totalBuku: @json($totalBukuCount ?? 0),
                    totalAnggota: @json($totalAnggotaCount ?? 0),
                    totalSiswa: @json($totalSiswaCount ?? 0),
                    totalGuru: @json($totalGuruCount ?? 0),
                    totalDipinjam: @json($totalDipinjamCount ?? 0),
                    bukuSedangDipinjam: @json($peminjamanRealtimeText ?? ''),
                    stokBuku: @json($stokBukuList ?? ''),
                    bukuTahunTerbit: @json($bukuTahun2020Summary ?? ''),
                    bukuPalingFavorit: @json($bukuFavoritSummary ?? ''),
                    riwayatPeminjamanTerbaru: @json($riwayatTerbaruSummary ?? ''),
                    scanQrGuide: @json($scanQrSummary ?? ''),
                    tanggunganBukuSiswaGuru: @json($tanggunganBukuSummary ?? ''),
                    fiturAplikasi: @json($fiturAplikasiSummary ?? ''),
                    rincianSiswaPerKelas: @json($rincianSiswaPerKelasSummary ?? ''),
                    daftarGuru: @json($daftarGuruSummary ?? ''),
                    daftarSeluruhAnggota: @json($daftarAnggotaList ?? ''),
                    aturanDendaHariLibur: @json($aturanDendaLibur ?? ''),
                    statusPerpustakaan: @json($statusPerpustakaan ?? ''),
                }
            });
            console.log("✅ Data user real-time disinkronkan ke Botpress:", @json($userName ?? 'Pengguna'), "(Role:", @json($userRole ?? 'Pengguna'), ")");
        }
    };

    const initBotpressSync = () => {
        if (window.botpress) {
            if (typeof window.botpress.on === 'function') {
                window.botpress.on('webchat:initialized', sendUserToBotpress);
                window.botpress.on('webchat:opened', sendUserToBotpress);
            }
            sendUserToBotpress();
        }
    };

    let bpAttempts = 0;
    const waitForBotpress = setInterval(() => {
        bpAttempts++;
        if (window.botpress && typeof window.botpress.updateUser === 'function') {
            clearInterval(waitForBotpress);
            initBotpressSync();
        } else if (bpAttempts > 30) {
            clearInterval(waitForBotpress);
        }
    }, 300);
</script>
