<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Petugas - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

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
                <p class="font-semibold">Administrator</p>
                <p class="text-sm">Petugas Perpustakaan</p>
            </div>
        </div>
    </header>

    {{-- KONTEN --}}
    <div class="container mx-auto p-6">

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-4">🎉 Selamat Datang di Dashboard Petugas!</h2>
            <p class="text-gray-600 mb-6">
                Halo, <strong>Administrator</strong>! Anda berhasil login sebagai
                <strong>petugas</strong>.
            </p>

            {{-- Statistik --}}
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <div class="bg-blue-100 p-4 rounded-lg">
                    <p class="text-blue-800 font-semibold">📚 Total Buku</p>
                    <p class="text-3xl font-bold text-blue-900">{{ \App\Models\Book::count() }}</p>
                </div>
                <div class="bg-green-100 p-4 rounded-lg">
                    <p class="text-green-800 font-semibold">👥 Total Anggota</p>
                    <p class="text-3xl font-bold text-green-900">{{ \App\Models\User::whereIn('role', ['siswa', 'guru'])->count() }}</p>
                </div>
                <div class="bg-yellow-100 p-4 rounded-lg">
                    <p class="text-yellow-800 font-semibold">📂 Kategori</p>
                    <p class="text-3xl font-bold text-yellow-900">{{ \App\Models\Category::count() }}</p>
                </div>
                <div class="bg-purple-100 p-4 rounded-lg">
                    <p class="text-purple-800 font-semibold">📖 Sedang Dipinjam</p>
                    <p class="text-3xl font-bold text-purple-900">{{ \App\Models\Loan::where('status', 'dipinjam')->count() }}</p>
                </div>
            </div>
        </div>

        {{-- Menu Aksi --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">⚙️ Menu Aksi</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">

                {{-- Scan QR --}}
                <a href="{{ route('petugas.scan') }}"
                   class="bg-cyan-600 hover:bg-cyan-700 text-white p-4 rounded-lg flex items-center gap-3 transition">
                    <span class="text-3xl">📱</span>
                    <div>
                        <p class="font-bold">Scan QR Anggota</p>
                        <p class="text-xs opacity-90">Scan kartu untuk cek peminjaman</p>
                    </div>
                </a>

                {{-- Sirkulasi --}}
                <a href="{{ route('petugas.sirkulasi') }}"
                   class="bg-blue-600 hover:bg-blue-700 text-white p-4 rounded-lg flex items-center gap-3 transition">
                    <span class="text-3xl">📋</span>
                    <div>
                        <p class="font-bold">Sirkulasi Peminjaman</p>
                        <p class="text-xs opacity-90">Kelola peminjaman & pengembalian</p>
                    </div>
                </a>

                {{-- Kelola Buku --}}
                <a href="{{ route('petugas.kelolaBuku') }}"
                   class="bg-purple-600 hover:bg-purple-700 text-white p-4 rounded-lg flex items-center gap-3 transition">
                    <span class="text-3xl">📚</span>
                    <div>
                        <p class="font-bold">Kelola Buku</p>
                        <p class="text-xs opacity-90">Tambah, edit, hapus buku</p>
                    </div>
                </a>

                {{-- Kelola Anggota --}}
                <a href="{{ route('petugas.kelolaAnggota') }}"
                   class="bg-orange-600 hover:bg-orange-700 text-white p-4 rounded-lg flex items-center gap-3 transition">
                    <span class="text-3xl">👥</span>
                    <div>
                        <p class="font-bold">Kelola Anggota</p>
                        <p class="text-xs opacity-90">Kelola data siswa & guru</p>
                    </div>
                </a>

                {{-- Logout --}}
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="w-full bg-red-500 hover:bg-red-600 text-white p-4 rounded-lg flex items-center gap-3 transition text-left">
                        <span class="text-3xl">🚪</span>
                        <div>
                            <p class="font-bold">Logout</p>
                            <p class="text-xs opacity-90">Keluar dari aplikasi</p>
                        </div>
                    </button>
                </form>

            </div>
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>