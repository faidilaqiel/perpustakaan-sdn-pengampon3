<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Kartu Anggota - {{ $user->name }}</title>
    <style>
        @page {
            margin: 0;
            padding: 0;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body {
            font-family: Arial, sans-serif;
            font-size: 8pt;
            color: #333;
            width: 85.6mm;
            height: 54mm;
        }

        /* ===== KARTU UTAMA ===== */
        .kartu {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            border: 1mm solid #000;
            border-radius: 2mm;
        }

        /* ===== BACKGROUND HIJAU ===== */
        .bg-hijau {
            position: absolute;
            top: 0;
            left: 0;
            width: 85.6mm;
            height: 54mm;
            background-color: #d1fae5;
            z-index: 0;
        }

        /* Aksen hijau tua (lengkungan bawah) */
        .bg-aksen-hijau {
            position: absolute;
            bottom: -10mm;
            left: -5mm;
            width: 100mm;
            height: 25mm;
            background-color: #10b981;
            border-radius: 50%;
            z-index: 0;
            opacity: 0.7;
        }
        .bg-aksen-hijau-2 {
            position: absolute;
            bottom: -15mm;
            left: -5mm;
            width: 100mm;
            height: 25mm;
            background-color: #059669;
            border-radius: 50%;
            z-index: 0;
            opacity: 0.5;
        }

        /* Lengkungan putih di kanan atas */
        .bg-putih-kanan {
            position: absolute;
            top: -20mm;
            right: -25mm;
            width: 80mm;
            height: 80mm;
            background-color: #ffffff;
            border-radius: 50%;
            z-index: 0;
            opacity: 0.6;
        }

        /* ===== HEADER ===== */
        .header {
            position: absolute;
            top: 1mm;
            left: 0;
            right: 0;
            height: 16mm;
            padding: 0 2mm;
            z-index: 1;
        }
        .header-table {
            width: 100%;
            border-collapse: collapse;
            height: 16mm;
        }
        .header-table td {
            vertical-align: middle;
        }
        .logo-kiri {
            width: 16mm;
            text-align: left;
        }
        .logo-kiri img {
            width: 14mm;
            height: 14mm;
        }
        .logo-kanan {
            width: 16mm;
            text-align: right;
        }
        .logo-kanan img {
            width: 14mm;
            height: 14mm;
        }
        .header-text {
            text-align: center;
            padding: 0 1mm;
        }
        .header-text h1 {
            font-size: 8pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.2pt;
            line-height: 1.1;
        }
        .header-text h2 {
            font-size: 11pt;
            font-weight: bold;
            color: #000;
            letter-spacing: 0.5pt;
            line-height: 1.1;
        }
        .header-text .sub {
            font-size: 5.5pt;
            color: #000;
            line-height: 1.2;
            margin-top: 0.3mm;
        }

        /* Garis header */
        .garis-header {
            position: absolute;
            top: 17mm;
            left: 3mm;
            right: 3mm;
            height: 0.7mm;
            background-color: #000;
            z-index: 1;
        }
        .garis-header-2 {
            position: absolute;
            top: 18mm;
            left: 3mm;
            right: 3mm;
            height: 0.3mm;
            background-color: #000;
            z-index: 1;
        }

        /* ===== FOTO ===== */
        .foto-wrapper {
            position: absolute;
            top: 20mm;
            left: 4mm;
            z-index: 2;
        }
        .foto-box {
            width: 19mm;
            height: 26mm;
            background-color: #dc2626;
            text-align: center;
            line-height: 26mm;
            font-size: 22pt;
            font-weight: bold;
            color: #ffffff;
            overflow: hidden;
        }
        .foto-box img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        /* ===== DATA ===== */
        .data-wrapper {
            position: absolute;
            top: 20mm;
            left: 25mm;
            right: 22mm;
            z-index: 2;
        }
        .data-table {
            width: 100%;
            font-size: 7pt;
            border-collapse: collapse;
            line-height: 1.9;
        }
        .data-table td {
            padding: 0;
            vertical-align: top;
        }
        .data-table .label {
            width: 20mm;
            color: #000;
            font-weight: bold;
            font-size: 7pt;
        }
        .data-table .sep {
            width: 3mm;
            color: #000;
            font-weight: bold;
        }
        .data-table .value {
            color: #000;
            font-weight: bold;
            font-size: 7pt;
        }

        /* ===== QR CODE (kanan atas) ===== */
        .qr-wrapper {
            position: absolute;
            top: 20mm;
            right: 3mm;
            z-index: 2;
            text-align: center;
        }
        .qr-label-top {
            font-size: 5pt;
            color: #000;
            font-weight: bold;
            margin-bottom: 0.3mm;
        }
        .qr-box {
            width: 17mm;
            height: 17mm;
            background-color: #ffffff;
            border: 0.5mm solid #000;
            padding: 0.5mm;
        }
        .qr-box img {
            width: 100%;
            height: 100%;
            display: block;
        }

        /* ===== TANDA TANGAN & STEMPEL (kanan bawah) ===== */
        .ttd-wrapper {
            position: absolute;
            bottom: 1mm;
            right: 3mm;
            width: 45mm;
            z-index: 2;
            text-align: right;
        }
        .ttd-date {
            font-size: 5pt;
            color: #000;
            line-height: 1.2;
            text-align: right;
        }
        .ttd-jabatan {
            font-size: 5.5pt;
            color: #000;
            line-height: 1.2;
            text-align: right;
        }
        .ttd-stempel-wrapper {
            position: relative;
            width: 45mm;
            height: 12mm;
            margin-top: 0.5mm;
        }
        /* Stempel (di belakang) */
        .stempel-box {
            position: absolute;
            top: -3mm;
            left: 0;
            width: 18mm;
            height: 18mm;
            z-index: 1;
            opacity: 0.85;
        }
        .stempel-box img {
            width: 100%;
            height: 100%;
        }
        /* Tanda tangan (di depan) */
        .ttd-img {
            position: absolute;
            top: 0;
            right: 2mm;
            height: 11mm;
            z-index: 2;
        }
        .ttd-img img {
            height: 11mm;
        }
        .ttd-nama {
            font-size: 5.5pt;
            font-weight: bold;
            color: #000;
            text-align: right;
            line-height: 1.2;
            margin-top: 0.3mm;
        }
        .ttd-nip {
            font-size: 5pt;
            color: #000;
            text-align: right;
            line-height: 1.2;
        }
    </style>
</head>
<body>

    <div class="kartu">

        {{-- Background --}}
        <div class="bg-hijau"></div>
        <div class="bg-putih-kanan"></div>
        <div class="bg-aksen-hijau"></div>
        <div class="bg-aksen-hijau-2"></div>

        {{-- HEADER --}}
        <div class="header">
            <table class="header-table">
                <tr>
                    <td class="logo-kiri">
                        @if(file_exists(public_path('images/logo-tut-wuri.png')))
                            <img src="{{ public_path('images/logo-tut-wuri.png') }}" alt="Tut Wuri Handayani">
                        @endif
                    </td>
                    <td class="header-text">
                        <h1>KARTU ANGGOTA PERPUSTAKAAN</h1>
                        <h2>SDN PENGAMPON 3</h2>
                        <div class="sub">Jl. Pengampon No.33</div>
                        <div class="sub">Telp. (0231) 232048</div>
                    </td>
                    <td class="logo-kanan">
                        @if(file_exists(public_path('images/logo-sdn.png')))
                            <img src="{{ public_path('images/logo-sdn.png') }}" alt="Logo SDN">
                        @endif
                    </td>
                </tr>
            </table>
        </div>

        {{-- Garis Header --}}
        <div class="garis-header"></div>
        <div class="garis-header-2"></div>

        {{-- FOTO --}}
        <div class="foto-wrapper">
            <div class="foto-box">
                @if($user->photo && file_exists(public_path('photos/' . $user->photo)))
                    <img src="{{ public_path('photos/' . $user->photo) }}" alt="Foto">
                @else
                    {{ strtoupper(substr($user->name, 0, 1)) }}
                @endif
            </div>
        </div>

        {{-- DATA --}}
        <div class="data-wrapper">
            <table class="data-table">
                <tr>
                    <td class="label">No. Anggota</td>
                    <td class="sep">:</td>
                    <td class="value">{{ $user->nisn ?? $user->nip }}</td>
                </tr>
                <tr>
                    <td class="label">Nama</td>
                    <td class="sep">:</td>
                    <td class="value">{{ strtoupper($user->name) }}</td>
                </tr>
                <tr>
                    <td class="label">No. Induk</td>
                    <td class="sep">:</td>
                    <td class="value">{{ $user->id }}</td>
                </tr>
            </table>
        </div>

        {{-- QR CODE --}}
        <div class="qr-wrapper">
            <div class="qr-label-top">KODE QR</div>
            <div class="qr-box">
                <img src="{{ $qrCodeBase64 }}" alt="QR Code">
            </div>
        </div>

        {{-- TANDA TANGAN + STEMPEL --}}
        <div class="ttd-wrapper">
            <div class="ttd-date">Cirebon, {{ \Carbon\Carbon::now()->translatedFormat('d F Y') }}</div>
            <div class="ttd-jabatan">Kepala Sekolah</div>
            <div class="ttd-stempel-wrapper">
                @if(file_exists(public_path('images/stempel-sekolah.png')))
                    <div class="stempel-box">
                        <img src="{{ public_path('images/stempel-sekolah.png') }}" alt="Stempel">
                    </div>
                @endif
                
                @if(file_exists(public_path('images/ttd-kepsek.png')))
                    <div class="ttd-img">
                        <img src="{{ public_path('images/ttd-kepsek.png') }}" alt="Tanda Tangan">
                    </div>
                @endif
            </div>
            <div class="ttd-nama">H. NONI RISANTO, M.Pd.I</div>
            <div class="ttd-nip">NIP. 19750814 200801 1 008</div>
        </div>

    </div>

</body>
</html>