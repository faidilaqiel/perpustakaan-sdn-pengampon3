@extends('layouts.app')

@section('title', isset($book) ? 'Edit Buku' : 'Tambah Buku Baru')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header Halaman --}}
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 mb-6 border border-gray-100 flex items-center gap-3">
            <a href="{{ route('petugas.kelolaBuku') }}" class="w-10 h-10 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center hover:bg-gray-200 transition shrink-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">{{ isset($book) ? 'Edit Data Buku' : 'Tambah Buku Baru' }}</h2>
                <p class="text-xs md:text-sm text-gray-500">Lengkapi formulir di bawah ini dengan benar.</p>
            </div>
        </div>

        {{-- Notifikasi Error Validasi --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($book) ? route('petugas.buku.update', $book->id) : route('petugas.buku.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-4 md:p-8">
            @csrf
            @if(isset($book))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 md:gap-6">
                {{-- Kolom Kiri --}}
                <div class="space-y-4 md:space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Kode Buku <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $book->code ?? '') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none font-mono uppercase text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Judul Buku <span class="text-red-500">*</span></label>
                        <input type="text" name="title" value="{{ old('title', $book->title ?? '') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Penulis <span class="text-red-500">*</span></label>
                        <input type="text" name="author" value="{{ old('author', $book->author ?? '') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Penerbit</label>
                        <input type="text" name="publisher" value="{{ old('publisher', $book->publisher ?? '') }}"
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm">
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-4 md:space-y-5">
                    <div class="grid grid-cols-2 gap-3 md:gap-4">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Tahun Terbit</label>
                            <input type="number" name="year" value="{{ old('year', $book->year ?? date('Y')) }}"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Stok Total <span class="text-red-500">*</span></label>
                            <input type="number" name="stock_total" value="{{ old('stock_total', $book->stock_total ?? 1) }}" required min="0"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm">
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Kategori <span class="text-red-500">*</span></label>
                        <select name="category_id" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white text-sm">
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $book->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                    {{ $cat->name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-1 md:mb-2">Cover Buku (Opsional)</label>
                        @if(isset($book) && $book->cover && file_exists(public_path('covers/' . $book->cover)))
                            <div class="mb-2">
                                <img src="{{ asset('covers/' . $book->cover) }}" alt="Cover Lama" class="h-20 rounded shadow-sm">
                                <p class="text-xs text-gray-500 mt-1">Biarkan kosong jika tidak ingin mengubah cover.</p>
                            </div>
                        @endif
                        <input type="file" name="cover" accept="image/*"
                               class="w-full px-2 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-xs md:text-sm file:mr-2 md:file:mr-4 file:py-1.5 md:file:py-2 file:px-3 md:file:px-4 file:rounded-full file:border-0 file:text-xs md:file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                        <p class="text-[10px] md:text-xs text-gray-500 mt-1">Format: JPG, PNG, JPEG. Maks: 2MB.</p>
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi (Tumpuk di HP, Sejajar di Laptop) --}}
            <div class="mt-8 pt-6 border-t border-gray-100 flex flex-col md:flex-row justify-end gap-3">
                <a href="{{ route('petugas.kelolaBuku') }}" class="w-full md:w-auto text-center px-6 py-3 md:py-2.5 bg-gray-100 text-gray-600 rounded-lg font-semibold hover:bg-gray-200 transition text-sm">
                    Batal
                </a>
                <button type="submit" class="w-full md:w-auto px-6 py-3 md:py-2.5 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-md flex items-center justify-center gap-2 text-sm">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
@endsection