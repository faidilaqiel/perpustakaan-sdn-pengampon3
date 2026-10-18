<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Dashboard Petugas - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen w-full overflow-x-hidden">

    {{-- HEADER RESPONSIF --}}
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

    {{-- KONTEN --}}
    <div class="container mx-auto p-4 md:p-6">
        
        {{-- Sambutan --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">🎉 Selamat Datang di Dashboard Petugas!</h2>
            <p class="text-gray-600">Halo, <strong>{{ Auth::user()->name ?? 'Administrator' }}</strong>. Anda berhasil login sebagai <span class="text-green-600 font-semibold">{{ Auth::user()->role ?? 'petugas' }}</span>.</p>
        </div>

        {{-- STATISTIK UTAMA --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
            <div class="bg-blue-100 p-4 rounded-lg shadow-sm border border-blue-200">
                <p class="text-blue-800 font-semibold text-xs md:text-sm">📚 Total Buku</p>
                <p class="text-2xl md:text-4xl font-bold text-blue-900 mt-1">{{ \App\Models\Book::count() }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg shadow-sm border border-green-200">
                <p class="text-green-800 font-semibold text-xs md:text-sm">👥 Total Anggota</p>
                <p class="text-2xl md:text-4xl font-bold text-green-900 mt-1">{{ \App\Models\User::whereIn('role', ['siswa', 'guru'])->count() }}</p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg shadow-sm border border-yellow-200">
                <p class="text-yellow-800 font-semibold text-xs md:text-sm">📂 Kategori</p>
                <p class="text-2xl md:text-4xl font-bold text-yellow-900 mt-1">{{ \App\Models\Category::count() }}</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg shadow-sm border border-purple-200">
                <p class="text-purple-800 font-semibold text-xs md:text-sm">📖 Sedang Dipinjam</p>
                <p class="text-2xl md:text-4xl font-bold text-purple-900 mt-1">{{ \App\Models\Loan::where('status', 'dipinjam')->count() }}</p>
            </div>
        </div>

        {{-- MENU AKSI (RESPONSIF: 1 Kolom di HP, 2-3 Kolom di Laptop) --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">⚙️ Menu Aksi</h3>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                {{-- Tombol Scan QR --}}
                <a href="{{ route('petugas.scan') }}" class="bg-teal-500 hover:bg-teal-600 text-white p-4 rounded-lg shadow-md transition flex items-center gap-4">
                    <span class="text-4xl">📷</span>
                    <div>
                        <h4 class="font-bold text-lg">Scan QR Anggota</h4>
                        <p class="text-sm opacity-90">Scan kartu untuk peminjaman</p>
                    </div>
                </a>

                {{-- Tombol Sirkulasi --}}
                <a href="{{ route('petugas.sirkulasi') }}" class="bg-blue-500 hover:bg-blue-600 text-white p-4 rounded-lg shadow-md transition flex items-center gap-4">
                    <span class="text-4xl">📋</span>
                    <div>
                        <h4 class="font-bold text-lg">Sirkulasi Peminjaman</h4>
                        <p class="text-sm opacity-90">Kelola peminjaman & pengembalian</p>
                    </div>
                </a>

                {{-- Tombol Kelola Buku --}}
                <a href="{{ route('petugas.kelolaBuku') }}" class="bg-purple-500 hover:bg-purple-600 text-white p-4 rounded-lg shadow-md transition flex items-center gap-4">
                    <span class="text-4xl">📚</span>
                    <div>
                        <h4 class="font-bold text-lg">Kelola Buku</h4>
                        <p class="text-sm opacity-90">Tambah, edit, hapus buku</p>
                    </div>
                </a>

                {{-- Tombol Kelola Anggota --}}
                <a href="{{ route('petugas.kelolaAnggota') }}" class="bg-orange-500 hover:bg-orange-600 text-white p-4 rounded-lg shadow-md transition flex items-center gap-4">
                    <span class="text-4xl">👥</span>
                    <div>
                        <h4 class="font-bold text-lg">Kelola Anggota</h4>
                        <p class="text-sm opacity-90">Kelola data siswa & guru</p>
                    </div>
                </a>

                {{-- Tombol Logout --}}
                <form method="POST" action="{{ route('logout') }}" class="w-full">
                    @csrf
                    <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white p-4 rounded-lg shadow-md transition flex items-center gap-4 text-left">
                        <span class="text-4xl">🚪</span>
                        <div>
                            <h4 class="font-bold text-lg">Logout</h4>
                            <p class="text-sm opacity-90">Keluar dari aplikasi</p>
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