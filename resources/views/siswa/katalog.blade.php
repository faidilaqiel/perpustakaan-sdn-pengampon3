@extends('layouts.siswa')

@section('title', 'Katalog Buku - Perpustakaan Digital')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-end gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Katalog Buku</h2>
            <p class="text-gray-500 text-sm mt-1">Temukan dan pinjam buku favoritmu.</p>
        </div>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Form Pencarian & Filter --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
        <form method="GET" action="{{ route('siswa.katalog') }}" class="flex flex-col md:flex-row gap-2 mb-4" id="search-form">
            <div class="relative flex-1">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="fas fa-search text-gray-400"></i>
                </div>
                <input type="text" name="q" id="search-input" value="{{ $keyword }}"
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                       placeholder="Cari judul buku, penulis, atau kode...">
            </div>
            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold text-sm transition shadow-md">
                Cari
            </button>
        </form>

        {{-- Filter Kategori (Tombol Pil) --}}
        <div class="flex items-center gap-2 overflow-x-auto pb-2 scrollbar-hide">
            <a href="{{ route('siswa.katalog', ['q' => $keyword]) }}" 
               class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition border {{ !$categoryId ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                Semua
            </a>
            @foreach($categories as $cat)
                <a href="{{ route('siswa.katalog', ['q' => $keyword, 'category' => $cat->id]) }}" 
                   class="px-4 py-1.5 rounded-full text-sm font-semibold whitespace-nowrap transition border {{ $categoryId == $cat->id ? 'bg-green-600 text-white border-green-600' : 'bg-white text-gray-600 border-gray-300 hover:bg-gray-50' }}">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    {{-- Container Buku --}}
    <div id="book-container" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-3 md:gap-4">
        {{-- Diisi oleh JavaScript --}}
    </div>

    {{-- Script AJAX & Notifikasi --}}
    <script>
        function renderBooks(books) {
            const container = document.getElementById('book-container');
            container.innerHTML = ''; 

            if (books.length === 0) {
                container.innerHTML = `<div class="col-span-full bg-white border border-gray-200 text-gray-500 p-8 rounded-xl text-center shadow-sm">Tidak ada buku yang ditemukan.</div>`;
                return;
            }

            books.forEach(book => {
                let coverHtml = '';
                if (book.cover && book.cover !== 'null') {
                    let src = book.cover.startsWith('http') ? book.cover : `/covers/${book.cover}`;
                    coverHtml = `<img src="${src}" onerror="this.onerror=null; this.parentElement.innerHTML='<div class=\\'w-full h-full flex items-center justify-center text-5xl text-green-200 bg-green-50\\'><i class=\\'fas fa-book\\'></i></div>';" class="w-full h-full object-cover group-hover:scale-105 transition duration-300">`;
                } else {
                    coverHtml = `<div class="w-full h-full flex items-center justify-center text-5xl text-green-200 bg-green-50"><i class="fas fa-book"></i></div>`;
                }

                let stockBadge = '';
                if (book.stock_available > 0) {
                    stockBadge = `<span class="inline-block bg-green-100 text-green-700 text-[10px] md:text-xs px-2 py-1 rounded-md font-semibold mt-2">✅ Tersedia (${book.stock_available})</span>`;
                } else {
                    stockBadge = `<span class="inline-block bg-red-100 text-red-700 text-[10px] md:text-xs px-2 py-1 rounded-md font-semibold mt-2">❌ Habis</span>`;
                }

                container.innerHTML += `
                    <a href="/siswa/buku/${book.id}" class="block h-full group">
                        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition flex flex-col h-full">
                            <div class="aspect-[3/4] bg-gray-100 relative overflow-hidden">
                                ${coverHtml}
                            </div>
                            <div class="p-3 md:p-4 flex-1 flex flex-col">
                                <p class="text-[10px] text-gray-400 font-mono mb-1">${book.code}</p>
                                <h3 class="font-bold text-gray-800 text-sm md:text-base leading-tight line-clamp-2">${book.title}</h3>
                                <p class="text-xs text-gray-500 mt-1 mb-2 truncate">${book.author}</p>
                                <div class="mt-auto">
                                    <span class="text-[10px] bg-green-50 text-green-600 px-2 py-0.5 rounded border border-green-100">${book.category}</span>
                                    <br>
                                    ${stockBadge}
                                </div>
                            </div>
                        </div>
                    </a>
                `;
            });
        }

        function fetchKatalog() {
            const keyword = document.getElementById('search-input').value;
            // Ambil category dari URL saat ini
            const urlParams = new URLSearchParams(window.location.search);
            const category = urlParams.get('category') || '';

            fetch(`{{ route('siswa.api.katalog') }}?q=${encodeURIComponent(keyword)}&category=${category}`)
                .then(response => response.json())
                .then(data => { renderBooks(data.books); })
                .catch(error => console.error('Error:', error));
        }

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
                                }).then(() => { lastNotif = ""; });
                            }
                        });
                    }
                })
                .catch(error => console.error('Error cek notifikasi:', error));
        }

        document.addEventListener('DOMContentLoaded', () => {
            fetchKatalog(); 
            cekNotifikasiBaru(); 
        });

        // Jika ada perubahan pada URL (klik kategori), reload katalog tanpa refresh
        document.querySelectorAll('.flex-wrap a').forEach(link => {
            link.addEventListener('click', function(e) {
                e.preventDefault();
                const url = this.getAttribute('href');
                window.history.pushState({}, '', url);
                fetchKatalog();
                
                // Update class active tombol
                document.querySelectorAll('.flex-wrap a').forEach(l => {
                    l.classList.remove('bg-green-600', 'text-white', 'border-green-600');
                    l.classList.add('bg-white', 'text-gray-600', 'border-gray-300', 'hover:bg-gray-50');
                });
                this.classList.remove('bg-white', 'text-gray-600', 'border-gray-300', 'hover:bg-gray-50');
                this.classList.add('bg-green-600', 'text-white', 'border-green-600');
            });
        });
    </script>
@endsection