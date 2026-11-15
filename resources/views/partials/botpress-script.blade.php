{{-- ================= CUSTOM CHATBOT PUSTAKAAI (POWERED BY GOOGLE GEMINI & MYSQL REAL-TIME) ================= --}}

<!-- Floating Toggle Button di Pojok Kanan Bawah (Tampilan Sama Persis) -->
<div id="pustakaai-toggle" onclick="togglePustakaAIChat()" class="fixed bottom-6 right-6 z-50 cursor-pointer transition transform hover:scale-105 active:scale-95 shadow-2xl rounded-full bg-blue-600 flex items-center justify-center w-14 h-14 border-2 border-white" title="Tanya PustakaAI">
    <div class="relative w-9 h-9 flex items-center justify-center">
        <div class="w-8 h-8 rounded-full bg-white flex items-center justify-center shadow-inner overflow-hidden">
            <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
            </svg>
        </div>
        <span class="absolute top-0 right-0 w-2.5 h-2.5 bg-green-400 border border-white rounded-full"></span>
    </div>
</div>

<!-- Modal Jendela Chatbot (Desain UI, Header, Tombol, Warna 100% Presisi) -->
<div id="pustakaai-window" class="hidden fixed bottom-24 right-4 sm:right-6 z-50 w-[92vw] sm:w-[410px] h-[580px] max-h-[85vh] bg-white rounded-3xl shadow-2xl border border-gray-100 flex flex-col overflow-hidden font-sans">
    
    <!-- Header Chatbot -->
    <div class="px-5 py-3.5 border-b border-gray-100 flex items-center justify-between bg-white shrink-0">
        <div class="flex items-center gap-2.5">
            <button onclick="kembaliKeMenuAwal()" class="text-gray-400 hover:text-gray-600 p-1 rounded-lg transition" title="Kembali ke Beranda">
                <i class="fas fa-chevron-left text-sm"></i>
            </button>
            <div class="w-9 h-9 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center p-1 overflow-hidden">
                <svg class="w-5 h-5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800 leading-tight">PustakaAI</h4>
                <p class="text-[11px] text-gray-400 truncate max-w-[170px]">Pintu gerbang ilmu di SDN Pengampo...</p>
            </div>
        </div>
        <div class="flex items-center gap-1 text-gray-400">
            <button onclick="resetPercakapan()" class="hover:text-gray-700 p-1.5 rounded-lg transition" title="Mulai Ulang Percakapan">
                <i class="fas fa-ellipsis-h text-sm"></i>
            </button>
            <button onclick="togglePustakaAIChat()" class="hover:text-gray-700 p-1.5 rounded-lg transition" title="Tutup">
                <i class="fas fa-times text-base"></i>
            </button>
        </div>
    </div>

    <!-- Body Percakapan -->
    <div id="pustakaai-body" class="flex-1 overflow-y-auto p-4 sm:p-5 space-y-4 bg-[#fbfbfb]">
        
        <!-- Welcome Screen (Judul "Halo!" & Kartu Menu Cepat) -->
        <div id="welcome-section" class="space-y-4">
            <div class="pt-1">
                <h2 class="text-2xl font-extrabold text-gray-900 leading-tight tracking-tight">
                    Halo! Ada yang bisa<br>PustakaAI bantu?
                </h2>
                <p class="text-xs sm:text-sm text-gray-500 mt-1">
                    Layanan informasi digital resmi Perpustakaan SDN Pengampon 3
                </p>
            </div>

            <!-- Kartu Menu Cepat -->
            <div class="space-y-2.5 pt-1">
                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Kartu 1 -->
                    <div onclick="pilihMenuCepat('Bagaimana cara meminjam buku di perpustakaan ini?')" class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-xs hover:border-blue-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2">
                            <i class="fas fa-tag"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-xs text-gray-800 leading-tight">Cara Pinjam Buku</h5>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Syarat dan panduan peminjaman</p>
                        </div>
                    </div>
                    <!-- Kartu 2 -->
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

                <div class="grid grid-cols-2 gap-2.5">
                    <!-- Kartu 3 -->
                    <div onclick="pilihMenuCepat('Rekomendasikan buku seru dan paling favorit yang sering dipinjam!')" class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-xs hover:border-blue-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2">
                            <i class="fas fa-sparkles"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-xs text-gray-800 leading-tight">Rekomendasi buku seru</h5>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Daftar buku terfavorit minggu ini</p>
                        </div>
                    </div>
                    <!-- Kartu 4 -->
                    <div onclick="pilihMenuCepat('Ada berapa total transaksi di perpustakaan ini?')" class="bg-white p-3.5 rounded-2xl border border-gray-100 shadow-xs hover:border-blue-400 hover:shadow-md transition cursor-pointer flex flex-col justify-between">
                        <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm mb-2">
                            <i class="fas fa-exchange-alt"></i>
                        </div>
                        <div>
                            <h5 class="font-bold text-xs text-gray-800 leading-tight">Total Transaksi</h5>
                            <p class="text-[10px] text-gray-400 mt-0.5 leading-tight">Cek sirkulasi buku real-time</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Wadah Bubble Pesan Chat -->
        <div id="chat-messages" class="space-y-3"></div>

        <!-- Indikator Loading "Mengetik..." -->
        <div id="chat-loading" class="hidden flex items-center gap-2 text-gray-400 text-xs italic">
            <div class="w-6 h-6 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center shrink-0">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
            </div>
            <div class="bg-white border border-gray-100 px-3 py-1.5 rounded-full shadow-xs flex items-center gap-1.5">
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce"></span>
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce [animation-delay:0.2s]"></span>
                <span class="w-1.5 h-1.5 bg-blue-500 rounded-full animate-bounce [animation-delay:0.4s]"></span>
                <span class="ml-1 text-[11px] text-gray-500 font-medium">PustakaAI sedang mengetik...</span>
            </div>
        </div>

    </div>

    <!-- Kolom Input (Desain Tetap Sama Persis) -->
    <div class="p-3 bg-white border-t border-gray-100 shrink-0">
        <div class="border border-gray-200 rounded-2xl p-2.5 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition bg-white">
            <input type="text" id="chat-input" placeholder="Tanya PustakaAI di sini..." class="w-full text-xs text-gray-700 outline-none bg-transparent" onkeydown="handleInputEnter(event)" autocomplete="off">
            <div class="flex items-center justify-between pt-2 mt-1 border-t border-gray-50 text-gray-400">
                <div class="flex items-center gap-3 text-sm">
                    <button type="button" class="hover:text-gray-600 transition"><i class="fas fa-paperclip"></i></button>
                    <button type="button" class="hover:text-gray-600 transition"><i class="far fa-smile"></i></button>
                    <button type="button" class="hover:text-gray-600 transition"><i class="fas fa-microphone"></i></button>
                </div>
                <button type="button" onclick="kirimPesan()" class="text-blue-600 hover:text-blue-800 p-1 transition">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
        </div>
        <div class="text-center mt-1.5 text-[9px] text-gray-400 font-medium">
            ⚡ Powered by Google Gemini AI & MySQL Real-Time
        </div>
    </div>

</div>

<!-- Script Logika Frontend: Menghubungkan ke Node.js Backend Gemini -->
<script>
    // URL Backend Node.js
    const CHAT_BACKEND_URL = "http://localhost:3000/api/chat";
    const USER_NAME = @json(Auth::user()->name ?? 'Faidil Aqiel');
    const USER_ROLE = @json(Auth::user()->role === 'petugas' ? 'Petugas Perpustakaan' : (Auth::user()->role === 'kepsek' ? 'Kepala Sekolah' : (Auth::user()->role === 'guru' ? 'Guru' : 'Siswa')));

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

        // Sembunyikan kartu welcome agar rapi
        document.getElementById('welcome-section').classList.add('hidden');

        // 1. Tampilkan Bubble Pesan User di Kanan
        tampilkanBubbleUser(pesan);
        input.value = '';

        // 2. Tampilkan Indikator Loading
        const loader = document.getElementById('chat-loading');
        loader.classList.remove('hidden');
        scrollKeBawah();

        try {
            // 3. Fetch ke Backend Node.js /api/chat
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

            // 4. Tampilkan Balasan dari Gemini di Kiri
            if (data.jawaban) {
                tampilkanBubbleBot(data.jawaban);
            } else {
                tampilkanBubbleBot("Maaf, tidak ada jawaban dari server AI.");
            }
        } catch (err) {
            console.error(err);
            loader.classList.add('hidden');
            tampilkanBubbleBot("❌ Tidak dapat terhubung ke server backend Chatbot Node.js. Pastikan server aktif (`node server.js` di folder chatbot-server).");
        }
        scrollKeBawah();
    }

    function tampilkanBubbleUser(teks) {
        const container = document.getElementById('chat-messages');
        const bubble = document.createElement('div');
        bubble.className = "flex justify-end";
        bubble.innerHTML = `
            <div class="bg-blue-600 text-white text-xs px-4 py-2.5 rounded-2xl rounded-tr-xs max-w-[82%] shadow-xs break-words">
                ${escapeHtml(teks)}
            </div>
        `;
        container.appendChild(bubble);
        scrollKeBawah();
    }

    function tampilkanBubbleBot(teks) {
        const container = document.getElementById('chat-messages');
        const bubble = document.createElement('div');
        bubble.className = "flex items-start gap-2";
        
        // Parse formatting bold **teks** dan newline
        let parsed = escapeHtml(teks)
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
            .replace(/\n/g, '<br>');

        bubble.innerHTML = `
            <div class="w-6 h-6 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center shrink-0 mt-0.5">
                <svg class="w-3.5 h-3.5 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17.93c-3.95-.49-7-3.85-7-7.93 0-.62.08-1.21.21-1.79L9 15v1c0 1.1.9 2 2 2v1.93zm6.9-2.54c-.26-.81-1-1.39-1.9-1.39h-1v-3c0-.55-.45-1-1-1H8v-2h2c.55 0 1-.45 1-1V7h2c1.1 0 2-.9 2-2v-.41c2.93 1.19 5 4.06 5 7.41 0 2.08-.8 3.97-2.1 5.39z"/>
                </svg>
            </div>
            <div class="bg-white border border-gray-100 text-gray-800 text-xs px-3.5 py-2.5 rounded-2xl rounded-tl-xs max-w-[85%] shadow-xs leading-relaxed break-words">
                ${parsed}
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
