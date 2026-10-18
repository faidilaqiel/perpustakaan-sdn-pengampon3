@extends('layouts.app')

@section('title', 'Laporan Perpustakaan - SDN Pengampon 3')

@section('content')
    {{-- Header Halaman --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-xl font-bold text-gray-800">📊 Laporan Perpustakaan</h2>
            <p class="text-sm text-gray-500 mt-1">Statistik dan laporan aktivitas perpustakaan.</p>
        </div>
        <div class="flex items-center gap-2">
            <span class="text-sm text-gray-500 bg-gray-50 px-3 py-1.5 rounded-lg border border-gray-200">
                <i class="far fa-calendar-alt mr-1"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </span>
        </div>
    </div>

    {{-- Filter Periode & Tombol Cetak --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-6 border border-gray-100">
        <div class="flex flex-wrap gap-3 items-center">
            <form method="GET" action="{{ route('kepsek.laporan') }}" class="flex items-center gap-2">
                <label class="font-semibold text-gray-700 text-sm">Periode:</label>
                <select name="periode" class="border border-gray-300 rounded-lg px-3 py-1.5 text-sm focus:ring-2 focus:ring-green-500 focus:outline-none bg-white">
                    <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                    <option value="bulan_ini" {{ $periode === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                    <option value="bulan_lalu" {{ $periode === 'bulan_lalu' ? 'selected' : '' }}>Bulan Lalu</option>
                </select>
                <button type="submit" class="bg-green-600 hover:bg-green-700 text-white px-3 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition">
                    Tampilkan
                </button>
            </form>

            <a href="{{ route('kepsek.cetakPdf', ['periode' => $periode]) }}" target="_blank" class="bg-red-600 hover:bg-red-700 text-white px-4 py-1.5 rounded-lg text-sm font-semibold shadow-sm transition flex items-center gap-2 ml-auto">
                <i class="fas fa-file-pdf"></i> Cetak PDF
            </a>
        </div>
    </div>

    {{-- Kotak Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-3 gap-3 md:gap-4 mb-6">
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-lg"><i class="fas fa-book"></i></div>
            <div><p class="text-xs text-blue-800 font-semibold">Total Buku</p><p class="text-xl font-bold text-blue-900">{{ $totalBuku }}</p></div>
        </div>
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-lg"><i class="fas fa-users"></i></div>
            <div><p class="text-xs text-green-800 font-semibold">Total Anggota</p><p class="text-xl font-bold text-green-900">{{ $totalAnggota }}</p></div>
        </div>
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center text-lg"><i class="fas fa-folder"></i></div>
            <div><p class="text-xs text-yellow-800 font-semibold">Total Kategori</p><p class="text-xl font-bold text-yellow-900">{{ $totalKategori }}</p></div>
        </div>
        <div class="bg-purple-50 border border-purple-200 p-4 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-purple-100 text-purple-600 rounded-lg flex items-center justify-center text-lg"><i class="fas fa-book-reader"></i></div>
            <div><p class="text-xs text-purple-800 font-semibold">Sedang Dipinjam</p><p class="text-xl font-bold text-purple-900">{{ $sedangDipinjam }}</p></div>
        </div>
        <div class="bg-orange-50 border border-orange-200 p-4 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-orange-100 text-orange-600 rounded-lg flex items-center justify-center text-lg"><i class="fas fa-chart-line"></i></div>
            <div><p class="text-xs text-orange-800 font-semibold">Total Peminjaman</p><p class="text-xl font-bold text-orange-900">{{ $totalPeminjaman }}</p></div>
        </div>
        <div class="bg-red-50 border border-red-200 p-4 rounded-xl shadow-sm flex items-center gap-4">
            <div class="w-10 h-10 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-lg"><i class="fas fa-money-bill-wave"></i></div>
            <div><p class="text-xs text-red-800 font-semibold">Total Denda</p><p class="text-xl font-bold text-red-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p></div>
        </div>
    </div>

    {{-- Area Grafik --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">📈 Tren Peminjaman (7 Hari Terakhir)</h3>
            <canvas id="chartPeminjaman"></canvas>
        </div>
        <div class="bg-white p-4 rounded-xl shadow-sm border border-gray-100">
            <h3 class="font-bold text-gray-800 mb-4">📊 Tren Kunjungan Baca (7 Hari Terakhir)</h3>
            <canvas id="chartKunjungan"></canvas>
        </div>
    </div>

    {{-- Script Chart JS --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // Grafik Peminjaman (Garis)
        const ctxPeminjaman = document.getElementById('chartPeminjaman').getContext('2d');
        new Chart(ctxPeminjaman, {
            type: 'line',
            data: {
                labels: {!! json_encode($grafikPeminjaman->pluck('tanggal')) !!},
                datasets: [{
                    label: 'Buku Dipinjam',
                    data: {!! json_encode($grafikPeminjaman->pluck('total')) !!},
                    borderColor: '#16a34a', /* Warna Hijau */
                    backgroundColor: 'rgba(22, 163, 74, 0.1)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });

        // Grafik Kunjungan (Batang)
        const ctxKunjungan = document.getElementById('chartKunjungan').getContext('2d');
        new Chart(ctxKunjungan, {
            type: 'bar',
            data: {
                labels: {!! json_encode($grafikKunjungan->pluck('tgl')) !!},
                datasets: [{
                    label: 'Pengunjung',
                    data: {!! json_encode($grafikKunjungan->pluck('total')) !!},
                    backgroundColor: '#10b981', /* Warna Hijau Muda */
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 1 } } }
            }
        });
    </script>
@endsection