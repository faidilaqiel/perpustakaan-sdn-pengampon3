@extends('layouts.app')

@section('title', 'Dashboard Cetak Kartu Anggota Siswa - SDN Pengampon 3')

@section('content')
<div class="space-y-6">

    {{-- Header Halaman --}}
    <div class="bg-white rounded-2xl shadow-xs p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-3">
                <span class="w-10 h-10 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shadow-xs">
                    <i class="fas fa-id-card"></i>
                </span>
                <div>
                    <h2 class="text-xl font-black text-gray-900 leading-tight">Dashboard Cetak Kartu Anggota Siswa</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Template Resmi SDN Pengampon 3 • Dilengkapi QR Code Pintar & Fitur Unduh PNG</p>
                </div>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-xs font-bold bg-blue-50 text-blue-700 px-3 py-1.5 rounded-lg border border-blue-200">
                <i class="fas fa-user-graduate mr-1"></i> Khusus Siswa Aktif
            </span>
        </div>
    </div>

    {{-- Filter Pencarian & Kelas --}}
    <div class="bg-white rounded-2xl shadow-xs p-5 border border-gray-100">
        <form method="GET" action="{{ route('petugas.cetakKartu') }}" class="grid grid-cols-1 sm:grid-cols-3 gap-3 items-end">
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Cari Siswa (Nama / NISN)</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <input type="text" name="q" value="{{ $keyword }}" placeholder="Ketik nama atau NISN..." class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-bold text-gray-700 mb-1.5">Pilih Tingkatan Kelas</label>
                <select name="class" class="w-full px-3 py-2 border border-gray-200 rounded-xl text-xs focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <option value="">Semua Kelas Siswa</option>
                    @foreach($listKelas as $k)
                        <option value="{{ $k }}" {{ $filterClass == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-blue-600 hover:bg-blue-700 text-white py-2 rounded-xl text-xs font-bold shadow-xs transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-filter"></i> Terapkan Filter
                </button>
                <a href="{{ route('petugas.cetakKartu') }}" class="px-3.5 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-xl text-xs font-bold transition flex items-center justify-center" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Grid Kartu Siswa --}}
    <div class="bg-white rounded-2xl shadow-xs p-6 border border-gray-100">
        <div class="flex justify-between items-center mb-5 pb-3 border-b border-gray-100">
            <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-users text-blue-500"></i> Daftar Siswa Terdaftar
            </h3>
            <span class="text-xs text-gray-500 font-medium">Total: <strong class="text-blue-600">{{ count($users) }}</strong> siswa</span>
        </div>

        @if(count($users) === 0)
            <div class="text-center py-16 text-gray-400">
                <i class="fas fa-id-badge text-5xl mb-3 text-gray-300"></i>
                <p class="text-sm font-bold text-gray-600">Tidak ada data siswa yang cocok.</p>
                <p class="text-xs text-gray-400 mt-1">Coba periksa kata kunci pencarian atau filter kelas Anda.</p>
            </div>
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
                @foreach($users as $user)
                    <div class="border border-gray-200 rounded-2xl p-4 bg-white hover:border-blue-400 hover:shadow-lg transition-all duration-200 flex flex-col justify-between group">
                        
                        <div class="flex items-center gap-3">
                            {{-- Mini Preview Kartu --}}
                            <div class="w-14 h-20 rounded-lg overflow-hidden border border-blue-200 shadow-2xs relative shrink-0 bg-blue-50">
                                <img src="{{ asset('images/template-kartu-siswa.jpg') }}" class="w-full h-full object-cover">
                                <div class="absolute inset-0 bg-blue-900/10 flex items-center justify-center">
                                    <i class="fas fa-qrcode text-blue-900 text-xs"></i>
                                </div>
                            </div>

                            {{-- Biodata Siswa --}}
                            <div class="flex-1 min-w-0">
                                <span class="bg-blue-100 text-blue-800 text-[9px] font-extrabold px-2 py-0.5 rounded-full inline-block mb-1">
                                    KELAS {{ strtoupper($user->class ?? '-') }}
                                </span>
                                <h4 class="font-bold text-sm text-gray-900 leading-tight truncate group-hover:text-blue-600 transition" title="{{ $user->name }}">
                                    {{ $user->name }}
                                </h4>
                                <p class="text-[11px] font-mono text-gray-500 mt-0.5">
                                    NISN: <span class="font-bold text-gray-700">{{ $user->nisn ?? '-' }}</span>
                                </p>
                            </div>
                        </div>

                        {{-- Tombol Buka Kartu & Download PNG --}}
                        <div class="mt-4 pt-3 border-t border-gray-100">
                            <a href="{{ route('petugas.kartuAnggotaSingle', $user->id) }}" target="_blank" class="w-full bg-blue-600 hover:bg-blue-700 text-white text-center py-2 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5 shadow-xs">
                                <i class="fas fa-eye"></i> Buka & Download PNG
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>
@endsection
