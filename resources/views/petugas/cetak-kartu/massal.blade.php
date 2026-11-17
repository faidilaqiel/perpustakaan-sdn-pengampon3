<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Cetak Kartu Anggota Perpustakaan - SDN Pengampon 3</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 10mm;
        }
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: Arial, sans-serif;
            background-color: #f1f5f9;
            color: #333;
            padding: 10px;
        }

        .no-print-bar {
            position: fixed;
            top: 15px;
            right: 15px;
            z-index: 9999;
            background: white;
            padding: 10px 16px;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
            display: flex;
            gap: 10px;
        }

        .btn-print {
            background: #059669;
            color: white;
            border: none;
            padding: 8px 16px;
            font-size: 13px;
            font-weight: bold;
            border-radius: 6px;
            cursor: pointer;
        }

        /* Grid Lembar Kertas A4 (Isi 8 Kartu per halaman) */
        .a4-sheet {
            width: 190mm;
            margin: 0 auto 15mm auto;
            background: white;
            display: grid;
            grid-template-columns: repeat(2, 85.6mm);
            gap: 8mm 10mm;
            justify-content: center;
            page-break-after: always;
        }

        /* ===== KARTU SATUAN (UKURAN STANDAR ID CARD CR80: 85.6mm x 54mm) ===== */
        .kartu {
            width: 85.6mm;
            height: 54mm;
            position: relative;
            overflow: hidden;
            background-color: #ffffff;
            border: 0.5mm solid #059669;
            border-radius: 2mm;
            box-shadow: 0 1px 4px rgba(0,0,0,0.1);
        }

        .bg-hijau {
            position: absolute;
            top: 0; left: 0;
            width: 85.6mm; height: 54mm;
            background: linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);
            z-index: 0;
        }

        .bg-aksen-hijau {
            position: absolute;
            bottom: -12mm; left: -5mm;
            width: 100mm; height: 26mm;
            background-color: #10b981;
            border-radius: 50%;
            z-index: 0;
            opacity: 0.7;
        }

        .bg-aksen-hijau-2 {
            position: absolute;
            bottom: -16mm; left: -5mm;
            width: 100mm; height: 26mm;
            background-color: #059669;
            border-radius: 50%;
            z-index: 0;
            opacity: 0.5;
        }

        /* HEADER KARTU */
        .header {
            position: absolute;
            top: 1mm; left: 0; right: 0;
            height: 14mm;
            padding: 0 2mm;
            z-index: 1;
        }
        .header-table { width: 100%; border-collapse: collapse; }
        .logo-kiri { width: 14mm; vertical-align: middle; text-align: left; }
        .logo-kiri img { width: 11mm; height: 11mm; object-fit: contain; }
        .teks-header { vertical-align: middle; text-align: center; }
        .teks-header .kab { font-size: 5.5pt; font-weight: bold; color: #065f46; letter-spacing: 0.3px; }
        .teks-header .sekolah { font-size: 7pt; font-weight: 900; color: #064e3b; margin: 0.5px 0; }
        .teks-header .alamat { font-size: 4pt; color: #047857; }
        .garis-header {
            position: absolute;
            top: 13.5mm; left: 2mm; right: 2mm;
            height: 0.6mm;
            background-color: #059669;
            z-index: 1;
        }

        .sub-header {
            position: absolute;
            top: 14.5mm; left: 0; right: 0;
            text-align: center;
            font-size: 6pt;
            font-weight: 900;
            color: #065f46;
            letter-spacing: 0.8px;
            z-index: 1;
        }

        /* FOTO */
        .foto-wrapper {
            position: absolute;
            top: 18.5mm; left: 3mm;
            width: 16mm; height: 21mm;
            z-index: 2;
        }
        .foto-box {
            width: 16mm; height: 21mm;
            border: 0.3mm solid #059669;
            border-radius: 1mm;
            background-color: #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 14pt;
            font-weight: bold;
            color: #059669;
            overflow: hidden;
        }
        .foto-box img { width: 100%; height: 100%; object-fit: cover; }

        /* DATA ANGGOTA */
        .data-wrapper {
            position: absolute;
            top: 18mm; left: 21mm;
            width: 38mm;
            z-index: 2;
        }
        .data-table { width: 100%; border-collapse: collapse; font-size: 5.5pt; }
        .data-table td { padding: 0.6mm 0; vertical-align: top; }
        .data-table .label { width: 14mm; color: #064e3b; font-weight: bold; }
        .data-table .sep { width: 1.5mm; text-align: center; }
        .data-table .value { color: #111; font-weight: bold; }

        /* QR CODE */
        .qr-wrapper {
            position: absolute;
            top: 18mm; right: 3mm;
            width: 18mm;
            text-align: center;
            z-index: 2;
        }
        .qr-label { font-size: 4.5pt; font-weight: bold; color: #065f46; margin-bottom: 0.5mm; }
        .qr-box {
            width: 16mm; height: 16mm;
            margin: 0 auto;
            background: white;
            border: 0.3mm solid #059669;
            border-radius: 1mm;
            padding: 0.5mm;
        }
        .qr-box img { width: 100%; height: 100%; display: block; }

        /* TANDA TANGAN & STEMPEL */
        .ttd-wrapper {
            position: absolute;
            bottom: 2mm; right: 3mm;
            width: 32mm;
            text-align: center;
            font-size: 4.5pt;
            color: #064e3b;
            z-index: 2;
        }
        .ttd-date { font-size: 4pt; }
        .ttd-jabatan { font-weight: bold; margin-bottom: 1mm; font-size: 4.5pt; }
        .ttd-nama { font-weight: 900; text-decoration: underline; font-size: 4.8pt; color: #042f2e; }
        .ttd-nip { font-size: 4pt; }

        @media print {
            body { background: white !important; padding: 0 !important; }
            .no-print-bar { display: none !important; }
            .a4-sheet { margin: 0 !important; box-shadow: none !important; width: 100% !important; }
        }
    </style>
</head>
<body>

    <div class="no-print-bar">
        <button class="btn-print" onclick="window.print()">🖨️ Cetak Lembar Kartu (Print)</button>
    </div>

    @php
        $chunks = array_chunk($cardsData, 8); // 8 kartu per halaman A4 (2 kolom x 4 baris)
    @endphp

    @foreach($chunks as $chunk)
        <div class="a4-sheet">
            @foreach($chunk as $item)
                @php
                    $u = $item['user'];
                    $qr = $item['qrCodeBase64'];
                @endphp
                <div class="kartu">
                    <div class="bg-hijau"></div>
                    <div class="bg-aksen-hijau"></div>
                    <div class="bg-aksen-hijau-2"></div>

                    {{-- HEADER --}}
                    <div class="header">
                        <table class="header-table">
                            <tr>
                                <td class="logo-kiri">
                                    <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo">
                                </td>
                                <td class="teks-header">
                                    <div class="kab">PEMERINTAH KOTA CIREBON</div>
                                    <div class="sekolah">SDN PENGAMPON 3</div>
                                    <div class="alamat">Jl. Pengampon No. 3 Kota Cirebon — Jawa Barat</div>
                                </td>
                            </tr>
                        </table>
                    </div>
                    <div class="garis-header"></div>
                    <div class="sub-header">KARTU ANGGOTA PERPUSTAKAAN</div>

                    {{-- FOTO --}}
                    <div class="foto-wrapper">
                        <div class="foto-box">
                            @if($u->photo && file_exists(public_path('photos/' . $u->photo)))
                                <img src="{{ asset('photos/' . $u->photo) }}" alt="Foto">
                            @else
                                {{ strtoupper(substr($u->name, 0, 1)) }}
                            @endif
                        </div>
                    </div>

                    {{-- BIODATA --}}
                    <div class="data-wrapper">
                        <table class="data-table">
                            <tr>
                                <td class="label">No. Anggota</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $u->nisn ?? $u->nip }}</td>
                            </tr>
                            <tr>
                                <td class="label">Nama</td>
                                <td class="sep">:</td>
                                <td class="value">{{ strtoupper($u->name) }}</td>
                            </tr>
                            <tr>
                                <td class="label">Peran</td>
                                <td class="sep">:</td>
                                <td class="value">{{ $u->role === 'siswa' ? 'Siswa (Kls ' . ($u->class ?? '-') . ')' : 'Guru' }}</td>
                            </tr>
                        </table>
                    </div>

                    {{-- QR CODE --}}
                    <div class="qr-wrapper">
                        <div class="qr-label">SCAN QR</div>
                        <div class="qr-box">
                            <img src="{{ $qr }}" alt="QR Code">
                        </div>
                    </div>

                    {{-- PENGESAHAN --}}
                    <div class="ttd-wrapper">
                        <div class="ttd-date">Cirebon, {{ \Carbon\Carbon::now()->translatedFormat('d M Y') }}</div>
                        <div class="ttd-jabatan">Kepala Sekolah</div>
                        <div class="ttd-nama">H. NONI RISANTO, M.Pd.I</div>
                        <div class="ttd-nip">NIP. 19750814 200801 1 008</div>
                    </div>
                </div>
            @endforeach
        </div>
    @endforeach

</body>
</html>
