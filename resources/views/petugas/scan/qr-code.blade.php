<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>QR Code - {{ $user->name }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-6">

    <div class="bg-white rounded-2xl shadow-2xl p-8 max-w-md w-full text-center">

        {{-- Header --}}
        <h1 class="text-xl font-bold text-gray-800 mb-1">QR Code Anggota</h1>
        <p class="text-sm text-gray-500 mb-6">Screenshot QR ini untuk ditempel ke kartu.</p>

        {{-- QR Code --}}
        <div class="bg-white p-4 rounded-lg border-4 border-green-500 inline-block mb-4">
            <img src="{{ $qrCodeBase64 }}" alt="QR Code" class="w-64 h-64">
        </div>

        {{-- Info Siswa --}}
        <div class="text-left bg-gray-50 rounded-lg p-4 mb-6">
            <p class="text-xs text-gray-500">Nama:</p>
            <p class="font-bold text-lg mb-2">{{ $user->name }}</p>

            <p class="text-xs text-gray-500">
                @if($user->role === 'siswa') NISN @else NIP @endif:
            </p>
            <p class="font-mono font-bold">
                @if($user->role === 'siswa') {{ $user->nisn }} @else {{ $user->nip }} @endif
            </p>

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
            💡 Tekan <strong>Ctrl + Shift + S</strong> (atau screenshot) untuk menyimpan gambar QR.
        </p>

    </div>

</body>
</html>