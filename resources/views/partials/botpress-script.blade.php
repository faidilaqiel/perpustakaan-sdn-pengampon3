{{-- ================= CUSTOM CHATBOT PUSTAKAAI (POWERED BY GOOGLE GEMINI & MYSQL) ================= --}}

@php
    $loggedInUser = Auth::user();
    $userName     = $loggedInUser ? $loggedInUser->name : 'Pengunjung';
    $userClass    = $loggedInUser?->class;

    $userRole = match (strtolower($loggedInUser?->role ?? '')) {
        'petugas' => 'Petugas Perpustakaan',
        'kepsek'  => 'Kepala Sekolah',
        'guru'    => 'Guru',
        'siswa'   => 'Siswa Kelas ' . ($userClass ?? '-'),
        default   => 'Pengguna',
    };
@endphp

<!-- Floating Toggle Button di Pojok Kanan Bawah -->
<div id="pustakaai-toggle" onclick="togglePustakaAIChat()" class="fixed bottom-6 right-6 z-50 cursor-pointer transition transform hover:scale-110 shadow-2xl rounded-full bg-blue-600 flex items-center justify-center w-14 h-14 border-2 border-white overflow-hidden p-0.5">
    <img src="{{ asset('images/pustakaai-avatar.png') }}" alt="PustakaAI" class="w-full h-full object-cover rounded-full">
</div>

<!-- Modal Jendela Chatbot (Tampilan Sama Persis Seperti Sebelumnya) -->
<div id="pustakaai-window" class="hidden fixed bottom-24 right-6 z-50 w-[380px] sm:w-[410px] h-[580px] bg-white rounded-3xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden font-sans">
    
    <!-- Header Chatbot -->
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-white">
        <div class="flex items-center gap-3">
            <button onclick="kembaliKeMenuAwal()" class="text-gray-400 hover:text-gray-600 p-1" title="Kembali ke Menu Awal">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <div class="w-9 h-9 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center overflow-hidden">
                <img src="{{ asset('images/pustakaai-avatar.png') }}" class="w-full h-full object-cover">
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800 leading-tight">PustakaAI</h4>
                <p class="text-[11px] text-gray-400 truncate max-w-[170px]">Pintu gerbang ilmu di SDN Pengampo...</p>
            </div>
        </div>
        <div class="flex items-center gap-2 text-gray-400">
            <button onclick="resetPercakapan()" class="hover:text-gray-600 p-1" title="Mulai Ulang Percakapan">
                <i class="fas fa-ellipsis-h text-sm"></i>
            </button>
            <button onclick="togglePustakaAIChat()" class="hover:text-gray-600 p-1" title="Tutup">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>
    </div>

    <!-- Body Percakapan -->
    <div id="pustakaai-body" class="flex-1 overflow-y-auto p-5 space-y-4 bg-[#fbfbfb]">
        
        <!-- Welcome Screen (Judul "Halo!" & Kartu Cepat) -->
        <div id="welcome-section" class="space-y-4">
            <div>
                <h2 class="text-2xl font-extrabold text-gray-900 leading-tight tracking-tight">
                    Halo! Ada yang bisa<br>PustakaAI bantu?
                </h2>
                <p class="text-sm text-gray-500 mt-1">
                    Layanan informasi digital resmi Perpustakaan SDN Pengampon 3
                </p>
            </div>

            <!-- 3-4 Kartu Menu Cepat -->
            <div class="space-y-2.5 pt-1">
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Kartu 1: Cara Pinjam Buku -->
                    <div onclick="pilihMenuCepat('Bagaimana cara meminjam buku di perpustakaan ini?')" class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-xs hover:border-blue-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-xs text-gray-800 leading-tight">Cara Pinjam Buku</h5>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Syarat dan panduan peminjaman</p>
                        </div>
                    </div>
                    <!-- Kartu 2: Cari Koleksi Buku -->
                    <div onclick="pilihMenuCepat('Berapa jumlah buku yang ada di perpustakaan?')" class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-xs hover:border-blue-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2">
                            <i class="fas fa-search"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-xs text-gray-800 leading-tight">Cari Koleksi Buku</h5>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Temukan buku cerita & pelajaran</p>
                        </div>
                    </div>
                </div>

                <!-- Kartu 3: Rekomendasi Buku -->
                <div onclick="pilihMenuCepat('Rekomendasikan buku seru dan paling favorit yang sering dipinjam!')" class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-xs hover:border-blue-400 hover:shadow-md transition cursor-pointer w-[52%]">
                    <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2">
                        <i class="fas fa-sparkles"></i>
                    </div>
                    <div>
                        <h5 class="font-bold text-xs text-gray-800 leading-tight">Rekomendasi buku seru</h5>
                        <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Daftar buku terfavorit minggu ini</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wadah Bubble Chat -->
        <div id="chat-messages" class="space-y-3"></div>

        <!-- Loading Indicator ("PustakaAI sedang mengetik...") -->
        <div id="chat-loading" class="hidden flex items-center gap-2 text-gray-400 text-xs italic">
            <div class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center overflow-hidden border border-blue-200">
                <img src="{{ asset('images/pustakaai-avatar.png') }}" class="w-full h-full object-cover">
            </div>
            <div class="bg-gray-100 px-3.5 py-1.5 rounded-full flex items-center gap-1.5 shadow-xs">
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce"></span>
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                <span class="ml-1 text-[11px] text-gray-500 font-medium">Mengetik...</span>
            </div>
        </div>

    </div>

    <!-- Kolom Input (Desain Tetap Sama Persis) -->
    <div class="p-3 bg-white border-t border-gray-100">
        <div class="border border-gray-200 rounded-2xl p-2.5 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition bg-white">
            <input type="text" id="chat-input" placeholder="Tanya PustakaAI di sini..." class="w-full text-xs text-gray-700 outline-none bg-transparent" onkeydown="handleInputEnter(event)">
            <div class="flex items-center justify-between pt-2 mt-1 border-t border-gray-50 text-gray-400">
                <div class="flex items-center gap-3 text-sm">
                    <button type="button" class="hover:text-gray-600"><i class="fas fa-paperclip"></i></button>
                    <button type="button" class="hover:text-gray-600"><i class="far fa-smile"></i></button>
                    <button type="button" class="hover:text-gray-600"><i class="fas fa-microphone"></i></button>
                </div>
                <button type="button" onclick="kirimPesan()" class="text-blue-600 hover:text-blue-800 p-1">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
        </div>
        <div class="text-center mt-1.5 text-[9px] text-gray-400">
            ⚡ Powered by Google Gemini API & MySQL Real-Time
        </div>
    </div>

</div>

<script>
    // Konfigurasi endpoint server Node.js Gemini
    const CHAT_BACKEND_URL = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1'
        ? "http://localhost:3000/api/chat"
        : "/api/chat";

    const USER_NAME = @json($userName ?? 'Faidil Aqiel');
    const USER_ROLE = @json($userRole ?? 'Petugas Perpustakaan');

    function togglePustakaAIChat() {
        const win = document.getElementById('pustakaai-window');
        win.classList.toggle('hidden');
        if (!win.classList.contains('hidden')) {
            document.getElementById('chat-input').focus();
        }
    }

    function kembaliKeMenuAwal() {
        document.getElementById('welcome-section').classList.remove('hidden');
    }

    function resetPercakapan() {
        document.getElementById('chat-messages').innerHTML = '';
        document.getElementById('welcome-section').classList.remove('hidden');
    }

    function pilihMenuCepat(pertanyaan) {
        document.getElementById('chat-input').value = pertanyaan;
        kirimPesan();
    }

    function handleInputEnter(e) {
        if (e.key === 'Enter') {
            kirimPesan();
        }
    }

    async function kirimPesan() {
        const input = document.getElementById('chat-input');
        const pesan = input.value.trim();
        if (!pesan) return;

        // Sembunyikan welcome screen saat percakapan dimulai
        document.getElementById('welcome-section').classList.add('hidden');

        // 1. Tampilkan Bubble Pesan User di Kanan
        tampilkanBubbleUser(pesan);
        input.value = '';

        // 2. Tampilkan Indikator Loading ("Mengetik...")
        const loader = document.getElementById('chat-loading');
        loader.classList.remove('hidden');
        scrollKeBawah();

        try {
            // 3. Fetch ke Backend Node.js Google Gemini API
            const res = await fetch(CHAT_BACKEND_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    pesan: pesan,
                    userName: USER_NAME,
                    userRole: USER_ROLE
                })
            });

            const data = await res.json();
            loader.classList.add('hidden');

            // 4. Tampilkan Balasan dari Backend Gemini di Kiri
            if (data && data.jawaban) {
                tampilkanBubbleBot(data.jawaban);
            } else {
                tampilkanBubbleBot("Maaf, belum ada respons dari AI.");
            }
        } catch (err) {
            console.error(err);
            loader.classList.add('hidden');
            tampilkanBubbleBot("❌ Tidak dapat terhubung ke server Chatbot Gemini (" + err.message + "). Pastikan backend Node.js (server.js) sedang aktif.");
        }
        scrollKeBawah();
    }

    function tampilkanBubbleUser(teks) {
        const container = document.getElementById('chat-messages');
        const bubble = document.createElement('div');
        bubble.className = "flex justify-end";
        bubble.innerHTML = `
            <div class="bg-blue-600 text-white text-xs px-4 py-2.5 rounded-2xl rounded-tr-xs max-w-[80%] shadow-xs break-words">
                ${escapeHtml(teks)}
            </div>
        `;
        container.appendChild(bubble);
        scrollKeBawah();
    }

    function formatMarkdown(teks) {
        if (!teks) return '';
        let escaped = escapeHtml(teks);
        // Ubah format bold markdown **teks** menjadi <strong>teks</strong>
        escaped = escaped.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
        // Ubah format bullet point * baris menjadi simbol bullet rapi
        escaped = escaped.replace(/(?:^|<br>)\*\s+(.*?)(?=<br>|$)/g, '<br>• $1');
        // Ubah karakter baris baru \n menjadi <br>
        escaped = escaped.replace(/\n/g, '<br>');
        // Bersihkan multiple <br> berlebih
        escaped = escaped.replace(/(<br>\s*){3,}/g, '<br><br>');
        return escaped;
    }

    function tampilkanBubbleBot(teks) {
        const container = document.getElementById('chat-messages');
        const bubble = document.createElement('div');
        bubble.className = "flex items-start gap-2";
        
        const formatted = formatMarkdown(teks);
        bubble.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-blue-50 flex items-center justify-center shrink-0 mt-0.5 overflow-hidden border border-blue-200">
                <img src="{{ asset('images/pustakaai-avatar.png') }}" class="w-full h-full object-cover">
            </div>
            <div class="bg-white border border-gray-100 text-gray-800 text-xs px-3.5 py-2.5 rounded-2xl rounded-tl-xs max-w-[85%] shadow-xs leading-relaxed break-words">
                ${formatted}
            </div>
        `;
        container.appendChild(bubble);
        scrollKeBawah();
    }

    function scrollKeBawah() {
        const body = document.getElementById('pustakaai-body');
        body.scrollTop = body.scrollHeight;
    }

    function escapeHtml(string) {
        return String(string).replace(/[&<>"'`=\/]/g, function (s) {
            return {
                '&': '&amp;',
                '<': '&lt;',
                '>': '&gt;',
                '"': '&quot;',
                "'": '&#39;',
                '/': '&#x2F;',
                '`': '&#x60;',
                '=': '&#x3D;'
            }[s];
        });
    }
</script>
