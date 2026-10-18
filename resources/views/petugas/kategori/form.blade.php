@extends('layouts.app')

@section('title', isset($category) ? 'Edit Kategori' : 'Tambah Kategori')

@section('content')
    <div class="max-w-2xl mx-auto">
        {{-- Header Halaman --}}
        <div class="bg-white rounded-xl shadow-sm p-4 md:p-5 mb-6 border border-gray-100 flex items-center gap-3">
            <a href="{{ route('petugas.kategori.index') }}" class="w-10 h-10 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center hover:bg-gray-200 transition shrink-0">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-lg md:text-xl font-bold text-gray-800">{{ isset($category) ? 'Edit Kategori' : 'Tambah Kategori Baru' }}</h2>
                <p class="text-xs md:text-sm text-gray-500">Lengkapi formulir di bawah ini.</p>
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

        <form method="POST" action="{{ isset($category) ? route('petugas.kategori.update', $category->id) : route('petugas.kategori.store') }}" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
            @csrf
            @if(isset($category))
                @method('PUT')
            @endif

            <div class="mb-6">
                <label class="block text-sm font-bold text-gray-700 mb-2">Nama Kategori <span class="text-red-500">*</span></label>
                <input type="text" name="name" value="{{ old('name', $category->name ?? '') }}" required placeholder="Contoh: Cerita Rakyat, Matematika, IPA..."
                       class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm">
            </div>

            <div class="pt-4 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('petugas.kategori.index') }}" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-lg font-semibold hover:bg-gray-200 transition text-sm">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-md flex items-center gap-2 text-sm">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>
@endsection