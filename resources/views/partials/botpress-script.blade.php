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
            <div class="w-10 h-10 rounded-full bg-blue-50 border border-blue-200 flex items-center justify-center overflow-hidden shrink-0">
                <img src="{{ asset('images/pustakaai-avatar.png') }}" class="w-full h-full object-cover">
            </div>
            <div>
                <h4 class="font-bold text-sm text-gray-800 leading-tight">PustakaAI</h4>
                <p class="text-[11px] text-gray-400 truncate max-w-[200px]">Pintu gerbang ilmu di SDN Pengampon 3</p>
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

    <!-- Kolom Input (Desain Tetap Sama Persis + Fitur Interaktif) -->
    <div class="p-3 bg-white border-t border-gray-100 relative">
        
        <!-- Emoji Picker Panel (Pop-up Kecil) -->
        <div id="emoji-panel" class="hidden absolute bottom-24 left-4 z-50 bg-white border border-gray-200 rounded-2xl shadow-xl p-2.5 flex items-center gap-2 select-none animate-fade-in">
            <button type="button" onclick="sisipkanEmoji('😊')" class="text-xl hover:scale-125 transition transform p-1">😊</button>
            <button type="button" onclick="sisipkanEmoji('😂')" class="text-xl hover:scale-125 transition transform p-1">😂</button>
            <button type="button" onclick="sisipkanEmoji('👍')" class="text-xl hover:scale-125 transition transform p-1">👍</button>
            <button type="button" onclick="sisipkanEmoji('📚')" class="text-xl hover:scale-125 transition transform p-1">📚</button>
            <button type="button" onclick="sisipkanEmoji('❤️')" class="text-xl hover:scale-125 transition transform p-1">❤️</button>
            <button type="button" onclick="sisipkanEmoji('✨')" class="text-xl hover:scale-125 transition transform p-1">✨</button>
            <button type="button" onclick="sisipkanEmoji('📖')" class="text-xl hover:scale-125 transition transform p-1">📖</button>
        </div>

        <!-- Preview Gambar Terlampir -->
        <div id="image-preview-container" class="hidden mb-2 relative inline-block">
            <img id="image-preview" src="" alt="Preview" class="w-16 h-16 object-cover rounded-xl border-2 border-blue-400 shadow-sm">
            <button type="button" onclick="hapusGambarTerlampir()" class="absolute -top-1.5 -right-1.5 bg-red-500 hover:bg-red-600 text-white rounded-full w-5 h-5 flex items-center justify-center text-[10px] shadow">
                <i class="fas fa-times"></i>
            </button>
        </div>

        <!-- Hidden File Input untuk Gambar -->
        <input type="file" id="chat-file-input" accept="image/*" class="hidden" onchange="handleImageSelected(event)">

        <div class="border border-gray-200 rounded-2xl p-2.5 focus-within:border-blue-500 focus-within:ring-1 focus-within:ring-blue-500 transition bg-white">
            <input type="text" id="chat-input" placeholder="Tanya PustakaAI di sini..." class="w-full text-xs text-gray-700 outline-none bg-transparent" onkeydown="handleInputEnter(event)">
            <div class="flex items-center justify-between pt-2 mt-1 border-t border-gray-50 text-gray-400">
                <div class="flex items-center gap-3 text-sm">
                    <!-- Ikon Klip Kertas (Lampiran) -->
                    <button type="button" onclick="bukaFilePicker()" title="Lampirkan Gambar" class="hover:text-blue-600 transition">
                        <i class="fas fa-paperclip"></i>
                    </button>
                    <!-- Ikon Emoji -->
                    <button type="button" onclick="toggleEmojiPanel()" title="Pilih Emoji" class="hover:text-amber-500 transition">
                        <i class="far fa-smile"></i>
                    </button>
                    <!-- Ikon Mikrofon (Speech-to-Text) -->
                    <button type="button" id="mic-btn" onclick="toggleVoiceRecording()" title="Bicara (Suara ke Teks)" class="hover:text-red-500 transition">
                        <i id="mic-icon" class="fas fa-microphone"></i>
                    </button>
                </div>
                <button type="button" onclick="kirimPesan()" class="text-blue-600 hover:text-blue-800 p-1 transition transform active:scale-95" title="Kirim">
                    <i class="fas fa-paper-plane text-xs"></i>
                </button>
            </div>
        </div>
        <div class="text-center mt-1.5 text-[9px] text-gray-400">
            ⚡ Intelligent Library Assistant
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

    // Variabel lampiran gambar
    let selectedImageBase64 = null;
    let selectedImageMime = null;

    // 1. FITUR LAMPIRAN GAMBAR (Klip Kertas)
    function bukaFilePicker() {
        document.getElementById('chat-file-input').click();
    }

    function handleImageSelected(event) {
        const file = event.target.files[0];
        if (!file) return;

        if (!file.type.startsWith('image/')) {
            alert('Hanya file gambar yang diperbolehkan.');
            return;
        }

        selectedImageMime = file.type;
        const reader = new FileReader();
        reader.onload = function(e) {
            selectedImageBase64 = e.target.result;
            const previewContainer = document.getElementById('image-preview-container');
            const previewImg = document.getElementById('image-preview');
            previewImg.src = selectedImageBase64;
            previewContainer.classList.remove('hidden');
        };
        reader.readAsDataURL(file);
    }

    function hapusGambarTerlampir() {
        selectedImageBase64 = null;
        selectedImageMime = null;
        document.getElementById('chat-file-input').value = '';
        document.getElementById('image-preview-container').classList.add('hidden');
    }

    // 2. FITUR EMOJI PICKER (Senyum)
    function toggleEmojiPanel() {
        const panel = document.getElementById('emoji-panel');
        panel.classList.toggle('hidden');
    }

    function sisipkanEmoji(emoji) {
        const input = document.getElementById('chat-input');
        input.value += emoji;
        input.focus();
        document.getElementById('emoji-panel').classList.add('hidden');
    }

    // 3. FITUR SPEECH TO TEXT (Mikrofon)
    let isRecording = false;
    let recognition = null;

    function initSpeechRecognition() {
        const SpeechRecognition = window.SpeechRecognition || window.webkitSpeechRecognition;
        if (!SpeechRecognition) {
            alert('Browser Anda belum mendukung fitur Web Speech API. Silakan gunakan Google Chrome atau Microsoft Edge terbaru.');
            return null;
        }

        const recognizer = new SpeechRecognition();
        recognizer.lang = 'id-ID';
        recognizer.interimResults = false;
        recognizer.maxAlternatives = 1;

        recognizer.onstart = function() {
            isRecording = true;
            updateMicUI(true);
        };

        recognizer.onresult = function(event) {
            const transcript = event.results[0][0].transcript;
            const input = document.getElementById('chat-input');
            input.value = (input.value ? input.value + ' ' : '') + transcript;
            input.focus();
        };

        recognizer.onerror = function(event) {
            console.warn('Speech recognition error:', event.error);
            isRecording = false;
            updateMicUI(false);
        };

        recognizer.onend = function() {
            isRecording = false;
            updateMicUI(false);
        };

        return recognizer;
    }

    function toggleVoiceRecording() {
        if (!recognition) {
            recognition = initSpeechRecognition();
        }
        if (!recognition) return;

        if (!isRecording) {
            try {
                recognition.start();
            } catch (err) {
                console.warn('Recognition start error:', err);
            }
        } else {
            recognition.stop();
        }
    }

    function updateMicUI(recording) {
        const micIcon = document.getElementById('mic-icon');
        const micBtn = document.getElementById('mic-btn');
        if (recording) {
            micIcon.className = "fas fa-microphone text-red-500 animate-pulse text-base";
            micBtn.title = "Sedang mendengarkan suara Anda...";
        } else {
            micIcon.className = "fas fa-microphone";
            micBtn.title = "Bicara (Suara ke Teks)";
        }
    }

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
        hapusGambarTerlampir();
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
        const hasImage = !!selectedImageBase64;

        if (!pesan && !hasImage) return;

        // Tutup emoji panel jika terbuka
        document.getElementById('emoji-panel').classList.add('hidden');
        // Sembunyikan welcome screen saat percakapan dimulai
        document.getElementById('welcome-section').classList.add('hidden');

        // Simpan referensi gambar untuk payload & UI sebelum di-reset
        const imgToSend = selectedImageBase64;
        const mimeToSend = selectedImageMime;

        // 1. Tampilkan Bubble Pesan User di Kanan (Termasuk Gambar jika ada)
        tampilkanBubbleUser(pesan, imgToSend);
        input.value = '';
        hapusGambarTerlampir();

        // 2. Tampilkan Indikator Loading ("Mengetik...")
        const loader = document.getElementById('chat-loading');
        loader.classList.remove('hidden');
        scrollKeBawah();

        try {
            // 3. Fetch ke Backend Node.js Google Gemini API (Mendukung Teks + Gambar Vision)
            const res = await fetch(CHAT_BACKEND_URL, {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    pesan: pesan,
                    imageBase64: imgToSend,
                    imageMime: mimeToSend,
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

    function tampilkanBubbleUser(teks, imageBase64) {
        const container = document.getElementById('chat-messages');
        const bubble = document.createElement('div');
        bubble.className = "flex justify-end";

        let content = '';
        if (imageBase64) {
            content += `<div class="mb-1.5"><img src="${imageBase64}" class="w-36 h-auto rounded-xl border border-white/30 shadow-xs"></div>`;
        }
        if (teks) {
            content += `<div>${escapeHtml(teks)}</div>`;
        }

        bubble.innerHTML = `
            <div class="bg-blue-600 text-white text-xs px-4 py-2.5 rounded-2xl rounded-tr-xs max-w-[80%] shadow-xs break-words">
                ${content}
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
