<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Perpustakaan SDN Pengampon 3</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            font-size: 12px;
            color: #333;
            padding: 20px;
        }
        .header {
            text-align: center;
            border-bottom: 3px double #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header img {
            width: 70px;
            height: 70px;
            object-fit: contain;
        }
        .header h1 { font-size: 18px; margin-top: 5px; }
        .header h2 { font-size: 16px; margin-top: 3px; }
        .header p { font-size: 11px; margin-top: 3px; color: #666; }

        .title {
            text-align: center;
            margin-bottom: 15px;
        }
        .title h3 {
            font-size: 16px;
            text-decoration: underline;
        }
        .title p {
            font-size: 11px;
            color: #666;
            margin-top: 3px;
        }

        .statistik {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .statistik td {
            padding: 8px;
            border: 1px solid #ddd;
            width: 33.33%;
        }
        .statistik .label { font-weight: bold; font-size: 11px; color: #555; }
        .statistik .value { font-size: 18px; font-weight: bold; color: #222; }

        .section-title {
            font-size: 14px;
            font-weight: bold;
            margin-top: 20px;
            margin-bottom: 10px;
            padding: 5px 0;
            border-bottom: 2px solid #333;
        }

        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .data-table th,
        .data-table td {
            border: 1px solid #999;
            padding: 6px 8px;
            font-size: 11px;
            text-align: left;
        }
        .data-table th {
            background-color: #f0f0f0;
            font-weight: bold;
        }
        .data-table tr:nth-child(even) {
            background-color: #fafafa;
        }

        .empty {
            text-align: center;
            color: #888;
            font-style: italic;
            padding: 15px;
        }

        .footer {
            position: fixed;
            bottom: 20px;
            left: 0;
            right: 0;
            text-align: center;
            font-size: 10px;
            color: #666;
            border-top: 1px solid #ddd;
            padding-top: 5px;
        }

        /* ==== TANDA TANGAN ==== */
        .ttd {
            margin-top: 40px;
            width: 100%;
        }
        .ttd-container {
            float: right;
            text-align: left;
            width: 250px;
        }
        .ttd p {
            margin-bottom: 3px;
            line-height: 1.4;
        }
        .ttd .nama {
            margin-top: 60px;
            font-weight: bold;
            text-decoration: underline;
        }
        .clearfix::after {
            content: "";
            display: table;
            clear: both;
        }
    </style>
</head>
<body>

    {{-- HEADER --}}
    <div class="header">
        <img src="{{ public_path('images/logo-sdn.png') }}" alt="Logo">
        <h1>PERPUSTAKAAN DIGITAL</h1>
        <h2>SD NEGERI PENGAMPON 3</h2>
        <p>Kota Cirebon - Bersenergi</p>
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
                <div class="label">Total Buku</div>
                <div class="value">{{ $totalBuku }}</div>
            </td>
            <td>
                <div class="label">Total Anggota</div>
                <div class="value">{{ $totalAnggota }}</div>
            </td>
            <td>
                <div class="label">Total Kategori</div>
                <div class="value">{{ $totalKategori }}</div>
            </td>
        </tr>
        <tr>
            <td>
                <div class="label">Sedang Dipinjam</div>
                <div class="value">{{ $sedangDipinjam }}</div>
            </td>
            <td>
                <div class="label">Total Peminjaman</div>
                <div class="value">{{ $totalPeminjaman }}</div>
            </td>
            <td>
                <div class="label">Total Denda (Lunas)</div>
                <div class="value">Rp {{ number_format($totalDenda, 0, ',', '.') }}</div>
            </td>
        </tr>
    </table>

    {{-- BUKU TERPOPULER --}}
    <div class="section-title">BUKU TERPOPULER</div>
    @if($bukuTerpopuler->isEmpty())
        <div class="empty">Belum ada data peminjaman.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Judul Buku</th>
                    <th>Penulis</th>
                    <th style="width: 100px; text-align: center;">Dipinjam</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bukuTerpopuler as $index => $item)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $item->book->title ?? 'Buku tidak ditemukan' }}</td>
                        <td>{{ $item->book->author ?? '-' }}</td>
                        <td style="text-align: center;">{{ $item->total }} kali</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- BUKU SEDANG DIPINJAM --}}
    <div class="section-title">BUKU SEDANG DIPINJAM</div>
    @if($bukuDipinjam->isEmpty())
        <div class="empty">Tidak ada buku yang sedang dipinjam.</div>
    @else
        <table class="data-table">
            <thead>
                <tr>
                    <th style="width: 30px;">No</th>
                    <th>Peminjam</th>
                    <th>Buku</th>
                    <th>Tgl Pinjam</th>
                    <th>Jatuh Tempo</th>
                </tr>
            </thead>
            <tbody>
                @foreach($bukuDipinjam as $index => $loan)
                    <tr>
                        <td style="text-align: center;">{{ $index + 1 }}</td>
                        <td>{{ $loan->user->name }}</td>
                        <td>{{ $loan->book->title }}</td>
                        <td>{{ $loan->loan_date }}</td>
                        <td>{{ $loan->due_date }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    @endif

    {{-- TANDA TANGAN (Rata kanan, semua sejajar) --}}
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
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </div>

</body>
</html>