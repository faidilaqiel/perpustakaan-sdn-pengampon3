@extends('layouts.siswa')

@section('title', $book->title . ' - Perpustakaan Digital')

@section('content')
    <div class="max-w-4xl mx-auto">
        
        {{-- Tombol Kembali --}}
        <a href="{{ route('siswa.katalog') }}" class="inline-flex items-center gap-2 mb-6 text-green-600 hover:text-green-800 font-semibold text-sm transition">
            <i class="fas fa-arrow-left"></i> Kembali ke Katalog
        </a>

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

        <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                
                {{-- Kiri: Cover Buku --}}
                <div class="md:col-span-1 bg-gray-50 p-6 flex items-center justify-center border-b md:border-b-0 md:border-r border-gray-100">
                    <div class="w-full max-w-[200px] aspect-[3/4] rounded-lg overflow-hidden shadow-lg border border-gray-200 bg-white">
                        @if($book->cover && file_exists(public_path('covers/' . $book->cover)))
                            <img src="{{ asset('covers/' . $book->cover) }}" alt="Cover" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-green-300">
                                <i class="fas fa-book text-6xl mb-2"></i>
                                <span class="text-xs font-semibold">No Cover</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Kanan: Info Buku --}}
                <div class="md:col-span-2 p-6 md:p-8 flex flex-col justify-between">
                    <div>
                        <span class="text-xs font-mono text-gray-500 bg-gray-100 px-2 py-1 rounded">{{ $book->code }}</span>
                        <h1 class="text-2xl md:text-3xl font-bold text-gray-800 mt-3 mb-4 leading-tight">{{ $book->title }}</h1>
                        
                        <div class="space-y-3 text-sm text-gray-700">
                            <div class="flex">
                                <span class="w-32 text-gray-500">Penulis</span>
                                <span class="font-semibold flex-1">: {{ $book->author }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-gray-500">Penerbit</span>
                                <span class="font-semibold flex-1">: {{ $book->publisher }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-gray-500">Tahun Terbit</span>
                                <span class="font-semibold flex-1">: {{ $book->year }}</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-gray-500">Kategori</span>
                                <span class="flex-1">: <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold">{{ $book->category->name ?? '-' }}</span></span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-gray-500">Total Stok</span>
                                <span class="font-semibold flex-1">: {{ $book->stock_total }} buku</span>
                            </div>
                            <div class="flex">
                                <span class="w-32 text-gray-500">Tersedia</span>
                                <span class="flex-1">
                                    : @if($book->stock_available > 0)
                                        <span class="bg-green-100 text-green-700 px-2.5 py-1 rounded-full text-xs font-semibold">✅ {{ $book->stock_available }} buku</span>
                                    @else
                                        <span class="bg-red-100 text-red-700 px-2.5 py-1 rounded-full text-xs font-semibold">❌ Dipinjam semua</span>
                                    @endif
                                </span>
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Pinjam --}}
                    <div class="mt-8 pt-6 border-t border-gray-100">
                        @if($book->stock_available > 0)
                            <form method="POST" action="{{ route('siswa.pinjam', $book->id) }}">
                                @csrf
                                <button type="submit" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold px-8 py-3 rounded-lg transition shadow-md flex items-center justify-center gap-2">
                                    <i class="fas fa-book"></i> Pinjam Buku Ini
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full md:w-auto bg-gray-300 text-gray-500 font-bold px-8 py-3 rounded-lg cursor-not-allowed flex items-center justify-center gap-2">
                                <i class="fas fa-times-circle"></i> Stok Habis
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection