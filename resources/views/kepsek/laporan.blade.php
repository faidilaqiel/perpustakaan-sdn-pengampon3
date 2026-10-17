<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Perpustakaan - SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- TAMBAHAN BARU: Library Chart.js untuk Grafik -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- ===== HEADER ===== --}}
    <header class="bg-purple-600 text-white p-4 shadow-lg">
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
                <form method="POST" action="{{ route('logout') }}" class="mt-1">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-3 py-1 rounded text-sm">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- ===== KONTEN ===== --}}
    <div class="container mx-auto p-6">

        {{-- Judul & Filter --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">📊 Laporan Perpustakaan</h2>
            <p class="text-gray-600 mb-4">Statistik dan laporan aktivitas perpustakaan.</p>

            {{-- Form Filter + Tombol Cetak PDF --}}
            <div class="flex gap-2 items-center flex-wrap">
                <form method="GET" action="{{ route('kepsek.laporan') }}" class="flex gap-2 items-center flex-wrap">
                    <label class="font-semibold text-gray-700">Periode:</label>
                    <select name="periode" class="border border-gray-300 rounded-lg px-4 py-2">
                        <option value="semua" {{ $periode === 'semua' ? 'selected' : '' }}>Semua Waktu</option>
                        <option value="bulan_ini" {{ $periode === 'bulan_ini' ? 'selected' : '' }}>Bulan Ini</option>
                        <option value="bulan_lalu" {{ $periode === 'bulan_lalu' ? 'selected' : '' }}>Bulan Lalu</option>
                    </select>
                    <button type="submit" class="bg-purple-600 hover:bg-purple-700 text-white px-4 py-2 rounded-lg">
                        Tampilkan
                    </button>
                </form>

                {{-- Tombol Cetak PDF --}}
                <a href="{{ route('kepsek.cetakPdf', ['periode' => $periode]) }}"
                   target="_blank"
                   class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded-lg flex items-center gap-2">
                    📄 Cetak PDF
                </a>
            </div>
        </div>

        {{-- Statistik Utama (Baris 1) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-blue-100 p-4 rounded-lg">
                <p class="text-blue-800 font-semibold">📚 Total Buku</p>
                <p class="text-3xl font-bold text-blue-900">{{ $totalBuku }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <p class="text-green-800 font-semibold">👥 Total Anggota</p>
                <p class="text-3xl font-bold text-green-900">{{ $totalAnggota }}</p>
            </div>
            <div class="bg-yellow-100 p-4 rounded-lg">
                <p class="text-yellow-800 font-semibold">📂 Total Kategori</p>
                <p class="text-3xl font-bold text-yellow-900">{{ $totalKategori }}</p>
            </div>
        </div>

        {{-- Statistik Utama (Baris 2) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-purple-100 p-4 rounded-lg">
                <p class="text-purple-800 font-semibold">📖 Sedang Dipinjam</p>
                <p class="text-3xl font-bold text-purple-900">{{ $sedangDipinjam }}</p>
            </div>
            <div class="bg-orange-100 p-4 rounded-lg">
                <p class="text-orange-800 font-semibold">📈 Total Peminjaman</p>
                <p class="text-3xl font-bold text-orange-900">{{ $totalPeminjaman }}</p>
            </div>
            <div class="bg-red-100 p-4 rounded-lg">
                <p class="text-red-800 font-semibold">💰 Total Denda (Lunas)</p>
                <p class="text-3xl font-bold text-red-900">Rp {{ number_format($totalDenda, 0, ',', '.') }}</p>
            </div>
        </div>

        {{-- ===== TAMBAHAN BARU: BAGIAN GRAFIK ===== --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
            
            <!-- Grafik Garis: Peminjaman -->
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="font-bold text-gray-700 mb-4">📈 Tren Peminjaman (7 Hari Terakhir)</h3>
                <canvas id="chartPeminjaman"></canvas>
            </div>

            <!-- Grafik Batang: Kunjungan -->
            <div class="bg-white p-4 rounded-lg shadow-lg">
                <h3 class="font-bold text-gray-700 mb-4">📊 Tren Kunjungan Baca (7 Hari Terakhir)</h3>
                <canvas id="chartKunjungan"></canvas>
            </div>
            
        </div>
        {{-- ===== AKHIR BAGIAN GRAFIK ===== --}}

        {{-- Buku Terpopuler --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h3 class="text-xl font-bold text-gray-800 mb-4">🏆 Buku Terpopuler</h3>

            @if($bukuTerpopuler->isEmpty())
                <p class="text-gray-500">Belum ada data peminjaman.</p>
            @else
                <div class="space-y-2">
                    @foreach($bukuTerpopuler as $index => $item)
                        <div class="flex items-center gap-3 p-3 bg-gray-50 rounded-lg">
                            <span class="w-8 h-8 bg-purple-500 text-white rounded-full flex items-center justify-center font-bold">
                                {{ $index + 1 }}
                            </span>
                            <div class="flex-1">
                                <p class="font-semibold">{{ $item->book->title ?? 'Buku tidak ditemukan' }}</p>
                                <p class="text-sm text-gray-500">{{ $item->book->author ?? '-' }}</p>
                            </div>
                            <span class="bg-blue-100 text-blue-800 px-3 py-1 rounded text-sm font-bold">
                                {{ $item->total }} kali dipinjam
                            </span>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        {{-- Buku Sedang Dipinjam --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="text-xl font-bold text-gray-800">📖 Buku Sedang Dipinjam</h3>
                <p class="text-sm text-gray-600">Daftar buku yang belum dikembalikan.</p>
            </div>

            @if($bukuDipinjam->isEmpty())
                <div class="p-6 text-center text-gray-500">
                    Tidak ada buku yang sedang dipinjam.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-4 py-2 text-left text-sm font-semibold">No</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Peminjam</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Buku</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Tgl Pinjam</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Jatuh Tempo</th>
                                <th class="px-4 py-2 text-left text-sm font-semibold">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($bukuDipinjam as $index => $loan)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-4 py-2 text-sm">{{ $index + 1 }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        <strong>{{ $loan->user->name }}</strong><br>
                                        <span class="text-xs text-gray-500">
                                            {{ $loan->user->nisn ?? $loan->user->nip ?? '-' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <strong>{{ $loan->book->title }}</strong><br>
                                        <span class="text-xs text-gray-500">{{ $loan->book->code ?? '-' }}</span>
                                    </td>
                                    <td class="px-4 py-2 text-sm">{{ $loan->loan_date }}</td>
                                    <td class="px-4 py-2 text-sm">
                                        {{ $loan->due_date }}
                                        @if(\Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($loan->due_date)))
                                            <br><span class="text-xs text-red-600 font-bold">⚠️ TERLAMBAT!</span>
                                        @endif
                                    </td>
                                    <td class="px-4 py-2 text-sm">
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                            📚 Dipinjam
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

    </div>

    {{-- ===== FOOTER ===== --}}
    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

    {{-- ===== SCRIPT GRAFIK ===== --}}
    <script>
        // 1. Grafik Peminjaman (Garis)
        const ctxPeminjaman = document.getElementById('chartPeminjaman').getContext('2d');
        new Chart(ctxPeminjaman, {
            type: 'line',
            data: {
                labels: {!! json_encode($grafikPeminjaman->pluck('tanggal')) !!},
                datasets: [{
                    label: 'Buku Dipinjam',
                    data: {!! json_encode($grafikPeminjaman->pluck('total')) !!},
                    borderColor: 'rgba(59, 130, 246, 1)', 
                    backgroundColor: 'rgba(59, 130, 246, 0.2)',
                    borderWidth: 2,
                    fill: true,
                    tension: 0.3
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });

        // 2. Grafik Kunjungan (Batang) -> SUDAH DIPERBAIKI JADI 'tgl'
        const ctxKunjungan = document.getElementById('chartKunjungan').getContext('2d');
        new Chart(ctxKunjungan, {
            type: 'bar',
            data: {
                labels: {!! json_encode($grafikKunjungan->pluck('tgl')) !!},
                datasets: [{
                    label: 'Pengunjung',
                    data: {!! json_encode($grafikKunjungan->pluck('total')) !!},
                    backgroundColor: 'rgba(16, 185, 129, 0.8)', 
                    borderRadius: 5
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: { beginAtZero: true, ticks: { stepSize: 1 } }
                }
            }
        });
    </script>

</body>
</html>