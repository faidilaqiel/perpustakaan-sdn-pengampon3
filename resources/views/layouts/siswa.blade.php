<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>@yield('title', 'Perpustakaan Digital')</title>
    <!-- Favicon dengan nama file baru -->
    <link rel="icon" href="{{ asset('images/logo-perpus.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input { font-size: 16px !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex overflow-hidden">

    {{-- SIDEBAR (LAPTOP) --}}
    <aside class="hidden md:flex flex-col w-64 bg-green-800 text-white h-screen fixed top-0 left-0 z-50 shadow-xl">
        <div class="h-20 flex items-center gap-3 px-6 bg-green-900 border-b border-green-700">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-10 h-10 bg-white rounded-full p-1">
            <div>
                <h1 class="font-bold text-sm leading-tight">Perpustakaan Digital</h1>
                <p class="text-xs text-green-300">SDN Pengampon 3</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            <a href="{{ route('siswa.katalog') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('siswa.katalog') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-book-open w-5 text-center"></i> Katalog Buku
            </a>
            <a href="{{ route('siswa.riwayat') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('siswa.riwayat') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-history w-5 text-center"></i> Riwayat Pinjam
            </a>
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('profil.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}">
                <i class="fas fa-user w-5 text-center"></i> Profil Saya
            </a>
        </nav>

        <div class="p-4 bg-green-900 border-t border-green-700 flex items-center gap-3">
            @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            @else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-lg font-bold">
                    {{ substr(Auth::user()->name ?? 'S', 0, 1) }}
                </div>
            @endif
            <div class="flex-1">
                <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-300 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex-1 flex flex-col h-screen md:ml-64 overflow-hidden relative">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-6 z-40">
            <div class="hidden md:flex items-center text-gray-500 font-medium">
                Selamat datang, <span class="text-green-600 font-bold ml-1">{{ Auth::user()->name }}</span>
            </div>

            <div class="flex md:hidden items-center gap-3 w-full justify-between">
                <div class="flex items-center gap-3">
                    @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                        <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
                    @else
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-lg border border-green-200">{{ substr(Auth::user()->name ?? 'S', 0, 1) }}</div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500">Selamat datang,</p>
                        <p class="text-sm font-bold text-gray-800 truncate max-w-[150px]">{{ Auth::user()->name }}</p>
                    </div>
                </div>
            </div>

            <div class="flex items-center gap-3">
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

        {{-- BOTTOM NAV (HP) --}}
        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around items-center h-16 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            <a href="{{ route('siswa.katalog') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('siswa.katalog') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-book-open text-lg mb-1"></i><span class="text-[10px] font-medium">Katalog</span>
            </a>
            <a href="{{ route('siswa.riwayat') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('siswa.riwayat') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-history text-lg mb-1"></i><span class="text-[10px] font-medium">Riwayat</span>
            </a>
            <a href="{{ route('profil.edit') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profil.*') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}">
                <i class="fas fa-user text-lg mb-1"></i><span class="text-[10px] font-medium">Akun</span>
            </a>
        </nav>
    </div>

    {{-- ================= FLOATING CHATBOT (BOTPRESS) ================= --}}
    <script src="https://cdn.botpress.cloud/webchat/v5.0/inject.js"></script>
    <script src="https://files.bpcontent.cloud/2026/10/20/07/20261020071537-L4781M7K.js" defer></script>

    <script>
        const botUserData = {
            name: @json(Auth::user()->name ?? 'Siswa'),
            role: 'Siswa',
            userName: @json(Auth::user()->name ?? 'Siswa'),
            userRole: 'Siswa'
        };

        const sendUserToBotpress = () => window.botpress && window.botpress.updateUser && window.botpress.updateUser({ data: botUserData });

        const waitForBotpress = setInterval(() => {
            if (window.botpress && window.botpress.updateUser) {
                clearInterval(waitForBotpress);
                sendUserToBotpress();
                window.botpress.on('webchat:opened', sendUserToBotpress);
            }
        }, 500);
    </script>
</body>
</html>