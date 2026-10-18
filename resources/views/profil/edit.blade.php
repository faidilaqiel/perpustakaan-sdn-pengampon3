@extends('layouts.app')

@section('title', 'Profil Saya - Perpustakaan Digital')

@section('content')
    <div class="max-w-2xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
            <h2 class="text-xl font-bold text-gray-800 mb-6 text-center">Profil Saya</h2>

            @if(session('success'))
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <form method="POST" action="{{ route('profil.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PUT')

                {{-- Foto Profil --}}
                <div class="flex flex-col items-center mb-6">
                    @if($user->photo && file_exists(public_path('photos/' . $user->photo)))
                        <img src="{{ asset('photos/' . $user->photo) }}" class="w-24 h-24 object-cover rounded-full shadow-lg mb-3">
                    @else
                        <div class="w-24 h-24 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 text-3xl shadow-lg mb-3">
                            <i class="fas fa-user"></i>
                        </div>
                    @endif
                    <label class="text-sm font-semibold text-green-600 cursor-pointer hover:underline">
                        Ganti Foto Profil
                        <input type="file" name="photo" class="hidden" accept="image/*">
                    </label>
                    <p class="text-xs text-gray-400 mt-1">Format: JPG/PNG. Maks 2MB.</p>
                </div>

                {{-- Nama --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap</label>
                    <input type="text" name="name" value="{{ old('name', $user->name) }}" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                {{-- Info Role & ID --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Role</label>
                        <input type="text" value="{{ ucfirst($user->role) }}" disabled class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">NISN / NIP</label>
                        <input type="text" value="{{ $user->nisn ?? $user->nip ?? '-' }}" disabled class="w-full px-4 py-2.5 border border-gray-200 bg-gray-50 rounded-lg text-gray-500 cursor-not-allowed font-mono">
                    </div>
                </div>

                {{-- Password --}}
                <div>
                    <label class="block text-sm font-bold text-gray-700 mb-2">Kata Sandi Baru (Opsional)</label>
                    <input type="password" name="password" placeholder="Kosongkan jika tidak ingin mengubah sandi" class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>

                <div class="pt-4 border-t border-gray-100 flex justify-end">
                    <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2.5 rounded-lg font-semibold shadow-md transition flex items-center gap-2">
                        <i class="fas fa-save"></i> Simpan Perubahan
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection