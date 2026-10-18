<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Perpustakaan Digital')</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input { font-size: 16px !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex overflow-hidden">

    {{-- ================= SIDEBAR (LAPTOP) ================= --}}
    <aside class="hidden md:flex flex-col w-64 bg-green-800 text-white h-screen fixed top-0 left-0 z-50 shadow-xl">
        {{-- Logo --}}
        <div class="h-20 flex items-center gap-3 px-6 bg-green-900 border-b border-green-700">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-10 h-10 bg-white rounded-full p-1">
            <div>
                <h1 class="font-bold text-sm leading-tight">Perpustakaan Digital</h1>
                <p class="text-xs text-green-300">SDN Pengampon 3</p>
            </div>
        </div>

        {{-- Menu Dinamis --}}
        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            @if(Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.dashboard') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-home w-5 text-center"></i> Dashboard
                </a>
                <a href="{{ route('petugas.kelolaBuku') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kelolaBuku') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-book w-5 text-center"></i> Daftar Buku
                </a>
                <a href="{{ route('petugas.kategori.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kategori.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-folder w-5 text-center"></i> Kategori Buku
                </a>
                <a href="{{ route('petugas.sirkulasi') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.sirkulasi') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-exchange-alt w-5 text-center"></i> Sirkulasi
                </a>
                <a href="{{ route('petugas.kelolaAnggota') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kelolaAnggota') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-users w-5 text-center"></i> Data Anggota
                </a>
                <a href="{{ route('petugas.scan') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.scan') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-qrcode w-5 text-center"></i> Scan QR
                </a>
            @elseif(Auth::user()->role === 'kepsek')
                <a href="{{ route('kepsek.laporan') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('kepsek.laporan') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                    <i class="fas fa-chart-pie w-5 text-center"></i> Laporan
                </a>
            @endif
            
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('profil.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-user w-5 text-center"></i> Profil Saya
            </a>
        </nav>

        {{-- Profil Dinamis --}}
        <div class="p-4 bg-green-900 border-t border-green-700 flex items-center gap-3">
            @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            @else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-lg font-bold">
                    {{ substr(Auth::user()->name ?? 'A', 0, 1) }}
                </div>
            @endif
            <div class="flex-1">
                <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-300 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </aside>

    {{-- ================= KONTEN UTAMA ================= --}}
    <div class="flex-1 flex flex-col h-screen md:ml-64 overflow-hidden relative">
        
        {{-- NAVBAR ATAS --}}
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-6 z-40">
            <div class="hidden md:flex items-center bg-gray-100 rounded-lg px-4 py-2 w-96">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" placeholder="Cari buku, anggota..." class="bg-transparent border-none outline-none text-sm w-full">
            </div>
            
            <div class="flex md:hidden items-center gap-2">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-8 h-8 bg-white rounded-full p-1">
                <span class="font-bold text-green-800 text-sm">Perpustakaan Digital</span>
            </div>

            <div class="flex items-center gap-3">
                {{-- Tombol Profil Atas --}}
                <a href="{{ route('profil.edit') }}" class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center hover:bg-green-200 transition" title="Profil">
                    <i class="fas fa-user"></i>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition" title="Logout">
                        <i class="fas fa-sign-out-alt"></i>
                    </button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">
            @yield('content')
        </main>

        {{-- BOTTOM NAV (TAMPILAN HP) --}}
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around items-center h-16 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            @if(Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.dashboard') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                    <i class="fas fa-home text-lg mb-1"></i><span class="text-[10px] font-medium">Beranda</span>
                </a>
                <a href="{{ route('petugas.kelolaBuku') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.kelolaBuku') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                    <i class="fas fa-book text-lg mb-1"></i><span class="text-[10px] font-medium">Buku</span>
                </a>
                <a href="{{ route('petugas.sirkulasi') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.sirkulasi') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                    <i class="fas fa-exchange-alt text-lg mb-1"></i><span class="text-[10px] font-medium">Sirkulasi</span>
                </a>
                <a href="{{ route('petugas.kelolaAnggota') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.kelolaAnggota') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                    <i class="fas fa-users text-lg mb-1"></i><span class="text-[10px] font-medium">Anggota</span>
                </a>
            @elseif(Auth::user()->role === 'kepsek')
                <a href="{{ route('kepsek.laporan') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('kepsek.laporan') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                    <i class="fas fa-chart-pie text-lg mb-1"></i><span class="text-[10px] font-medium">Laporan</span>
                </a>
            @endif
            {{-- Menu Akun --}}
            <a href="{{ route('profil.edit') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profil.*') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-user text-lg mb-1"></i><span class="text-[10px] font-medium">Akun</span>
            </a>
        </nav>
    </div>

</body>
</html>