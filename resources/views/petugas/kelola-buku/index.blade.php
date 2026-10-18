@extends('layouts.app')

@section('title', 'Kelola Buku - Perpustakaan Digital')

@section('content')
    {{-- Header Halaman --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Kelola Buku</h2>
            <p class="text-sm text-gray-500 mt-1">Total {{ $books->count() }} buku di perpustakaan.</p>
        </div>
        <a href="{{ route('petugas.buku.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2 shadow-md transition">
            <i class="fas fa-plus"></i> Tambah Buku Baru
        </a>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif

    {{-- Konten Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        
        {{-- Form Pencarian --}}
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <form method="GET" action="{{ route('petugas.kelolaBuku') }}" class="flex gap-2">
                <input type="text" name="q" value="{{ request('q') }}" 
                       class="flex-1 px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm"
                       placeholder="Cari judul, penulis, atau kode buku...">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg text-sm font-semibold">
                    Cari
                </button>
            </form>
        </div>

        {{-- Tabel Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="p-4 font-semibold">No</th>
                        <th class="p-4 font-semibold">Cover</th>
                        <th class="p-4 font-semibold">Kode</th>
                        <th class="p-4 font-semibold">Judul & Penulis</th>
                        <th class="p-4 font-semibold">Kategori</th>
                        <th class="p-4 font-semibold">Stok</th>
                        <th class="p-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $index => $book)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                            <td class="p-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="p-4">
                                @if($book->cover)
                                    <img src="{{ asset('covers/' . $book->cover) }}" alt="Cover" class="w-12 h-16 object-cover rounded shadow-sm">
                                @else
                                    <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-400">
                                        <i class="fas fa-book"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="p-4 font-mono text-gray-600">{{ $book->code }}</td>
                            <td class="p-4">
                                <strong class="text-gray-800 block">{{ $book->title }}</strong>
                                <span class="text-xs text-gray-500">{{ $book->author }} ({{ $book->year }})</span>
                            </td>
                            <td class="p-4">
                                <span class="bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-semibold">
                                    {{ $book->category->name ?? '-' }}
                                </span>
                            </td>
                            <td class="p-4">
                                @if($book->stock_available > 0)
                                    <span class="text-green-600 font-bold">✅ {{ $book->stock_available }} / {{ $book->stock_total }}</span>
                                @else
                                    <span class="text-red-600 font-bold">❌ Habis / {{ $book->stock_total }}</span>
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('petugas.buku.edit', $book->id) }}" class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded flex items-center justify-center hover:bg-yellow-200 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('petugas.buku.destroy', $book->id) }}" onsubmit="return confirm('Hapus buku ini?')">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="w-8 h-8 bg-red-100 text-red-600 rounded flex items-center justify-center hover:bg-red-200 transition" title="Hapus">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="p-8 text-center text-gray-500">Belum ada data buku.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Tampilan HP (Kartu) --}}
        <div class="block md:hidden p-4 space-y-4">
            @forelse($books as $book)
                <div class="border border-gray-200 rounded-lg p-3 shadow-sm flex gap-3">
                    @if($book->cover)
                        <img src="{{ asset('covers/' . $book->cover) }}" class="w-20 h-28 object-cover rounded">
                    @else
                        <div class="w-20 h-28 bg-gray-100 rounded flex items-center justify-center text-gray-400"><i class="fas fa-book text-2xl"></i></div>
                    @endif
                    <div class="flex-1">
                        <span class="text-xs font-mono text-gray-500">{{ $book->code }}</span>
                        <h4 class="font-bold text-gray-800 leading-tight">{{ $book->title }}</h4>
                        <p class="text-xs text-gray-500 mt-1">{{ $book->author }}</p>
                        <div class="mt-2 flex flex-wrap gap-1">
                            <span class="bg-blue-100 text-blue-700 px-2 py-0.5 rounded text-[10px]">{{ $book->category->name ?? '-' }}</span>
                            <span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-[10px]">Stok: {{ $book->stock_available }}</span>
                        </div>
                        <div class="mt-3 flex gap-2">
                            <a href="{{ route('petugas.buku.edit', $book->id) }}" class="flex-1 bg-yellow-500 text-white text-center py-1.5 rounded text-xs font-semibold">Edit</a>
                            <form method="POST" action="{{ route('petugas.buku.destroy', $book->id) }}" onsubmit="return confirm('Hapus?')" class="flex-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full bg-red-500 text-white py-1.5 rounded text-xs font-semibold">Hapus</button>
                            </form>
                        </div>
                    </div>
                </div>
            @empty
                <div class="text-center text-gray-500 py-6">Belum ada data buku.</div>
            @endforelse
        </div>
    </div>
@endsection