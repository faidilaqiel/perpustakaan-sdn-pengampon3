@extends('layouts.app')

@section('title', 'Scan QR - Perpustakaan Digital')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6 md:p-10 border border-gray-100 text-center">
            <div class="w-20 h-20 bg-green-100 text-green-600 rounded-full flex items-center justify-center text-4xl mx-auto mb-4">
                <i class="fas fa-qrcode"></i>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Scan QR Code Anggota</h2>
            <p class="text-gray-500 mb-8">Scan QR Code pada kartu anggota, ATAU ketik NISN/NIP secara manual.</p>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('petugas.scan.hasil') }}" class="max-w-md mx-auto">
                @csrf
                <div class="mb-6">
                    <input type="text" name="qr_code" required autofocus
                           class="w-full px-4 py-4 text-center text-xl border-2 border-green-500 rounded-xl focus:outline-none focus:ring-4 focus:ring-green-200 transition font-mono tracking-widest"
                           placeholder="Ketik NISN / NIP...">
                    <p class="text-xs text-gray-400 mt-2">💡 Kalau pakai scanner QR: cursor harus di kolom ini.</p>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-lg py-4 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> CARI ANGGOTA
                </button>
            </form>
        </div>

        {{-- Panduan --}}
        <div class="bg-blue-50 border border-blue-100 rounded-xl p-5 md:p-6 mt-6 text-sm text-blue-800 shadow-sm">
            <h3 class="font-bold mb-3 flex items-center gap-2"><i class="fas fa-info-circle"></i> Cara Pakai:</h3>
            <ol class="list-decimal list-inside space-y-1.5 opacity-90">
                <li><strong>Manual:</strong> Ketik NISN/NIP siswa di kolom, lalu klik "Cari Anggota".</li>
                <li><strong>Scanner QR:</strong> Colok scanner USB, klik kolom input, lalu scan QR pada kartu.</li>
                <li><strong>Hasil:</strong> Aplikasi akan menampilkan data siswa + peminjaman aktifnya.</li>
            </ol>
        </div>
    </div>
@endsection