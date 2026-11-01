<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: white !important; }
            .print-card { box-shadow: none !important; }
        }
        /* Paksa SVG isi penuh container */
        #qr-container svg {
            width: 100% !important;
            height: 100% !important;
            display: block;
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center print-card">

        <h1 class="text-xl font-bold text-gray-800 mb-1">QR Code Anggota</h1>
        <p class="text-sm text-gray-500 mb-6">Screenshot atau cetak QR ini untuk kartu anggota.</p>

        {{-- QR Code Container --}}
        <div class="flex justify-center mb-4">
            <div class="border-4 border-green-600 rounded-lg p-2 bg-white" style="width:288px; height:288px;">
                @if(!empty($qrSvg))
                    {{-- SVG dari server (paling andal, tidak butuh JS/CDN/internet) --}}
                    <div id="qr-container" style="width:272px; height:272px;">
                        {!! $qrSvg !!}
                    </div>
                @else
                    {{-- Fallback: tampilkan pesan error --}}
                    <div class="w-full h-full flex flex-col items-center justify-center text-red-500 text-sm gap-2">
                        <span class="text-3xl">⚠️</span>
                        <p>Gagal membuat QR.</p>
                        <p class="text-xs text-gray-500">Pastikan NISN/NIP sudah terisi.</p>
                    </div>
                @endif
            </div>
        </div>

        {{-- Info Anggota --}}
        <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-xs text-gray-500">Nama:</p>
            <p class="font-bold text-lg mb-2">{{ $user->name }}</p>

            <p class="text-xs text-gray-500">
                @if($user->role === 'siswa') NISN @else NIP @endif:
            </p>
            <p class="font-mono font-bold mb-2">{{ $qrContent }}</p>

            @if($user->role === 'siswa' && $user->class)
                <p class="text-xs text-gray-500 mt-2">Kelas:</p>
                <p class="font-bold">{{ $user->class }}</p>
            @endif

            <p class="text-xs text-gray-500 mt-2">Role:</p>
            <p class="font-bold uppercase">
                @if($user->role === 'siswa') 🎓 SISWA @else 👨‍🏫 GURU @endif
            </p>
        </div>

        {{-- Tombol Aksi --}}
        <div class="flex gap-2 no-print">
            <a href="{{ route('petugas.kelolaAnggota') }}"
               class="flex-1 bg-gray-400 hover:bg-gray-500 text-white px-4 py-3 rounded-lg font-semibold transition">
                ← Kembali
            </a>
            <button onclick="window.print()"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold transition">
                🖨️ Print
            </button>
        </div>

        <p class="text-xs text-gray-400 mt-4 no-print">
            💡 Tekan <strong>Ctrl + Shift + S</strong> untuk screenshot QR.
        </p>

    </div>

</body>
</html>
