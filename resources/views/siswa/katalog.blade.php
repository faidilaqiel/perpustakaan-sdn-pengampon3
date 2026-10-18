<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Buku - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    <header class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-full p-1">
                <div>
                    <h1 class="text-xl font-bold">Perpustakaan Digital</h1>
                    <p class="text-sm">SDN Pengampon 3</p>
                </div>
            </div>
            <div class="text-right">
                <p class="font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-sm">Role: {{ Auth::user()->role }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    <div class="container mx-auto p-6">

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">📚 Katalog Buku</h2>
            <p class="text-gray-600 mb-4">Cari dan pinjam buku favoritmu!</p>

            <form method="GET" action="{{ route('siswa.katalog') }}" class="flex gap-2" id="search-form">
                <input type="text" name="q" id="search-input" value="{{ $keyword }}"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Cari judul buku, penulis, atau kode buku...">
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-6 py-2 rounded-lg">
                    🔍 Cari
                </button>
            </form>
        </div>

        {{-- Container tempat daftar buku dirender oleh JS --}}
        <div id="book-container" class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Konten awal (opsional, akan langsung ditimpa JS) --}}
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

    {{-- ===== SCRIPT AUTO-REFRESH KATALOG & NOTIFIKASI ===== --}}
    <script>
        // --- 1. FUNGSI RENDER KATALOG ---
        function renderBooks(books) {
            const container = document.getElementById('book-container');
            container.innerHTML = ''; // Kosongkan kontainer

            if (books.length === 0) {
                container.innerHTML = `<div class="col-span-full bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded">Tidak ada buku yang ditemukan.</div>`;
                return;
            }

            books.forEach(book => {
                let coverHtml = '';
                if (book.cover) {
                    // Cek apakah cover berupa link http atau nama file lokal
                    let src = book.cover.startsWith('http') ? book.cover : `/covers/${book.cover}`;
                    coverHtml = `<img src="${src}" class="w-full h-full object-cover">`;
                } else {
                    coverHtml = `<span class="text-6xl">📖</span>`;
                }

                let stockBadge = '';
                if (book.stock_available > 0) {
                    stockBadge = `<span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded">✅ Tersedia (${book.stock_available})</span>`;
                } else {
                    stockBadge = `<span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded">❌ Dipinjam Semua</span>`;
                }

                container.innerHTML += `
                    <a href="/siswa/buku/${book.id}" class="block">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer flex flex-col h-full">
                            <div class="h-48 bg-gradient-to-br from-blue-100 to-blue-300 flex items-center justify-center relative">
                                ${coverHtml}
                            </div>
                            <div class="p-4 flex-1 flex flex-col">
                                <p class="text-xs text-gray-500">${book.code}</p>
                                <h3 class="font-bold text-gray-800 mb-1">${book.title}</h3>
                                <p class="text-sm text-gray-600 mb-2">${book.author}</p>
                                <p class="text-xs text-blue-600 mb-3">${book.category}</p>
                                <div class="mt-auto">${stockBadge}</div>
                            </div>
                        </div>
                    </a>
                `;
            });
        }

        // --- 2. FUNGSI AMBIL DATA KATALOG ---
        function fetchKatalog() {
            const keyword = document.getElementById('search-input').value;
            fetch(`{{ route('siswa.api.katalog') }}?q=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    renderBooks(data.books);
                })
                .catch(error => console.error('Error:', error));
        }

        // --- 3. FUNGSI NOTIFIKASI ---
        let lastNotif = "{{ Auth::user()->notifikasi ?? '' }}"; 

        function cekNotifikasiBaru() {
            fetch("{{ route('siswa.cekNotif') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.notifikasi && data.notifikasi !== lastNotif) {
                        lastNotif = data.notifikasi; 
                        let notif = data.notifikasi.split('|');
                        let tipe = notif[0]; 
                        let pesan = notif[1];

                        Swal.fire({
                            icon: tipe === "disetujui" ? "success" : "error",
                            title: tipe === "disetujui" ? "Peminjaman Disetujui!" : "Peminjaman Ditolak",
                            text: pesan,
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: tipe === "disetujui" ? "#16a34a" : "#dc2626",
                            allowOutsideClick: false
                        }).then((result) => {
                            if (result.isConfirmed) {
                                fetch("{{ route('siswa.hapusNotif') }}", {
                                    method: 'POST',
                                    headers: {
                                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                        'Content-Type': 'application/json'
                                    }
                                }).then(() => {
                                    lastNotif = ""; 
                                });
                            }
                        });
                    }
                })
                .catch(error => console.error('Error cek notifikasi:', error));
        }

        // --- 4. INISIALISASI & INTERVAL ---
        // Jalankan saat halaman pertama dimuat
        document.addEventListener('DOMContentLoaded', () => {
            fetchKatalog(); // Muat katalog awal
            cekNotifikasiBaru(); // Cek notif awal
        });

        // Otomatis perbarui katalog setiap 5 detik
        setInterval(fetchKatalog, 5000); 
        
        // Otomatis cek notifikasi setiap 5 detik
        setInterval(cekNotifikasiBaru, 5000); 

        // Handle submit form pencarian agar tidak reload halaman
        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            fetchKatalog();
        });
    </script>

</body>
</html>