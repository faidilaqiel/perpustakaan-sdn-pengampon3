@extends('layouts.app')

@section('title', 'Kelola Anggota - Perpustakaan Digital')

@section('content')
    {{-- Header Halaman --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Kelola Anggota</h2>
            <p class="text-sm text-gray-500 mt-1">Kelola data siswa & guru perpustakaan.</p>
        </div>
        <a href="{{ route('petugas.anggota.create') }}" class="bg-green-600 hover:bg-green-700 text-white px-5 py-2.5 rounded-lg font-semibold text-sm flex items-center gap-2 shadow-md transition">
            <i class="fas fa-plus"></i> Tambah Anggota
        </a>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl shadow-sm">
            <p class="text-blue-800 font-semibold text-sm">👥 Total Anggota</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $totalAnggota }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl shadow-sm">
            <p class="text-green-800 font-semibold text-sm">🎓 Total Siswa</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $totalSiswa }}</p>
        </div>
        <div class="bg-purple-50 border border-purple-200 p-4 rounded-xl shadow-sm">
            <p class="text-purple-800 font-semibold text-sm">👨‍🏫 Total Guru</p>
            <p class="text-3xl font-bold text-purple-900 mt-1">{{ $totalGuru }}</p>
        </div>
    </div>

    {{-- Filter & Pencarian --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
        <form method="GET" action="{{ route('petugas.kelolaAnggota') }}" class="flex flex-col md:flex-row gap-2 items-stretch md:items-end">
            <div class="flex-1">
                <label class="block text-xs text-gray-500 mb-1">Pencarian</label>
                <input type="text" name="q" value="{{ $keyword }}" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm" placeholder="Cari nama, NISN, NIP...">
            </div>
            <div class="flex gap-2">
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Role</label>
                    <select name="role" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                        <option value="">Semua Role</option>
                        <option value="siswa" {{ $filterRole === 'siswa' ? 'selected' : '' }}>Siswa</option>
                        <option value="guru" {{ $filterRole === 'guru' ? 'selected' : '' }}>Guru</option>
                    </select>
                </div>
                <div class="flex-1">
                    <label class="block text-xs text-gray-500 mb-1">Kelas</label>
                    <select name="class" class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 text-sm">
                        <option value="">Semua Kelas</option>
                        @foreach($listKelas as $kelas)
                            <option value="{{ $kelas }}" {{ request('class') == $kelas ? 'selected' : '' }}>Kelas {{ $kelas }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="flex gap-2 mt-2 md:mt-0">
                <button type="submit" class="bg-gray-800 hover:bg-gray-900 text-white px-4 py-2 rounded-lg w-full md:w-auto text-sm font-semibold">Filter</button>
                @if($keyword || $filterRole || request('class'))
                    <a href="{{ route('petugas.kelolaAnggota') }}" class="bg-gray-200 hover:bg-gray-300 text-gray-700 px-4 py-2 rounded-lg text-center w-full md:w-auto text-sm font-semibold">Reset</a>
                @endif
            </div>
        </form>
    </div>

    {{-- Konten Tabel --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($users->isEmpty())
            <div class="p-10 text-center text-gray-500">
                <p class="text-4xl mb-3">📭</p>
                <p>Belum ada anggota yang sesuai dengan filter/pencarian.</p>
            </div>
        @else
            {{-- Tabel Desktop --}}
            <div class="hidden md:block overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                            <th class="p-4 font-semibold">No</th>
                            <th class="p-4 font-semibold">Foto</th>
                            <th class="p-4 font-semibold">Nama & ID</th>
                            <th class="p-4 font-semibold">Role</th>
                            <th class="p-4 font-semibold">Kelas / Gender</th>
                            <th class="p-4 font-semibold text-center">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $index => $user)
                            <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                                <td class="p-4 text-gray-500">{{ $users->firstItem() + $index }}</td>
                                <td class="p-4">
                                    @if($user->photo && file_exists(public_path('photos/' . $user->photo)))
                                        <img src="{{ asset('photos/' . $user->photo) }}" alt="Foto" class="w-10 h-10 object-cover rounded-full shadow">
                                    @else
                                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                                            <i class="fas fa-user"></i>
                                        </div>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <strong class="text-gray-800 block">{{ $user->name }}</strong>
                                    <span class="text-xs text-gray-500 font-mono">
                                        @if($user->role === 'siswa') NISN: {{ $user->nisn }} @else NIP: {{ $user->nip }} @endif
                                    </span>
                                </td>
                                <td class="p-4">
                                    @if($user->role === 'siswa')
                                        <span class="bg-blue-100 text-blue-700 px-2.5 py-1 rounded-full text-xs font-semibold">🎓 Siswa</span>
                                    @elseif($user->role === 'guru')
                                        <span class="bg-purple-100 text-purple-700 px-2.5 py-1 rounded-full text-xs font-semibold">👨‍🏫 Guru</span>
                                    @endif
                                </td>
                                <td class="p-4">
                                    <span class="text-gray-800 font-semibold">{{ $user->class ?? '-' }}</span>
                                    <span class="text-xs text-gray-500 block">({{ $user->gender === 'L' ? 'Laki-laki' : 'Perempuan' }})</span>
                                </td>
                                <td class="p-4">
                                    <div class="flex justify-center gap-2">
                                        <a href="{{ route('petugas.anggota.edit', $user->id) }}" class="w-8 h-8 bg-yellow-100 text-yellow-600 rounded flex items-center justify-center hover:bg-yellow-200 transition" title="Edit">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                        <a href="{{ route('petugas.scan.qrCode', $user->id) }}" target="_blank" class="w-8 h-8 bg-blue-100 text-blue-600 rounded flex items-center justify-center hover:bg-blue-200 transition" title="QR Code">
                                            <i class="fas fa-qrcode"></i>
                                        </a>
                                        <form method="POST" action="{{ route('petugas.anggota.destroy', $user->id) }}" onsubmit="return confirm('Yakin hapus anggota ini?')">
                                            @csrf @method('DELETE')
                                            <button type="submit" class="w-8 h-8 bg-red-100 text-red-600 rounded flex items-center justify-center hover:bg-red-200 transition" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Tampilan HP (Kartu) --}}
            <div class="block md:hidden p-4 space-y-4">
                @foreach($users as $index => $user)
                    <div class="border border-gray-200 rounded-lg p-3 shadow-sm bg-gray-50">
                        <div class="flex items-center gap-3 mb-3 border-b border-gray-200 pb-2">
                            @if($user->photo && file_exists(public_path('photos/' . $user->photo)))
                                <img src="{{ asset('photos/' . $user->photo) }}" class="w-12 h-12 object-cover rounded-full shadow">
                            @else
                                <div class="w-12 h-12 bg-gray-200 rounded-full flex items-center justify-center text-gray-500">
                                    <i class="fas fa-user"></i>
                                </div>
                            @endif
                            <div>
                                <strong class="text-gray-800 text-sm block">{{ $user->name }}</strong>
                                <span class="text-xs text-gray-500 font-mono">
                                    @if($user->role === 'siswa') NISN: {{ $user->nisn }} @else NIP: {{ $user->nip }} @endif
                                </span>
                            </div>
                        </div>
                        <div class="grid grid-cols-2 text-xs text-gray-600 mb-3 gap-2">
                            <div>
                                <span class="block text-gray-400">Role</span>
                                @if($user->role === 'siswa') <span class="text-blue-600 font-semibold">🎓 Siswa</span> @else <span class="text-purple-600 font-semibold">👨‍🏫 Guru</span> @endif
                            </div>
                            <div>
                                <span class="block text-gray-400">Kelas / Gender</span>
                                <span class="font-semibold">{{ $user->class ?? '-' }} ({{ $user->gender === 'L' ? 'L' : 'P' }})</span>
                            </div>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-2 border-t border-gray-200 pt-2">
                            <a href="{{ route('petugas.anggota.edit', $user->id) }}" class="flex-1 bg-yellow-500 text-white py-1.5 rounded text-xs text-center font-semibold">✏️ Edit</a>
                            <a href="{{ route('petugas.scan.qrCode', $user->id) }}" target="_blank" class="flex-1 bg-blue-500 text-white py-1.5 rounded text-xs text-center font-semibold">📱 QR</a>
                            <form method="POST" action="{{ route('petugas.anggota.destroy', $user->id) }}" onsubmit="return confirm('Hapus?')" class="flex-1">
                                @csrf @method('DELETE')
                                <button type="submit" class="w-full bg-red-500 text-white py-1.5 rounded text-xs font-semibold">🗑️ Hapus</button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="p-4 border-t border-gray-100">
                {{ $users->appends(['q' => $keyword, 'role' => $filterRole, 'class' => request('class')])->links() }}
            </div>
        @endif
    </div>
@endsection