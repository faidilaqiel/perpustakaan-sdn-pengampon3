<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ isset($user) ? 'Edit Anggota' : 'Tambah Anggota' }} - Perpustakaan Digital SDN Pengampon 3</title>
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
        <a href="{{ route('petugas.kelolaAnggota') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Daftar Anggota
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
                {{ isset($user) ? '✏️ Edit Anggota' : '➕ Tambah Anggota Baru' }}
            </h2>
            <p class="text-gray-600 mb-6">
                {{ isset($user) ? 'Perbarui data anggota di bawah ini.' : 'Isi data anggota baru dengan lengkap.' }}
            </p>

            <form method="POST"
                  action="{{ isset($user) ? route('petugas.anggota.update', $user->id) : route('petugas.anggota.store') }}"
                  enctype="multipart/form-data">
                @csrf
                @if(isset($user))
                    @method('PUT')
                @endif

                {{-- Role --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Role <span class="text-red-500">*</span>
                    </label>
                    <select name="role" id="role"
                            class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('role') border-red-500 @enderror"
                            required onchange="toggleFields()">
                        <option value="">-- Pilih Role --</option>
                        <option value="siswa" {{ old('role', $user->role ?? '') == 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="guru" {{ old('role', $user->role ?? '') == 'guru' ? 'selected' : '' }}>Guru</option>
                    </select>
                    @error('role')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Nama --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name"
                           value="{{ old('name', $user->name ?? '') }}"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('name') border-red-500 @enderror"
                           placeholder="Contoh: Ahmad Fauzi"
                           required>
                    @error('name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Gender --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Jenis Kelamin <span class="text-red-500">*</span>
                    </label>
                    <div class="flex gap-4">
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="L"
                                   {{ old('gender', $user->gender ?? '') == 'L' ? 'checked' : '' }} required>
                            <span>Laki-laki</span>
                        </label>
                        <label class="flex items-center gap-2">
                            <input type="radio" name="gender" value="P"
                                   {{ old('gender', $user->gender ?? '') == 'P' ? 'checked' : '' }} required>
                            <span>Perempuan</span>
                        </label>
                    </div>
                    @error('gender')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Field Siswa: NISN & Kelas --}}
                <div id="field-siswa" class="hidden">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                NISN <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="nisn"
                                   value="{{ old('nisn', $user->nisn ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('nisn') border-red-500 @enderror"
                                   placeholder="Contoh: 0012345678"
                                   maxlength="10">
                            @error('nisn')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-gray-700 font-semibold mb-2">
                                Kelas <span class="text-red-500">*</span>
                            </label>
                            <input type="text" name="class"
                                   value="{{ old('class', $user->class ?? '') }}"
                                   class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('class') border-red-500 @enderror"
                                   placeholder="Contoh: VI-A"
                                   maxlength="10">
                            @error('class')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Field Guru: NIP --}}
                <div id="field-guru" class="hidden">
                    <div class="mb-4">
                        <label class="block text-gray-700 font-semibold mb-2">
                            NIP <span class="text-red-500">*</span>
                        </label>
                        <input type="text" name="nip"
                               value="{{ old('nip', $user->nip ?? '') }}"
                               class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('nip') border-red-500 @enderror"
                               placeholder="Contoh: 199001012015011002"
                               maxlength="30">
                        @error('nip')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Password --}}
                <div class="mb-4">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Password
                        @if(!isset($user))
                            <span class="text-red-500">*</span>
                        @else
                            <span class="text-xs text-gray-500">(Kosongkan kalau tidak mau ubah)</span>
                        @endif
                    </label>
                    <input type="text" name="password"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('password') border-red-500 @enderror"
                           placeholder="{{ isset($user) ? 'Kosongkan kalau tidak diubah' : 'Minimal 6 karakter' }}"
                           {{ !isset($user) ? 'required' : '' }}>
                    <p class="text-xs text-gray-500 mt-1">
                        💡 Untuk siswa/guru, password biasanya sama dengan NISN/NIP-nya.
                    </p>
                    @error('password')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Foto --}}
                <div class="mb-6">
                    <label class="block text-gray-700 font-semibold mb-2">
                        Foto (Opsional)
                    </label>

                    @if(isset($user) && $user->photo)
                        <div class="mb-3">
                            <p class="text-sm text-gray-500 mb-2">Foto saat ini:</p>
                            <img src="{{ asset('photos/' . $user->photo) }}"
                                 alt="Foto Lama"
                                 class="w-24 h-24 object-cover rounded-full shadow">
                        </div>
                    @endif

                    <input type="file" name="photo" accept="image/*"
                           class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 @error('photo') border-red-500 @enderror">
                    <p class="text-xs text-gray-500 mt-1">
                        📷 Format: JPG, PNG, JPEG. Maks: 2MB.
                        @if(isset($user))
                            (Biarkan kosong kalau tidak mau ganti foto.)
                        @endif
                    </p>
                    @error('photo')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Tombol Aksi --}}
                <div class="flex gap-3">
                    <button type="submit"
                            class="bg-green-600 hover:bg-green-700 text-white px-8 py-3 rounded-lg font-semibold">
                        {{ isset($user) ? '💾 Simpan Perubahan' : '➕ Tambah Anggota' }}
                    </button>
                    <a href="{{ route('petugas.kelolaAnggota') }}"
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

    {{-- ===== SCRIPT UNTUK