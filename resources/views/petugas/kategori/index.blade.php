@extends('layouts.app')

@section('title', 'Kelola Kategori - Perpustakaan Digital')

@section('content')
    {{-- Header Halaman --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Kategori Buku</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola daftar kategori perpustakaan.</p>
        </div>
        <a href="{{ route('petugas.kategori.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2 shadow-md transition">
            <i class="fas fa-plus"></i> Tambah Kategori
        </a>
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

    {{-- Konten Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="p-4 font-semibold w-16">No</th>
                        <th class="p-4 font-semibold">Nama Kategori</th>
                        <th class="p-4 font-semibold">Jumlah Buku</th>
                        <th class="p-4 font-semibold text-center w-40">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $index => $cat)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                            <td class="p-4 text-gray-500">{{ $index + 1 }}</td>
                            <td class="p-4 font-bold text-gray-800">{{ $cat->name }}</td>
                            <td class="p-4 text-gray-600">
                                <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full text-xs font-semibold">
                                    {{ $cat->books_count }} Buku
                                </span>
                            </td>
                            <td class="p-4">
                                <div class="flex justify-center gap-2">
                                    <a href="{{ route('petugas.kategori.edit', $cat->id) }}" class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded flex items-center justify-center hover:bg-yellow-200 transition" title="Edit">
                                        <i class="fas fa-edit"></i>
                                    </a>
                                    <form method="POST" action="{{ route('petugas.kategori.destroy', $cat->id) }}" onsubmit="return confirm('Yakin hapus kategori ini?')">
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
                            <td colspan="4" class="p-8 text-center text-gray-500">Belum ada data kategori.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection