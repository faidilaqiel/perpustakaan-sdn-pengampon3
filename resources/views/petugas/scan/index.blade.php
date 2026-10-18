<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Scan QR Code - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input[type="text"] {
            font-size: 16px !important; /* Mencegah iOS auto-zoom */
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen w-full overflow-x-hidden">

    <header class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center gap-3 text-center md:text-left">
            <div class="flex flex-col md:flex-row items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-full p-1">
                <div>
                    <h1 class="text-xl font-bold">Perpustakaan Digital</h1>
                    <p class="text-sm">SDN Pengampon 3</p>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <p class="font-semibold">{{ Auth::user()->name ?? 'Administrator' }}</p>
                <p class="text-sm">Role: {{ Auth::user()->role ?? 'Petugas' }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm w-full md:w-auto font-semibold shadow">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-4 md:p-6">

        <a href="{{ route('petugas.dashboard') }}" class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold text-sm">
            ← Kembali ke Dashboard
        </a>

        <div class="bg-white rounded-lg shadow-lg p-6 md:p-10 max-w-2xl mx-auto text-center">
            <div class="text-6xl mb-4">📱</div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Scan QR Code Anggota</h2>
            <p class="text-gray-600 mb-8">Scan QR Code pada kartu anggota, ATAU ketik NISN/NIP secara manual.</p>

            @if(session('success'))
                <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('petugas.scan.hasil') }}">
                @csrf
                <div class="mb-6">
                    <input type="text" name="qr_code" required autofocus
                           class="w-full px-4 py-4 text-center text-xl border-2 border-green-500 rounded-lg focus:outline-none focus:ring-4 focus:ring-green-200 transition"
                           placeholder="Scan atau ketik kode...">
                    <p class="text-xs text-gray-500 mt-2">💡 Kalau pakai scanner QR: cursor harus di kolom ini.</p>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-lg py-4 rounded-lg shadow-lg transition">
                    🔍 CARI ANGGOTA
                </button>
            </form>
        </div>

        {{-- Panduan --}}
        <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 md:p-6 max-w-2xl mx-auto mt-6 text-sm text-gray-700">
            <h3 class="font-bold text-blue-800 mb-2">ℹ️ Cara Pakai:</h3>
            <ol class="list-decimal list-inside space-y-1">
                <li><strong>Manual:</strong> Ketik NISN/NIP siswa di kolom, lalu klik "Cari Anggota".</li>
                <li><strong>Scanner QR:</strong> Colok scanner USB, klik kolom input, lalu scan QR pada kartu.</li>
                <li><strong>Hasil:</strong> Aplikasi akan menampilkan data siswa + peminjaman aktifnya.</li>
            </ol>
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4 mt-6">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>