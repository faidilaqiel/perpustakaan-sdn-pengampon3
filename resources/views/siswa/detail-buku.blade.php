<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $book->title }} - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- ===== HEADER ===== --}}
    <header class="bg-blue-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}"
                     alt="Logo SDN Pengampon 3"
                     class="w-12 h-12 object-contain bg-white rounded-full p-1">
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

    {{-- ===== KONTEN ===== --}}
    <div class="container mx-auto p-6">

        {{-- Tombol Kembali --}}
        <a href="{{ route('siswa.katalog') }}"
           class="inline-block mb-4 text-blue-600 hover:text-blue-800 font-semibold">
            ← Kembali ke Katalog
        </a>

        {{-- Pesan Sukses --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif

        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        <div class="bg-white rounded-lg shadow-lg overflow-hidden">

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 p-6">

                {{-- ===== KIRI: COVER BUKU ===== --}}
                <div class="md:col-span-1">
                    <div class="h-80 bg-gradient-to-br from-blue-400 to-blue-600 rounded-lg flex items-center justify-center">
                        <span class="text-9xl">📖</span>
                    </div>
                </div>

                {{-- ===== KANAN: INFO BUKU ===== --}}
                <div class="md:col-span-2">
                    <p class="text-sm text-gray-500 mb-1">Kode Buku: <strong>{{ $book->code }}</strong></p>
                    <h1 class="text-3xl font-bold text-gray-800 mb-3">{{ $book->title }}</h1>

                    {{-- Info Detail --}}
                    <div class="space-y-2 mb-6">
                        <div class="flex">
                            <span class="w-32 text-gray-600">Penulis</span>
                            <span class="text-gray-800">: <strong>{{ $book->author }}</strong></span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Penerbit</span>
                            <span class="text-gray-800">: {{ $book->publisher }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Tahun Terbit</span>
                            <span class="text-gray-800">: {{ $book->year }}</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Kategori</span>
                            <span class="text-gray-800">
                                : <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-sm">
                                    {{ $book->category->name ?? '-' }}
                                </span>
                            </span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Total Stok</span>
                            <span class="text-gray-800">: {{ $book->stock_total }} buku</span>
                        </div>
                        <div class="flex">
                            <span class="w-32 text-gray-600">Tersedia</span>
                            <span class="text-gray-800">
                                : 
                                @if($book->stock_available > 0)
                                    <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-sm">
                                        ✅ {{ $book->stock_available }} buku
                                    </span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-sm">
                                        ❌ Dipinjam semua
                                    </span>
                                @endif
                            </span>
                        </div>
                    </div>

                    {{-- Tombol Pinjam --}}
                    @if($book->stock_available > 0)
                        <form method="POST" action="{{ route('siswa.pinjam', $book->id) }}">
                            @csrf
                            <button type="submit"
                                    class="bg-blue-600 hover:bg-blue-700 text-white font-bold px-8 py-3 rounded-lg transition">
                                📚 Pinjam Buku Ini
                            </button>
                        </form>
                    @else
                        <button disabled
                                class="bg-gray-400 text-white font-bold px-8 py-3 rounded-lg cursor-not-allowed">
                            ❌ Stok Habis
                        </button>
                    @endif

                </div>

            </div>

        </div>

    </div>

    {{-- ===== FOOTER ===== --}}
    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>