<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Anggota - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- HEADER --}}
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
                <p class="font-semibold">Administrator</p>
                <p class="text-sm">Petugas Perpustakaan</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- KONTEN --}}
    <div class="container mx-auto p-6">

        <a href="{{ route('petugas.dashboard') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Dashboard
        </a>

        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-100 p-4 rounded-lg">
                <p class="text-blue-800 font-semibold">👥 Total Anggota</p>
                <p class="text-3xl font-bold text-blue-900">{{ $totalAnggota }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <p class="text-green-800 font-semibold">🎓 Total Siswa</p>
                <p class="text-3xl font-bold text-green-900">{{ $totalSiswa }}</p>
            </div>
            <div class="bg-purple-100 p-4 rounded-lg">
                <p class="text-purple-800 font-semibold">👨‍🏫 Total Guru</p>
                <p class="text-3xl font-bold text-purple-900">{{ $totalGuru }}</p>
            </div>
        </div>

        {{-- Judul + Tombol Tambah --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex justify-between items-center flex-wrap gap-3">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-1">👥 Kelola Anggota</h2>
                    <p class="text-gray-600">Kelola data siswa & guru perpustakaan.</p>
                </div>
                <a href="{{ route('petugas.anggota.create') }}"
                   class="bg-green-600 hover:bg-green-700 text-white px-6 py-3 rounded-lg font-semibold flex items-center gap-2">
                    ➕ Tambah Anggota Baru
                </a>
            </div>

            {{-- FORM FILTER --}}
            <form method="GET" action="{{ route('petugas.kelolaAnggota') }}" class="mt-4 flex gap-2 flex-wrap items-end">
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Pencarian</label>
                    <input type="text" name="q" value="{{ $keyword }}"
                           class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 min-w-[200px]"
                           placeholder="Cari nama, NISN, NIP...">
                </div>

                <div>
                    <label class="block text-xs text-gray-500 mb-1">Role</label>
                    <select name="role" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Role</option>
                        <option value="siswa" {{ $filterRole === 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="guru" {{ $filterRole === 'guru' ? 'selected' : '' }}>Guru</option>
                    </select>
                </div>

                {{-- FILTER KELAS BARU --}}
                <div>
                    <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                    <select name="class" class="px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500">
                        <option value="">Semua Kelas</option>
                        @foreach($listKelas as $kelas)
                            <option value="{{ $kelas }}" {{ request('class') == $kelas ? 'selected' : '' }}>
                                Kelas {{ $kelas }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-6 py-2 rounded-lg h-[42px]">
                    🔍 Filter
                </button>
                @if($keyword || $filterRole || request('class'))
                    <a href="{{ route('petugas.kelolaAnggota') }}"
                       class="bg-gray-400 hover:bg-gray-500 text-white px-4 py-2 rounded-lg h-[42px] flex items-center">
                        Reset
                    </a>
                @endif
            </form>
        </div>

        {{-- Tabel Anggota --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            @if($users->isEmpty())
                <div class="p-10 text-center text-gray-500">
                    <p class="text-4xl mb-3">📭</p>
                    <p>Belum ada anggota yang sesuai dengan filter/pencarian.</p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100 border-b">
                            <tr>
                                <th class="px-4 py-3 text-left text-sm font-semibold">No</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Foto</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Nama & ID</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Role</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Kelas / Gender</th>
                                <th class="px-4 py-3 text-left text-sm font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($users as $index => $user)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-3 text-sm">
                                        {{ $users->firstItem() + $index }}
                                    </td>
                                    <td class="px-4 py-3">
                                        @if($user->photo)
                                            <img src="{{ asset('photos/' . $user->photo) }}"
                                                 alt="Foto"
                                                 class="w-12 h-12 object-cover rounded-full shadow">
                                        @else
                                            <div class="w-12 h-12 bg-blue-100 rounded-full flex items-center justify-center text-xl">
                                                {{ $user->gender === 'L' ? '👦' : '👧' }}
                                            </div>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <strong>{{ $user->name }}</strong><br>
                                        <span class="text-xs text-gray-500 font-mono">
                                            @if($user->role === 'siswa')
                                                NISN: {{ $user->nisn }}
                                            @else
                                                NIP: {{ $user->nip }}
                                            @endif
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($user->role === 'siswa')
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                🎓 Siswa
                                            </span>
                                        @elseif($user->role === 'guru')
                                            <span class="bg-purple-100 text-purple-800 px-2 py-1 rounded text-xs">
                                                👨‍🏫 Guru
                                            </span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        @if($user->role === 'siswa')
                                            <span class="font-semibold">{{ $user->class ?? '-' }}</span>
                                        @endif
                                        <span class="text-xs text-gray-500">
                                            ({{ $user->gender === 'L' ? 'Laki-laki' : 'Perempuan' }})
                                        </span>
                                    </td>
                                    <td class="px-4 py-3 text-sm">
                                        <div class="flex gap-1 flex-wrap">
                                            {{-- TOMBOL BARU: CETAK KARTU --}}
                                            <a href="{{ route('petugas.anggota.cetakKartu', $user->id) }}"
                                               target="_blank"
                                               class="bg-green-500 hover:bg-green-600 text-white px-3 py-1 rounded text-xs">
                                                🖨️ Cetak
                                            </a>
                                            
                                            {{-- Tombol Lama --}}
                                            <a href="{{ route('petugas.anggota.edit', $user->id) }}"
                                               class="bg-yellow-500 hover:bg-yellow-600 text-white px-3 py-1 rounded text-xs">
                                                ✏️ Edit
                                            </a>
                                            <a href="{{ route('petugas.scan.qrCode', $user->id) }}"
                                               target="_blank"
                                               class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">
                                                📱 QR
                                            </a>
                                            <form method="POST"
                                                  action="{{ route('petugas.anggota.destroy', $user->id) }}"
                                                  onsubmit="return confirm('Yakin hapus anggota {{ $user->name }}?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit"
                                                        class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-xs">
                                                    🗑️ Hapus
                                                </button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <div class="p-4 border-t">
                    {{-- Memastikan filter class ikut tersimpan di URL pagination --}}
                    {{ $users->appends(['q' => $keyword, 'role' => $filterRole, 'class' => request('class')])->links() }}
                </div>
            @endif
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>