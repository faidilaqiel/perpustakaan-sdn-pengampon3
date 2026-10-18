@extends('layouts.app')

@section('title', 'Dashboard - Perpustakaan Digital')

@section('content')
    {{-- Salam Pembuka --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-sm text-gray-500 mt-1">Selamat datang di Perpustakaan Digital SDN Pengampon 3</p>
        </div>
        <div class="flex items-center text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
            <i class="far fa-calendar-alt mr-2"></i>
            {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
        </div>
    </div>

    {{-- Kotak Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fas fa-book"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Buku</p>
                <p class="text-xl md:text-2xl font-bold text-gray-800">{{ \App\Models\Book::count() }}</p>
            </div>
        </div>
        
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fas fa-users"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Anggota</p>
                <p class="text-xl md:text-2xl font-bold text-gray-800">{{ \App\Models\User::whereIn('role', ['siswa', 'guru'])->count() }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fas fa-book-reader"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Sedang Dipinjam</p>
                <p class="text-xl md:text-2xl font-bold text-gray-800">{{ \App\Models\Loan::where('status', 'dipinjam')->count() }}</p>
            </div>
        </div>

        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-xl">
                <i class="fas fa-exclamation-triangle"></i>
            </div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Terlambat</p>
                <p class="text-xl md:text-2xl font-bold text-gray-800">0</p>
            </div>
        </div>
    </div>

    {{-- Menu Aksi Cepat --}}
    <div class="bg-white rounded-xl shadow-sm p-5 md:p-6 border border-gray-100">
        <h3 class="text-lg font-bold text-gray-800 mb-4">Menu Cepat</h3>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
            <a href="{{ route('petugas.scan') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition group">
                <div class="w-12 h-12 bg-teal-500 text-white rounded-lg flex items-center justify-center text-2xl shadow-md group-hover:scale-105 transition">
                    <i class="fas fa-qrcode"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">Scan QR</h4>
                    <p class="text-xs text-gray-500">Absensi & Peminjaman</p>
                </div>
            </a>
            
            <a href="{{ route('petugas.sirkulasi') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition group">
                <div class="w-12 h-12 bg-blue-500 text-white rounded-lg flex items-center justify-center text-2xl shadow-md group-hover:scale-105 transition">
                    <i class="fas fa-exchange-alt"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">Sirkulasi</h4>
                    <p class="text-xs text-gray-500">Peminjaman & Pengembalian</p>
                </div>
            </a>

            <a href="{{ route('petugas.kelolaBuku') }}" class="flex items-center gap-4 p-4 rounded-xl border border-gray-100 hover:bg-gray-50 transition group">
                <div class="w-12 h-12 bg-purple-500 text-white rounded-lg flex items-center justify-center text-2xl shadow-md group-hover:scale-105 transition">
                    <i class="fas fa-book"></i>
                </div>
                <div>
                    <h4 class="font-bold text-gray-800">Kelola Buku</h4>
                    <p class="text-xs text-gray-500">Tambah / Edit Data Buku</p>
                </div>
            </a>
        </div>
    </div>
@endsection