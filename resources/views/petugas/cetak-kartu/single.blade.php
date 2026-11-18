<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Anggota Perpustakaan - {{ $user->name }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html2canvas untuk unduh PNG resolusi tinggi -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@700;800;900&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        /* Ukuran Kartu: 602px x 1024px sesuai proporsi template */
        #kartu-capture {
            width: 602px;
            height: 1024px;
            position: relative;
            background-image: url('{{ asset("images/template-kartu-siswa.jpg") }}');
            background-size: cover;
            background-repeat: no-repeat;
            background-position: center;
            border-radius: 28px;
            overflow: hidden;
            box-shadow: 0 20px 45px -10px rgba(0, 0, 0, 0.25);
        }

        /* Kotak Putih Utama (QR Code Penuh Bersih) */
        .box-konten-utama {
            position: absolute;
            top: 345px;
            left: 140px;
            width: 322px;
            height: 290px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        .box-konten-utama img {
            width: 275px;
            height: 275px;
            object-fit: contain;
            display: block;
        }

        /* Kotak Putih Bawah: Nama Anggota (Persis Seperti Contoh Gambar) */
        .box-nama-anggota {
            position: absolute;
            top: 662px;
            left: 36px;
            width: 530px;
            height: 85px;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 16px;
        }

        .box-nama-anggota h3 {
            font-size: 32px;
            font-weight: 900;
            color: #0f172a;
            letter-spacing: 0.5px;
            line-height: 1.15;
            text-transform: uppercase;
        }

        @media print {
            .no-print { display: none !important; }
            body { background: transparent !important; padding: 0 !important; }
            #kartu-capture {
                box-shadow: none !important;
                margin: 0 auto;
                page-break-inside: avoid;
            }
        }
    </style>
</head>
<body class="min-h-screen flex flex-col items-center justify-center p-4 sm:p-6">

    <!-- Top Action Toolbar -->
    <div class="no-print mb-6 flex flex-wrap items-center justify-center gap-3 bg-white p-3.5 rounded-2xl shadow-sm border border-gray-200">
        <a href="{{ route('petugas.cetakKartu') }}" class="px-4 py-2 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-bold rounded-xl transition flex items-center gap-1.5">
            <i class="fas fa-arrow-left"></i> Kembali ke Dashboard
        </a>
        <button id="btn-download-png" onclick="downloadSebagaiPNG()" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2 transform active:scale-95">
            <i class="fas fa-file-image text-sm"></i> Download Kartu (PNG)
        </button>
        <button onclick="window.print()" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2 transform active:scale-95">
            <i class="fas fa-print text-sm"></i> Cetak / Print PDF
        </button>
    </div>

    <!-- Area Kartu Anggota (602px x 1024px) -->
    <div id="kartu-wrapper" class="relative">
        <div id="kartu-capture">
            
            <!-- Area Kotak Tengah: QR Code Saja (Besar & Jelas seperti contoh) -->
            <div class="box-konten-utama">
                <img src="{{ $qrCodeBase64 }}" alt="QR Code Anggota">
            </div>

            <!-- Area Kotak Bawah: NAMA ANGGOTA (Sesuai Contoh Gambar: AISYAH PUTRI TIAN) -->
            <div class="box-nama-anggota">
                <h3 id="nama-siswa-display">
                    {{ strtoupper($user->name) }}
                </h3>
            </div>

        </div>
    </div>

    <p class="no-print text-xs text-gray-400 mt-4 text-center">
        💡 <em>Desain disesuaikan bersih: Menampilkan QR Code penuh dan Nama Anggota tanpa pencantuman NISN maupun Kelas.</em>
    </p>

    <!-- Script Download PNG Menggunakan html2canvas -->
    <script>
        // Sesuaikan ukuran font otomatis jika nama siswa sangat panjang agar tidak terpotong
        window.addEventListener('DOMContentLoaded', () => {
            const nameEl = document.getElementById('nama-siswa-display');
            if (nameEl) {
                const len = nameEl.innerText.length;
                if (len > 25) {
                    nameEl.style.fontSize = '24px';
                } else if (len > 18) {
                    nameEl.style.fontSize = '28px';
                }
            }
        });

        function downloadSebagaiPNG() {
            const btn = document.getElementById('btn-download-png');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses PNG...';
            btn.disabled = true;

            const element = document.getElementById('kartu-capture');

            html2canvas(element, {
                scale: 2, // Resolusi tinggi HD jernih
                useCORS: true,
                allowTaint: true,
                backgroundColor: null
            }).then(canvas => {
                const link = document.createElement('a');
                const cleanName = "{{ str_replace([' ', '/', '\\'], '_', strtoupper($user->name)) }}";
                link.download = `Kartu_Anggota_${cleanName}.png`;
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();

                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                alert('Terjadi kesalahan saat mengunduh PNG: ' + err.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>

</body>
</html>
