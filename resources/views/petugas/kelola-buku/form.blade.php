<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($book) ? 'Edit Buku' : 'Tambah Buku' }} - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- ===== HEADER ===== --}}
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
                <p class="font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-sm">Role: {{ Auth::user()->role }}</p>
            </div>
        </div>
    </header>

    {{-- ===== KONTEN ===== --}}
    <div class="container mx-auto p-6 max-w-3xl">

        {{-- Tombol Kembali --}}
        <a href="{{ route('petugas.kelolaBuku') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Daftar Buku
        </a>

        {{-- Pesan Error Validasi --}}
        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                <p class="font-bold mb-2">⚠️ Ada kesalahan:</p>
                <ul class="list-disc list-inside text-sm">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Form --}}
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-1">
                {{ isset($book) ? '✏️ Edit Buku' : '➕ Tambah Buku Baru' }}
            </h2>
            <p class="text-gray-600 mb-6">
                {{ isset($book) ? 'Perbarui data buku di bawah ini.' : 'Isi data buku baru dengan lengkap.' }}
            </p>

            <form method="POST"
                  action="{{ isset($book) ? route('petugas.buku.update', $book->id) : route('petugas.buku.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if(isset($book))
                    @method('PUT')
                @endif

                {{-- Kode Buku --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Kode Buku <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="code"
                           value="{{ old('code', $book->code ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('code') border-red-500 @enderror"
                           placeholder="Contoh: BK-006"
                           required>
                    @error('code')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Judul --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Judul Buku <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="title"
                           value="{{ old('title', $book->title ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('title') border-red-500 @enderror"
                           placeholder="Contoh: Laskar Pelangi"
                           required>
                    @error('title')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Penulis & Penerbit (2 kolom) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Penulis <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="author"
                               value="{{ old('author', $book->author ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('author') border-red-500 @enderror"
                               placeholder="Contoh: Andrea Hirata"
                               required>
                        @error('author')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Penerbit <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="publisher"
                               value="{{ old('publisher', $book->publisher ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('publisher') border-red-500 @enderror"
                               placeholder="Contoh: Bentang Pustaka"
                               required>
                        @error('publisher')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Tahun & Kategori (2 kolom) --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Tahun Terbit <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="year"
                               value="{{ old('year', $book->year ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('year') border-red-500 @enderror"
                               placeholder="Contoh: 2020"
                               min="1900" max="{{ date('Y') }}"
                               required>
                        @error('year')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label class="block text-gray-700 font-semibold mb-2">
                            Kategori <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id"
                                class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('category_id') border-red-500 @enderror"
                                required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ old('category_id', $book->category_id ?? '') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endforeach
                        </select>
                        @error('category_id')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Stok Total --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Jumlah Stok Total <span class="text-red-500">*</span>
                    </label>
                    <input type="number" name="stock_total"
                           value="{{ old('stock_total', $book->stock_total ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('stock_total') border-red-500 @enderror"
                           placeholder="Contoh: 5"
                           min="1"
                           required>
                    <p class="text-xs text-gray-500 mt-1">
                        💡 Stok tersedia akan otomatis dihitung (dikurangi yang sedang dipinjam).
                    </p>
                    @error('stock_total')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Cover Buku --}}
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Cover Buku (Opsional)
                    </label>

                    {{-- Preview cover lama (kalau edit) --}}
                    @if(isset($book) && $book->cover)
                        <div class="mb-3">
                            <p class="text-sm text-gray-500 mb-2">Cover saat ini:</p>
                            <img src="{{ asset('covers/' . $book->cover) }}"
                                 alt="Cover Lama"
                                 class="w-24 h-32 object-cover rounded shadow">
                        </div>
                    @endif

                    <input type="file" name="cover" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('cover') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        📷 Format: JPG, PNG, JPEG. Maks: 2MB.
                        @if(isset($book))
                            (Biarkan kosong kalau tidak mau ganti cover.)
                        @endif
                    </p>
                    @error('cover')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                        {{ isset($book) ? '💾 Simpan Perubahan' : '➕ Tambah Buku' }}
                    </button>
                    <a href="{{ route('petugas.kelolaBuku') }}"
                       class="bg-gray-400 hover:bg-gray-500 text-white px-6 py-3 rounded-lg font-semibold">
                        Batal
                    </a>
                </div>

            </form>
        </div>

    </div>

    {{-- ===== FOOTER ===== --}}
    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>