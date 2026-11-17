<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kartu Anggota Siswa - {{ $user->name }}</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- html2canvas untuk unduh PNG resolusi tinggi -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@600;700;800;900&display=swap');
        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
        }

        /* Ukuran Asli Kartu: 602px x 1024px (Potret Cantik Sesuai Template) */
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

        /* Kotak Putih Utama (QR Code + Detail Kelas) */
        .box-konten-utama {
            position: absolute;
            top: 350px;
            left: 140px;
            width: 322px;
            height: 280px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            border-radius: 24px;
        }

        .box-konten-utama img {
            width: 175px;
            height: 175px;
            object-fit: contain;
        }

        /* Kolom Putih Bawah: Nama Anggota */
        .box-nama-anggota {
            position: absolute;
            top: 665px;
            left: 28px;
            width: 546px;
            height: 80px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 0 24px;
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
            
            <!-- Area Tengah: QR Code & NISN Siswa -->
            <div class="box-konten-utama">
                <div class="bg-white p-2 rounded-2xl shadow-xs border border-blue-100">
                    <img src="{{ $qrCodeBase64 }}" alt="QR Code NISN">
                </div>
                <div class="mt-2 text-center">
                    <span class="text-xs font-extrabold text-blue-900 tracking-wider">
                        NISN: {{ $user->nisn ?? '-' }}
                    </span>
                    <span class="text-[11px] font-bold text-blue-600 block mt-0.5">
                        KELAS: {{ strtoupper($user->class ?? '-') }}
                    </span>
                </div>
            </div>

            <!-- Area Bawah: Nama Anggota Siswa -->
            <div class="box-nama-anggota">
                <h3 class="text-xl font-black text-blue-950 uppercase tracking-wide truncate max-w-full leading-tight">
                    {{ $user->name }}
                </h3>
                <span class="text-xs font-bold text-blue-600 tracking-wider mt-0.5">
                    ANGGOTA RESMI PERPUSTAKAAN
                </span>
            </div>

        </div>
    </div>

    <p class="no-print text-xs text-gray-400 mt-4 text-center">
        💡 <em>Kartu dibuat beresolusi tinggi 602 × 1024 px. Cocok untuk dibagikan ke siswa atau dicetak dalam ukuran saku.</em>
    </p>

    <!-- Script Download PNG Menggunakan html2canvas -->
    <script>
        function downloadSebagaiPNG() {
            const btn = document.getElementById('btn-download-png');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Memproses PNG...';
            btn.disabled = true;

            const element = document.getElementById('kartu-capture');

            // Pengaturan resolusi tinggi scale: 2 atau 3
            html2canvas(element, {
                scale: 2,
                useCORS: true,
                allowTaint: true,
                backgroundColor: null
            }).then(canvas => {
                const link = document.createElement('a');
                // Format nama file: Kartu_Siswa_Nama_NISN.png
                const cleanName = "{{ str_replace(' ', '_', $user->name) }}";
                const cleanNisn = "{{ $user->nisn ?? 'Siswa' }}";
                link.download = `Kartu_PustakaAI_${cleanName}_${cleanNisn}.png`;
                link.href = canvas.toDataURL('image/png', 1.0);
                link.click();

                btn.innerHTML = originalText;
                btn.disabled = false;
            }).catch(err => {
                alert('Terjadi kesalahan saat memproses gambar: ' + err.message);
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        }
    </script>

</body>
</html>
