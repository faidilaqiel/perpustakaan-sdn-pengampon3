@extends('layouts.app')

@section('title', isset($user) ? 'Edit Anggota' : 'Tambah Anggota Baru')

@section('content')
    <div class="max-w-4xl mx-auto">
        {{-- Header Halaman --}}
        <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex items-center gap-3">
            <a href="{{ route('petugas.kelolaAnggota') }}" class="w-10 h-10 bg-gray-100 text-gray-600 rounded-lg flex items-center justify-center hover:bg-gray-200 transition">
                <i class="fas fa-arrow-left"></i>
            </a>
            <div>
                <h2 class="text-xl font-bold text-gray-800">{{ isset($user) ? 'Edit Data Anggota' : 'Tambah Anggota Baru' }}</h2>
                <p class="text-sm text-gray-500">Lengkapi formulir di bawah ini dengan benar.</p>
            </div>
        </div>

        {{-- Notifikasi Error --}}
        @if ($errors->any())
            <div class="bg-red-50 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm">
                <ul class="list-disc list-inside">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ isset($user) ? route('petugas.anggota.update', $user->id) : route('petugas.anggota.store') }}" enctype="multipart/form-data" class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 md:p-8">
            @csrf
            @if(isset($user))
                @method('PUT')
            @endif

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                {{-- Kolom Kiri --}}
                <div class="space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Role <span class="text-red-500">*</span></label>
                        <select name="role" id="role" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white" onchange="toggleFields()">
                            <option value="siswa" {{ old('role', $user->role ?? '') === 'siswa' ? 'selected' : '' }}>Siswa</option>
                            <option value="guru" {{ old('role', $user->role ?? '') === 'guru' ? 'selected' : '' }}>Guru</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Nama Lengkap <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $user->name ?? '') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Jenis Kelamin <span class="text-red-500">*</span></label>
                        <select name="gender" required class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none bg-white">
                            <option value="L" {{ old('gender', $user->gender ?? '') === 'L' ? 'selected' : '' }}>Laki-laki</option>
                            <option value="P" {{ old('gender', $user->gender ?? '') === 'P' ? 'selected' : '' }}>Perempuan</option>
                        </select>
                    </div>
                </div>

                {{-- Kolom Kanan --}}
                <div class="space-y-5">
                    {{-- Field Khusus Siswa --}}
                    <div id="field-siswa" class="{{ old('role', $user->role ?? 'siswa') === 'guru' ? 'hidden' : '' }}">
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">Kelas <span class="text-red-500">*</span></label>
                            <input type="text" name="class" id="input-class" value="{{ old('class', $user->class ?? '') }}" placeholder="Contoh: 1, 2, 3, 4, 5, atau 6"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-2">NISN <span class="text-red-500">*</span></label>
                            <input type="text" name="nisn" id="input-nisn" value="{{ old('nisn', $user->nisn ?? '') }}" placeholder="Contoh: 0012345678"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none font-mono">
                            <p class="text-xs text-gray-500 mt-1">NISN terdiri dari 5-10 digit angka.</p>
                        </div>
                    </div>

                    {{-- Field Khusus Guru --}}
                    <div id="field-guru" class="{{ old('role', $user->role ?? 'siswa') === 'guru' ? '' : 'hidden' }}">
                        <div class="mb-5">
                            <label class="block text-sm font-bold text-gray-700 mb-2">NIP <span class="text-red-500">*</span></label>
                            <input type="text" name="nip" id="input-nip" value="{{ old('nip', $user->nip ?? '') }}" placeholder="Contoh: 19750717 202321 2 001"
                                   class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none font-mono">
                            <p class="text-xs text-gray-500 mt-1">NIP terdiri dari 5-18 digit angka (spasi atau tanda hubung akan otomatis dibersihkan).</p>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Kata Sandi {{ isset($user) ? '(Kosongkan jika tidak diubah)' : '*' }}</label>
                        <div class="relative">
                            <input type="password" name="password" id="member-password" {{ isset($user) ? '' : 'required' }} placeholder="Minimal 6 karakter"
                                   class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none">
                            <button type="button" onclick="togglePassword('member-password', 'eye-icon-member')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                                <i class="fas fa-eye" id="eye-icon-member"></i>
                            </button>
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-bold text-gray-700 mb-2">Foto Profil (Opsional)</label>
                        <input type="file" name="photo" accept="image/*"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-500 focus:outline-none text-sm file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-green-50 file:text-green-700 hover:file:bg-green-100">
                    </div>
                </div>
            </div>

            {{-- Tombol Aksi --}}
            <div class="mt-8 pt-6 border-t border-gray-100 flex justify-end gap-3">
                <a href="{{ route('petugas.kelolaAnggota') }}" class="px-6 py-2.5 bg-gray-100 text-gray-600 rounded-lg font-semibold hover:bg-gray-200 transition">
                    Batal
                </a>
                <button type="submit" class="px-6 py-2.5 bg-green-600 text-white rounded-lg font-semibold hover:bg-green-700 transition shadow-md flex items-center gap-2">
                    <i class="fas fa-save"></i> Simpan Data
                </button>
            </div>
        </form>
    </div>

    {{-- Script untuk Toggle Form Siswa/Guru & Toggle Password --}}
    <script>
        function toggleFields() {
            const role = document.getElementById('role').value;
            const fieldSiswa = document.getElementById('field-siswa');
            const fieldGuru = document.getElementById('field-guru');
            const inputClass = document.getElementById('input-class');
            const inputNisn = document.getElementById('input-nisn');
            const inputNip = document.getElementById('input-nip');

            if (role === 'guru') {
                fieldSiswa.classList.add('hidden');
                fieldGuru.classList.remove('hidden');
                if (inputClass) inputClass.disabled = true;
                if (inputNisn) inputNisn.disabled = true;
                if (inputNip) inputNip.disabled = false;
            } else {
                fieldSiswa.classList.remove('hidden');
                fieldGuru.classList.add('hidden');
                if (inputClass) inputClass.disabled = false;
                if (inputNisn) inputNisn.disabled = false;
                if (inputNip) inputNip.disabled = true;
            }
        }
        document.addEventListener('DOMContentLoaded', toggleFields);

        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
@endsection