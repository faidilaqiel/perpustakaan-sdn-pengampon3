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

    {{-- ================= SIDEBAR (TAMPILAN LAPTOP) ================= --}}
    <aside class="hidden md:flex flex-col w-64 bg-green-800 text-white h-screen fixed top-0 left-0 z-50 shadow-xl">
        <div class="h-20 flex items-center gap-3 px-6 bg-green-900 border-b border-green-700">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-10 h-10 bg-white rounded-full p-1">
            <div>
                <h1 class="font-bold text-sm leading-tight">Perpustakaan Digital</h1>
                <p class="text-xs text-green-300">SDN Pengampon 3</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            @if(Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.dashboard') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-home w-5 text-center"></i> Dashboard</a>
                <a href="{{ route('petugas.kelolaBuku') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kelolaBuku') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-book w-5 text-center"></i> Daftar Buku</a>
                <a href="{{ route('petugas.kategori.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kategori.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-folder w-5 text-center"></i> Kategori Buku</a>
                <a href="{{ route('petugas.sirkulasi') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.sirkulasi') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-exchange-alt w-5 text-center"></i> Sirkulasi</a>
                <a href="{{ route('petugas.kelolaAnggota') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kelolaAnggota') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-users w-5 text-center"></i> Data Anggota</a>
                <a href="{{ route('petugas.scan') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.scan') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-qrcode w-5 text-center"></i> Scan QR</a>
            @elseif(Auth::user()->role === 'kepsek')
                <a href="{{ route('kepsek.laporan') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('kepsek.laporan') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-chart-pie w-5 text-center"></i> Laporan</a>
            @endif
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('profil.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-user w-5 text-center"></i> Profil Saya</a>
        </nav>

        <div class="p-4 bg-green-900 border-t border-green-700 flex items-center gap-3">
            @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            @else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-lg font-bold">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
            @endif
            <div class="flex-1">
                <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-300 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </aside>

    {{-- ================= KONTEN UTAMA ================= --}}
    <div class="flex-1 flex flex-col h-screen md:ml-64 overflow-hidden relative">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-6 z-40">
            <div class="hidden md:flex items-center bg-gray-100 rounded-lg px-4 py-2 w-96">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" placeholder="Cari buku, anggota..." class="bg-transparent border-none outline-none text-sm w-full">
            </div>
            
            <div class="flex md:hidden items-center gap-3 w-full justify-between">
                <div class="flex items-center gap-3">
                    @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                        <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
                    @else
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-lg border border-green-200">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500">Selamat datang,</p>
                        <p class="text-sm font-bold text-gray-800 truncate max-w-[150px]">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <a href="{{ route('profil.edit') }}" class="w-8 h-8 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200"><i class="fas fa-ellipsis-v"></i></a>
            </div>

            <div class="hidden md:flex items-center gap-3">
                <a href="{{ route('profil.edit') }}" class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center hover:bg-green-200 transition" title="Profil"><i class="fas fa-user"></i></a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">
            @yield('content')
        </main>

        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around items-center h-16 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            @if(Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.dashboard') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-home text-lg mb-1"></i><span class="text-[10px] font-medium">Beranda</span></a>
                <a href="{{ route('petugas.kelolaBuku') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.kelolaBuku') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-book text-lg mb-1"></i><span class="text-[10px] font-medium">Buku</span></a>
                <a href="{{ route('petugas.sirkulasi') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.sirkulasi') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-exchange-alt text-lg mb-1"></i><span class="text-[10px] font-medium">Sirkulasi</span></a>
                <a href="{{ route('petugas.kelolaAnggota') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.kelolaAnggota') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-users text-lg mb-1"></i><span class="text-[10px] font-medium">Anggota</span></a>
            @elseif(Auth::user()->role === 'kepsek')
                <a href="{{ route('kepsek.laporan') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('kepsek.laporan') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-chart-pie text-lg mb-1"></i><span class="text-[10px] font-medium">Laporan</span></a>
            @endif
            <a href="{{ route('profil.edit') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profil.*') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-user text-lg mb-1"></i><span class="text-[10px] font-medium">Akun</span></a>
        </nav>
    </div>

    {{-- ================= FLOATING CHATBOT ================= --}}
    <button id="chat-toggle-btn" class="fixed bottom-20 md:bottom-6 right-6 bg-green-600 hover:bg-green-700 text-white w-14 h-14 rounded-full shadow-2xl flex items-center justify-center z-[9999] transition transform hover:scale-110">
        <i class="fas fa-comments text-2xl"></i>
    </button>

    <div id="chat-window" class="fixed bottom-36 md:bottom-24 right-6 w-80 md:w-96 bg-white rounded-2xl shadow-2xl border border-gray-200 z-[9999] hidden flex-col overflow-hidden" style="height: 450px;">
        <div class="bg-green-600 text-white p-4 flex justify-between items-center shrink-0">
            <div class="flex items-center gap-3">
                <i class="fas fa-robot text-xl"></i>
                <div>
                    <p class="font-bold text-sm">Asisten Perpustakaan</p>
                    <p class="text-[10px] opacity-80">Online</p>
                </div>
            </div>
            <button id="chat-close-btn" class="text-white hover:text-gray-200"><i class="fas fa-times text-lg"></i></button>
        </div>
        <div id="chat-messages" class="flex-1 p-4 overflow-y-auto bg-gray-50 flex flex-col gap-3">
            <div class="bg-white border border-gray-200 p-3 rounded-lg rounded-tl-none text-sm text-gray-700 shadow-sm self-start max-w-[80%]">
                Halo! Saya asisten perpustakaan digital. Ada yang bisa saya bantu?
            </div>
        </div>
        <div class="p-3 bg-white border-t border-gray-200 shrink-0">
            <div class="flex gap-2">
                <input type="text" id="chat-input" placeholder="Ketik pesan..." class="flex-1 border border-gray-300 rounded-lg px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-green-500">
                <button id="chat-send-btn" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2 rounded-lg text-sm font-semibold"><i class="fas fa-paper-plane"></i></button>
            </div>
        </div>
    </div>

    <script>
        // URL Chatbot dari Railway
        const CHATBOT_API_URL = 'https://secure-fascination-production-3402.up.railway.app/api/chat'; 

        const chatToggleBtn = document.getElementById('chat-toggle-btn');
        const chatWindow = document.getElementById('chat-window');
        const chatCloseBtn = document.getElementById('chat-close-btn');
        const chatInput = document.getElementById('chat-input');
        const chatSendBtn = document.getElementById('chat-send-btn');
        const chatMessages = document.getElementById('chat-messages');

        chatToggleBtn.addEventListener('click', () => {
            chatWindow.classList.toggle('hidden');
            chatWindow.classList.toggle('flex');
            if (!chatWindow.classList.contains('hidden')) chatInput.focus();
        });
        chatCloseBtn.addEventListener('click', () => {
            chatWindow.classList.add('hidden');
            chatWindow.classList.remove('flex');
        });

        async function sendMessage() {
            const message = chatInput.value.trim();
            if (!message) return;
            appendMessage(message, 'user');
            chatInput.value = '';
            const loadingId = appendMessage('Sedang mengetik...', 'bot', true);
            try {
                const response = await fetch(CHATBOT_API_URL, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ message: message })
                });
                const data = await response.json();
                document.getElementById(loadingId).remove();
                appendMessage(data.reply || 'Maaf, saya tidak mengerti.', 'bot');
            } catch (error) {
                document.getElementById(loadingId).remove();
                appendMessage('Maaf, koneksi ke server AI gagal.', 'bot');
            }
        }

        function appendMessage(text, sender, isLoading = false) {
            const id = 'msg-' + Date.now();
            const div = document.createElement('div');
            div.id = id;
            if (sender === 'user') {
                div.className = 'bg-green-600 text-white p-3 rounded-lg rounded-tr-none text-sm shadow-sm self-end max-w-[80%] ml-auto';
            } else {
                div.className = 'bg-white border border-gray-200 p-3 rounded-lg rounded-tl-none text-sm text-gray-700 shadow-sm self-start max-w-[80%]';
            }
            if (isLoading) div.innerHTML = `<span class="animate-pulse">${text}</span>`;
            else div.textContent = text;
            chatMessages.appendChild(div);
            chatMessages.scrollTop = chatMessages.scrollHeight;
            return id;
        }
        chatSendBtn.addEventListener('click', sendMessage);
        chatInput.addEventListener('keypress', (e) => { if (e.key === 'Enter') sendMessage(); });
    </script>
</body>
</html>