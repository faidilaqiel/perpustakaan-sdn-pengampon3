<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Scan QR - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- HEADER --}}
    <header class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" class="w-12 h-12 object-contain bg-white rounded-full p-1">
                <div>
                    <h1 class="text-xl font-bold">Perpustakaan Digital</h1>
                    <p class="text-sm">SDN Pengampon 3</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-sm">Role: {{ Auth::user()->role }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- KONTEN --}}
    <div class="container mx-auto p-6 max-w-3xl">

        <a href="{{ route('petugas.dashboard') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Dashboard
        </a>

        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-8 text-center">
            <div class="text-6xl mb-4">📱</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Scan QR Code Anggota</h2>
            <p class="text-gray-600 mb-6">
                Scan QR Code pada kartu anggota, ATAU ketik NISN/NIP secara manual.
            </p>

            <form method="POST" action="{{ route('petugas.scan.hasil') }}" class="max-w-lg mx-auto">
                @csrf

                <div class="mb-4">
                    <input type="text" name="kode" id="kode-input"
                           class="w-full px-6 py-4 border-2 border-green-500 rounded-lg text-center text-xl font-mono focus:outline-none focus:ring-4 focus:ring-green-300"
                           placeholder="Scan atau ketik kode..."
                           autofocus required>
                    <p class="text-xs text-gray-500 mt-2">
                        💡 Kalau pakai scanner QR: cursor harus di kolom ini.
                    </p>
                </div>

                <button type="submit"
                        class="w-full bg-green-600 hover:bg-green-700 text-white py-4 rounded-lg font-bold text-lg transition">
                    🔍 CARI ANGGOTA
                </button>
            </form>

            {{-- Info --}}
            <div class="mt-8 bg-blue-50 border border-blue-200 rounded-lg p-4 text-left">
                <p class="font-bold text-blue-800 mb-2">ℹ️ Cara Pakai:</p>
                <ol class="list-decimal list-inside text-sm text-blue-700 space-y-1">
                    <li><strong>Manual:</strong> Ketik NISN/NIP siswa di kolom, lalu klik "Cari Anggota".</li>
                    <li><strong>Scanner QR:</strong> Colok scanner USB, klik kolom input, lalu scan QR pada kartu.</li>
                    <li><strong>Hasil:</strong> Aplikasi akan menampilkan data siswa + peminjaman aktifnya.</li>
                </ol>
            </div>

        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

    {{-- Script: Auto-focus input --}}
    <script>
        document.getElementById('kode-input').focus();
    </script>

</body>
</html>