<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    {{-- QR Code Generator Library (JavaScript, client-side) --}}
    <script src="https://cdn.jsdelivr.net/npm/qrcode@1.5.3/build/qrcode.min.js"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">

        <h1 class="text-xl font-bold text-gray-800 mb-1">QR Code Anggota</h1>
        <p class="text-sm text-gray-500 mb-6">Screenshot QR ini untuk ditempel ke kartu.</p>

        {{-- QR Code Container (Canvas) --}}
        <div class="bg-white p-4 rounded-lg border-4 border-green-500 inline-block mb-4">
            <canvas id="qrcode" class="w-64 h-64"></canvas>
        </div>

        {{-- Info Siswa --}}
        <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-xs text-gray-500">Nama:</p>
            <p class="font-bold text-lg mb-2">{{ $user->name }}</p>

            <p class="text-xs text-gray-500">
                @if($user->role === 'siswa') NISN @else NIP @endif:
            </p>
            <p class="font-mono font-bold">{{ $qrContent }}</p>

            @if($user->role === 'siswa')
                <p class="text-xs text-gray-500 mt-2">Kelas:</p>
                <p class="font-bold">{{ $user->class }}</p>
            @endif

            <p class="text-xs text-gray-500 mt-2">Role:</p>
            <p class="font-bold uppercase">
                @if($user->role === 'siswa') 🎓 Siswa @else 👨‍🏫 Guru @endif
            </p>
        </div>

        {{-- Tombol --}}
        <div class="flex gap-2">
            <a href="{{ route('petugas.scan') }}"
               class="flex-1 bg-gray-400 hover:bg-gray-500 text-white px-4 py-3 rounded-lg font-semibold">
                ← Kembali
            </a>
            <button onclick="window.print()"
                    class="flex-1 bg-green-600 hover:bg-green-700 text-white px-4 py-3 rounded-lg font-semibold">
                🖨️ Print
            </button>
        </div>

        <p class="text-xs text-gray-400 mt-4">
            💡 Tekan <strong>Ctrl + Shift + S</strong> untuk screenshot QR.
        </p>

    </div>

    {{-- Script Generate QR (Client-Side) --}}
    <script>
        const qrContent = @json($qrContent);
        const canvas = document.getElementById('qrcode');

        QRCode.toCanvas(canvas, qrContent, {
            width: 256,
            margin: 1,
            errorCorrectionLevel: 'H',
            color: {
                dark: '#065f46',
                light: '#ffffff'
            }
        }, function(error) {
            if (error) {
                console.error(error);
                alert('Gagal generate QR Code: ' + error);
            }
        });
    </script>

</body>
</html>