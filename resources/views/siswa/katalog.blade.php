<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Memastikan tampilan skala 1:1 di semua HP -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Katalog Buku - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <style>
        /* Mencegah input text memperlebar layar di iOS */
        input[type="text"], input[type="search"] {
            -webkit-appearance: none;
            -moz-appearance: none;
            appearance: none;
            font-size: 16px !important; /* Mencegah Safari auto-zoom */
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen w-full overflow-x-hidden">

    <header class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center gap-3 text-center md:text-left">
            <div class="flex flex-col md:flex-row items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-full p-1">
                <div>
                    <h1 class="text-xl font-bold">Perpustakaan Digital</h1>
                    <p class="text-sm">SDN Pengampon 3</p>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <p class="font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-sm">Role: {{ Auth::user()->role }}</p>
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

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- HEADER PENCARIAN --}}
        <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-2">📚 Katalog Buku</h2>
            <p class="text-gray-600 mb-4 text-sm">Cari dan pinjam buku favoritmu!</p>

            {{-- Form Pencarian Responsif (Tumpuk di HP, Sejajar di Laptop) --}}
            <form method="GET" action="{{ route('siswa.katalog') }}" class="flex flex-col md:flex-row gap-2" id="search-form">
                <input type="text" name="q" id="search-input" value="{{ $keyword }}"
                       class="w-full md:flex-1 px-4 py-3 md:py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500 text-sm"
                       placeholder="Cari judul buku, penulis, atau kode buku...">
                <button type="submit" class="w-full md:w-auto bg-blue-600 hover:bg-blue-700 text-white px-6 py-3 md:py-2 rounded-lg font-semibold text-sm">
                    🔍 Cari
                </button>
            </form>
        </div>

        {{-- DAFTAR BUKU --}}
        <div id="book-container" class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            {{-- Diisi oleh JavaScript --}}
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4 mt-6">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

    <script>
        // --- RENDER KATALOG ---
        function renderBooks(books) {
            const container = document.getElementById('book-container');
            container.innerHTML = ''; 

            if (books.length === 0) {
                container.innerHTML = `<div class="col-span-full bg-yellow-100 border border-yellow-400 text-yellow-800 px-4 py-3 rounded text-sm">Tidak ada buku yang ditemukan.</div>`;
                return;
            }

            books.forEach(book => {
                let coverHtml = '';
                if (book.cover) {
                    let src = book.cover.startsWith('http') ? book.cover : `/covers/${book.cover}`;
                    coverHtml = `<img src="${src}" class="w-full h-full object-cover">`;
                } else {
                    coverHtml = `<span class="text-6xl">📖</span>`;
                }

                let stockBadge = '';
                if (book.stock_available > 0) {
                    stockBadge = `<span class="inline-block bg-green-100 text-green-800 text-xs px-2 py-1 rounded font-semibold">✅ Tersedia (${book.stock_available})</span>`;
                } else {
                    stockBadge = `<span class="inline-block bg-red-100 text-red-800 text-xs px-2 py-1 rounded font-semibold">❌ Dipinjam Semua</span>`;
                }

                container.innerHTML += `
                    <a href="/siswa/buku/${book.id}" class="block h-full">
                        <div class="bg-white rounded-lg shadow-md overflow-hidden hover:shadow-xl transition cursor-pointer flex flex-col h-full">
                            <div class="h-48 bg-gradient-to-br from-blue-100 to-blue-300 flex items-center justify-center relative">
                                ${coverHtml}
                            </div>
                            <div class="p-4 flex-1 flex flex-col">
                                <p class="text-xs text-gray-500">${book.code}</p>
                                <h3 class="font-bold text-gray-800 mb-1 text-base">${book.title}</h3>
                                <p class="text-sm text-gray-600 mb-2">${book.author}</p>
                                <p class="text-xs text-blue-600 mb-3">${book.category}</p>
                                <div class="mt-auto">${stockBadge}</div>
                            </div>
                        </div>
                    </a>
                `;
            });
        }

        // --- AMBIL DATA KATALOG ---
        function fetchKatalog() {
            const keyword = document.getElementById('search-input').value;
            fetch(`{{ route('siswa.api.katalog') }}?q=${encodeURIComponent(keyword)}`)
                .then(response => response.json())
                .then(data => {
                    renderBooks(data.books);
                })
                .catch(error => console.error('Error:', error));
        }

        // --- NOTIFIKASI AJAX (DISETUJUI / DITOLAK) ---
        let lastNotif = "{{ Auth::user()->notifikasi ?? '' }}"; 

        function cekNotifikasiBaru() {
            fetch("{{ route('siswa.cekNotif') }}")
                .then(response => response.json())
                .then(data => {
                    if (data.notifikasi && data.notifikasi !== lastNotif) {
                        lastNotif = data.notifikasi; 
                        let notif = data.notifikasi.split('|');
                        let tipe = notif[0]; 
                        
                        let pesan = notif[1].replace(/&quot;/g, '"');

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

        // --- INISIALISASI ---
        document.addEventListener('DOMContentLoaded', () => {
            fetchKatalog(); 
            cekNotifikasiBaru(); 
        });

        setInterval(fetchKatalog, 5000); 
        setInterval(cekNotifikasiBaru, 5000); 

        document.getElementById('search-form').addEventListener('submit', function(e) {
            e.preventDefault();
            fetchKatalog();
        });
    </script>

</body>
</html>