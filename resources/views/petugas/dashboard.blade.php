@extends('layouts.app')

@section('title', 'Dashboard Petugas')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Dashboard</h2>
            <p class="text-sm text-gray-500 mt-1">Selamat datang di Perpustakaan Digital SDN Pengampon 3</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="hidden sm:inline-flex items-center gap-2 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full text-xs text-green-700 font-medium">
                <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                Real-Time Aktif
            </div>
            <div class="text-sm text-gray-500 bg-white px-3 py-1.5 rounded-lg border border-gray-200 shadow-sm">
                <i class="far fa-calendar-alt mr-2 text-green-600"></i> {{ \Carbon\Carbon::now()->translatedFormat('l, d F Y') }}
            </div>
        </div>
    </div>

    {{-- Statistik --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <a href="{{ route('petugas.kelolaBuku') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-green-200 transition cursor-pointer">
            <div class="w-12 h-12 bg-green-100 text-green-600 rounded-lg flex items-center justify-center text-2xl"><i class="fas fa-book"></i></div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Total Buku</p>
                <p class="text-2xl font-bold text-gray-800" id="stat-total-buku">{{ $totalBuku }}</p>
            </div>
        </a>
        <a href="{{ route('petugas.kelolaAnggota') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-blue-200 transition cursor-pointer">
            <div class="w-12 h-12 bg-blue-100 text-blue-600 rounded-lg flex items-center justify-center text-2xl"><i class="fas fa-users"></i></div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Anggota</p>
                <p class="text-2xl font-bold text-gray-800" id="stat-total-anggota">{{ $totalAnggota }}</p>
            </div>
        </a>
        <a href="{{ route('petugas.sirkulasi') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-yellow-200 transition cursor-pointer">
            <div class="w-12 h-12 bg-yellow-100 text-yellow-600 rounded-lg flex items-center justify-center text-2xl"><i class="fas fa-book-reader"></i></div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Sedang Dipinjam</p>
                <p class="text-2xl font-bold text-gray-800" id="stat-sedang-dipinjam">{{ $sedangDipinjam }}</p>
            </div>
        </a>
        <a href="{{ route('petugas.sirkulasi') }}" class="bg-white p-5 rounded-xl shadow-sm border border-gray-100 flex items-center gap-4 hover:shadow-md hover:border-red-200 transition cursor-pointer">
            <div class="w-12 h-12 bg-red-100 text-red-600 rounded-lg flex items-center justify-center text-2xl"><i class="fas fa-exclamation-triangle"></i></div>
            <div>
                <p class="text-xs text-gray-500 font-medium">Terlambat</p>
                <p class="text-2xl font-bold text-red-600" id="stat-terlambat">{{ $terlambat }}</p>
            </div>
        </a>
    </div>

    {{-- Grafik & Peminjaman Terbaru --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Grafik --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100 lg:col-span-2">
            <div class="flex justify-between items-center mb-6">
                <h3 class="font-bold text-gray-800">Statistik Peminjaman Buku</h3>
                <span class="text-xs bg-gray-100 text-gray-600 px-2.5 py-1 rounded-lg">7 Hari Terakhir</span>
            </div>
            <canvas id="chartDashboard"></canvas>
            
            {{-- Banner Quotes --}}
            <div class="mt-6 bg-green-50 rounded-lg p-4 flex items-center gap-4 border border-green-100">
                <i class="fas fa-leaf text-3xl text-green-500"></i>
                <div>
                    <p class="text-sm font-bold text-green-800">"Membaca adalah jendela dunia."</p>
                    <p class="text-xs text-green-600">Jadikan perpustakaan sebagai tempat tumbuh kembang ilmu.</p>
                </div>
            </div>
        </div>

        {{-- Peminjaman Terbaru --}}
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100">
            <div class="flex justify-between items-center mb-4">
                <h3 class="font-bold text-gray-800">Peminjaman Terbaru</h3>
                <a href="{{ route('petugas.sirkulasi') }}" class="text-xs font-semibold text-green-600 hover:underline">Lihat Semua</a>
            </div>
            <div class="space-y-4" id="list-peminjaman-terbaru">
                @forelse($peminjamanTerbaru as $loan)
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 overflow-hidden shrink-0">
                            @if($loan->user->photo)
                                <img src="{{ asset('photos/' . $loan->user->photo) }}" class="w-full h-full object-cover">
                            @else
                                <i class="fas fa-user"></i>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-gray-800 truncate">{{ $loan->user->name }}</p>
                            <p class="text-xs text-gray-500 truncate">{{ $loan->book->title ?? 'Buku Terhapus' }}</p>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="text-[10px] text-gray-400">{{ \Carbon\Carbon::parse($loan->created_at)->translatedFormat('d M') }}</p>
                        </div>
                    </div>
                @empty
                    <p class="text-sm text-gray-500 text-center">Belum ada data.</p>
                @endforelse
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        // 1. Script untuk Grafik Chart.js
        const ctx = document.getElementById('chartDashboard').getContext('2d');
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: {!! json_encode($grafikPeminjaman->pluck('tanggal')) !!},
                datasets: [{
                    label: 'Buku Dipinjam',
                    data: {!! json_encode($grafikPeminjaman->pluck('total')) !!},
                    backgroundColor: '#16a34a',
                    borderRadius: 5,
                    barThickness: 30
                }]
            },
            options: {
                responsive: true,
                plugins: { legend: { display: false } },
                scales: { y: { beginAtZero: true, ticks: { stepSize: 10 } } }
            }
        });

        // 2. Real-Time Auto-Refresh Statistik Dashboard Petugas
        function pollDashboardStats() {
            fetch("{{ route('petugas.api.dashboardStats') }}")
                .then(res => res.json())
                .then(data => {
                    const elBuku = document.getElementById('stat-total-buku');
                    const elAnggota = document.getElementById('stat-total-anggota');
                    const elSedang = document.getElementById('stat-sedang-dipinjam');
                    const elTerlambat = document.getElementById('stat-terlambat');

                    if (elBuku) elBuku.textContent = data.totalBuku;
                    if (elAnggota) elAnggota.textContent = data.totalAnggota;
                    if (elSedang) elSedang.textContent = data.sedangDipinjam;
                    if (elTerlambat) elTerlambat.textContent = data.terlambat;

                    const listContainer = document.getElementById('list-peminjaman-terbaru');
                    if (listContainer && data.peminjamanTerbaru && data.peminjamanTerbaru.length > 0) {
                        let html = '';
                        data.peminjamanTerbaru.forEach(item => {
                            let photoHtml = item.user_photo 
                                ? `<img src="/photos/${item.user_photo}" class="w-full h-full object-cover">`
                                : `<i class="fas fa-user"></i>`;

                            html += `
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-gray-200 rounded-full flex items-center justify-center text-gray-500 overflow-hidden shrink-0">
                                        ${photoHtml}
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-sm font-bold text-gray-800 truncate">${item.user_name}</p>
                                        <p class="text-xs text-gray-500 truncate">${item.book_title}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[10px] text-gray-400">${item.created_at}</p>
                                    </div>
                                </div>
                            `;
                        });
                        listContainer.innerHTML = html;
                    }
                })
                .catch(err => console.error('Error poll dashboard stats:', err));
        }

        // Jalankan polling setiap 4 detik
        setInterval(pollDashboardStats, 4000);
    </script>
@endsection
