<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perpustakaan SDN Pengampon 3</title>
    <style>
        @page {
            margin: 30px 40px;
        }
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }

        /* HEADER KOP SURAT */
        .header {
            text-align: center;
            border-bottom: 3px solid #15803d; /* Hijau Tua */
            padding-bottom: 15px;
            margin-bottom: 20px;
            position: relative;
        }
        .header img {
            position: absolute;
            left: 0;
            top: 0;
            width: 75px;
            height: 75px;
            object-fit: contain;
        }
        .header-text {
            margin-left: 80px;
        }
        .header h1 {
            font-size: 18px;
            font-weight: bold;
            color: #15803d;
            margin: 0;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        .header h2 {
            font-size: 15px;
            font-weight: bold;
            color: #333;
            margin: 5px 0 2px 0;
        }
        .header p {
            font-size: 11px;
            color: #666;
            margin: 0;
        }

        /* JUDUL LAPORAN */
        .title {
            text-align: center;
            margin-bottom: 25px;
        }
        .title h3 {
            font-size: 16px;
            font-weight: bold;
            text-decoration: underline;
            margin: 0 0 5px 0;
        }
        .title p {
            font-size: 11px;
            color: #666;
            margin: 0;
        }

        /* TABEL STATISTIK */
        .statistik {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 25px;
        }
        .statistik td {
            padding: 12px;
            border: 1px solid #e5e7eb;
            width: 33.33%;
            text-align: center;
            background-color: #f9fafb;
        }
        .statistik .label {
            font-size: 10px;
            color: #6b7280;
            text-transform: uppercase;
            font-weight: bold;
            margin-bottom: 5px;
            display: block;
        }
        .statistik .value {
            font-size: 20px;
            font-weight: bold;
            color: #15803d;
        }

        /* JUDUL SECTION */
        .section-title {
            font-size: 14px;
            font-weight: bold;
            color: #15803d;
            margin-top: 25px;
            margin-bottom: 10px;
            padding-bottom: 5px;
            border-bottom: 2px solid #15803d;
        }

        /* TABEL DATA */
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .data-table th {
            background-color: #15803d;
            color: #ffffff;
            font-weight: bold;
            font-size: 11px;
            padding: 8px 10px;
            text-align: left;
            border: 1px solid #15803d;
        }
        .data-table td {
            padding: 8px 10px;
            font-size: 11px;
            border: 1px solid #e5e7eb;
            color: #333;
        }
        .data-table tr:nth-child(even) {
            background-color: #f9fafb;
        }
        .data-table .center {
            text-align: center;
        }
        .data-table .empty {
            text-align: center;
            padding: 20px;
            font-style: italic;
            color: #9ca3af;
        }

        /* TANDA TANGAN */
        .ttd {
            margin-top: 50px;
            width: 100%;
        }
        .ttd-container {
            float: right;
            text-align: center;
            width: 250px;
        }
        .ttd p {
            margin-bottom: 5px;
            font-size: 12px;
            line-height: 1.4;
        }
        .ttd .nama {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
            font-size: 13px;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }

        /* FOOTER */
        .footer {
            position: fixed;
            bottom: -15px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 9px;
            color: #9ca3af;
        }
    </style>
</head>
<body>

    {{-- KOP SURAT --}}
    <div class="header">
        @if(file_exists(public_path('images/logo-sdn.png')))
            <img src="{{ public_path('images/logo-sdn.png') }}" alt="Logo">
        @endif
        <div class="header-text">
            <h1>Perpustakaan Digital</h1>
            <h2>SD NEGERI PENGAMPON 3</h2>
            <p>Jl. Pengampon No.33, Kota Cirebon - Telp. (0231) 232048</p>
        </div>
    </div>

    {{-- JUDUL --}}
    <div class="title">
        <h3>LAPORAN PERPUSTAKAAN</h3>
        <p>Periode: 
            @if($periode === 'bulan_ini') Bulan Ini
            @elseif($periode === 'bulan_lalu') Bulan Lalu
            @else Semua Waktu
            @endif
        </p>
        <p>Dicetak pada: {{ $tanggalCetak }}</p>
    </div>

    {{-- STATISTIK --}}
    <table class="statistik">
        <tr>
            <td>
                <span class="label">Total Buku</span>
                <span class="value">{{ $totalBuku }}</span>
            </td>
            <td>
                <span class="label">Total Anggota</span>
                <span class="value">{{ $totalAnggota }}</span>
            </td>
            <td>
                <span class="label">Total Kategori</span>
                <span class="value">{{ $totalKategori }}</span>
            </td>
        </tr>
        <tr>
            <td>
                <span class="label">Sedang Dipinjam</span>
                <span class="value">{{ $sedangDipinjam }}</span>
            </td>
            <td>
                <span class="label">Total Peminjaman</span>
                <span class="value">{{ $totalPeminjaman }}</span>
            </td>
            <td>
                <span class="label">Total Denda (Lunas)</span>
                <span class="value" style="font-size: 16px;">Rp {{ number_format($totalDenda, 0, ',', '.') }}</span>
            </td>
        </tr>
    </table>

    {{-- BUKU TERPOPULER --}}
    <div class="section-title">BUKU TERPOPULER</div>
    @if($bukuTerpopuler->isEmpty())
        <table class="data-table">
            <tr><td class="empty">Belum ada data peminjaman.</td></tr>
        </table>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th class="center" style="width: 40px;">No</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th class="center" style="width: 120px;">Total Dipinjam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bukuTerpopuler as $index => $item)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $item->book->title ?? 'Buku tidak ditemukan' }}</td>
                        <td>{{ $item->book->author ?? '-' }}</td>
                        <td class="center"><strong>{{ $item->total }} kali</strong></td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- BUKU SEDANG DIPINJAM --}}
    <div class="section-title">DAFTAR BUKU SEDANG DIPINJAM</div>
    @if($bukuDipinjam->isEmpty())
        <table class="data-table">
            <tr><td class="empty">Tidak ada buku yang sedang dipinjam.</td></tr>
        </table>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th class="center" style="width: 40px;">No</th>
                    <th>Nama Peminjam</th>
                    <th>Judul Buku</th>
                    <th class="center">Tgl Pinjam</th>
                    <th class="center">Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bukuDipinjam as $index => $loan)
                    <tr>
                        <td class="center">{{ $index + 1 }}</td>
                        <td>{{ $loan->user->name }}</td>
                        <td>{{ $loan->book->title }}</td>
                        <td class="center">{{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}</td>
                        <td class="center">
                            {{ \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d M Y') }}
                            @if(\Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($loan->due_date)))
                                <br><span style="color: red; font-size: 9px; font-weight: bold;">(Terlambat)</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- TANDA TANGAN --}}
    <div class="ttd clearfix">
        <div class="ttd-container">
            <p>Cirebon, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</p>
            <p>Kepala Sekolah,</p>
            <p class="nama">H. NONI RISANTO, M.Pd.I</p>
            <p>NIP. 19750814 200801 1 008</p>
        </div>
    </div>

    {{-- FOOTER --}}
    <div class="footer">
        Dokumen ini dicetak otomatis oleh Sistem Perpustakaan Digital SDN Pengampon 3
    </div>

</body>
</html>