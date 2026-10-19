@extends('layouts.siswa')

@section('title', $book->title . ' - Perpustakaan Digital')

@section('content')
    <div class="max-w-5xl mx-auto">
        
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

        {{-- Kartu Detail Buku --}}
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-0">
                
                {{-- KIRI: Cover Buku --}}
                <div class="md:col-span-1 bg-gray-50 p-8 flex items-center justify-center border-b md:border-b-0 md:border-r border-gray-100">
                    <div class="w-full max-w-[240px] aspect-[3/4] rounded-xl overflow-hidden shadow-xl border-4 border-white bg-white">
                        @if($book->cover && file_exists(public_path('covers/' . $book->cover)))
                            <img src="{{ asset('covers/' . $book->cover) }}" alt="Cover" class="w-full h-full object-cover">
                        @elseif($book->cover)
                            <img src="{{ $book->cover }}" alt="Cover" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-green-200 bg-green-50">
                                <i class="fas fa-book text-6xl mb-2"></i>
                                <span class="text-xs font-semibold">No Cover</span>
                            </div>
                        @endif
                    </div>
                </div>

                {{-- KANAN: Info Buku --}}
                <div class="md:col-span-2 p-6 md:p-10 flex flex-col justify-between">
                    <div>
                        <div class="flex items-center gap-3 mb-3">
                            <span class="text-xs font-mono text-gray-600 bg-gray-100 px-3 py-1 rounded-md">{{ $book->code }}</span>
                            <span class="text-xs font-semibold text-green-700 bg-green-100 px-3 py-1 rounded-md">{{ $book->category->name ?? 'Tanpa Kategori' }}</span>
                        </div>
                        
                        <h1 class="text-2xl md:text-4xl font-extrabold text-gray-800 mb-2 leading-tight">{{ $book->title }}</h1>
                        <p class="text-lg text-gray-500 mb-6">Oleh: <span class="font-semibold text-gray-700">{{ $book->author }}</span></p>
                        
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 mb-8">
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-500 mb-1">Penerbit</p>
                                <p class="font-semibold text-gray-800 text-sm">{{ $book->publisher ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-500 mb-1">Tahun Terbit</p>
                                <p class="font-semibold text-gray-800 text-sm">{{ $book->year ?? '-' }}</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-500 mb-1">Total Stok</p>
                                <p class="font-semibold text-gray-800 text-sm">{{ $book->stock_total }} Buku</p>
                            </div>
                            <div class="bg-gray-50 p-3 rounded-lg border border-gray-100">
                                <p class="text-xs text-gray-500 mb-1">Tersedia</p>
                                @if($book->stock_available > 0)
                                    <p class="font-bold text-green-600 text-sm">✅ {{ $book->stock_available }} Buku</p>
                                @else
                                    <p class="font-bold text-red-600 text-sm">❌ Habis Dipinjam</p>
                                @endif
                            </div>
                        </div>
                    </div>

                    {{-- Tombol Pinjam (Sticky di HP) --}}
                    <div class="pt-6 border-t border-gray-100">
                        @if($book->stock_available > 0)
                            <form method="POST" action="{{ route('siswa.pinjam', $book->id) }}">
                                @csrf
                                <button type="submit" class="w-full md:w-auto bg-green-600 hover:bg-green-700 text-white font-bold px-10 py-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 flex items-center justify-center gap-2 text-lg">
                                    <i class="fas fa-hand-holding-heart"></i> Pinjam Buku Ini
                                </button>
                            </form>
                        @else
                            <button disabled class="w-full md:w-auto bg-gray-300 text-gray-500 font-bold px-10 py-4 rounded-xl cursor-not-allowed flex items-center justify-center gap-2 text-lg">
                                <i class="fas fa-times-circle"></i> Stok Habis
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection