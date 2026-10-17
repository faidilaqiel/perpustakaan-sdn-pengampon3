<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Buku - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- HEADER --}}
    <header class="bg-green-600 text-white p-4 shadow-lg">
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
                <p class="font-semibold">Administrator</p>
                <p class="text-sm">Petugas Perpustakaan</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- KONTEN --}}
    <div class="container mx-auto p-6">

        <a href="{{ route('petugas.dashboard') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Dashboard
        </a>

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

        {{-- Judul + Tombol Tambah --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">📚 Kelola Buku</h2>
                    <p class="text-gray-600">Total <strong>{{ $totalBuku }}</strong> buku di perpustakaan.</p>
                </div>
                <a href="{{ route('petugas.buku.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2">
                    ➕ Tambah Buku Baru
                </a>
            </div>

            <form method="GET" action="{{ route('petugas.kelolaBuku') }}" class="mt-4 flex gap-2">
                <input type="text" name="q" value="{{ $keyword }}"
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500"
                       placeholder="Cari judul, penulis, atau kode buku...">
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg">
                    🔍 Cari
                </button>
                @if($keyword)
                    <a href="{{ route('petugas.kelolaBuku') }}"
                       class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Buku --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            @if($books->isEmpty())
                <div class="p-10 text-center text-gray-500">
                    <p class="text-4xl mb-3">📭</p>
                    <p>Belum ada buku. Silakan tambah buku baru.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">No</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Cover</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Kode</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Judul & Penulis</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Kategori</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Stok</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($books as $index => $book)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        {{ $books->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($book->cover)
                                            <img src="{{ asset('covers/' . $book->cover) }}"
                                                 alt="Cover"
                                                 class="w-12 h-16 object-cover rounded shadow">
                                        @else
                                            <div class="w-12 h-16 bg-blue-100 rounded flex items-center justify-center text-2xl">
                                                📖
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm font-mono">{{ $book->code }}</td>
                                    <td class="px-4 py-3 text-sm">
                                        <strong>{{ $book->title }}</strong><br>
                                        <span class="text-xs text-gray-500">{{ $book->author }} ({{ $book->year }})</span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                            {{ $book->category->name ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($book->stock_available > 0)
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                ✅ {{ $book->stock_available }} / {{ $book->stock_total }}
                                            </span>
                                        @else
                                            <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">
                                                ❌ 0 / {{ $book->stock_total }}
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-1">
                                            <a href="{{ route('petugas.buku.edit', $book->id) }}"
                                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                                ✏️ Edit
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('petugas.buku.destroy', $book->id) }}"
                                                  onsubmit="return confirm('Yakin hapus buku {{ $book->title }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                                    🗑️ Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t">
                    {{ $books->appends(['q' => $keyword])->links() }}
                </div>
            @endif
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>