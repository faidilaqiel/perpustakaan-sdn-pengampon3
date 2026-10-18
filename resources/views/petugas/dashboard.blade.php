<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Petugas - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
        <h2 class="text-2xl font-bold text-gray-800 mb-6 text-center md:text-left">Dashboard Petugas</h2>

        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('petugas.sirkulasi') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center border-t-4 border-blue-500">
                <span class="text-4xl block mb-2">📋</span>
                <h3 class="font-bold text-gray-800 text-lg">Sirkulasi</h3>
                <p class="text-xs text-gray-500 mt-1">Kelola pinjam & kembali</p>
            </a>
            
            <a href="{{ route('petugas.kelolaBuku') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center border-t-4 border-green-500">
                <span class="text-4xl block mb-2">📚</span>
                <h3 class="font-bold text-gray-800 text-lg">Kelola Buku</h3>
                <p class="text-xs text-gray-500 mt-1">Tambah & edit buku</p>
            </a>
            
            <a href="{{ route('petugas.kelolaAnggota') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center border-t-4 border-purple-500">
                <span class="text-4xl block mb-2">👥</span>
                <h3 class="font-bold text-gray-800 text-lg">Kelola Anggota</h3>
                <p class="text-xs text-gray-500 mt-1">Data siswa & guru</p>
            </a>

            <a href="{{ route('petugas.scan') }}" class="bg-white p-6 rounded-lg shadow hover:shadow-lg transition text-center border-t-4 border-yellow-500">
                <span class="text-4xl block mb-2">📷</span>
                <h3 class="font-bold text-gray-800 text-lg">Scan QR</h3>
                <p class="text-xs text-gray-500 mt-1">Absensi kunjungan</p>
            </a>
        </div>
    </div>

    <footer class="text-center text-sm text-gray-500 py-4 mt-6">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>