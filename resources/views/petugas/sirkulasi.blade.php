<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sirkulasi - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- HEADER --}}
    <header class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-full p-1">
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

    {{-- KONTEN --}}
    <div class="container mx-auto p-6">

        <a href="{{ route('petugas.dashboard') }}" class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Dashboard
        </a>

        {{-- Notifikasi Sukses/Error --}}
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

        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2 flex items-center gap-2">
                📋 Sirkulasi Peminjaman
                @if($jumlahPengajuan > 0)
                    <span class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse">
                        {{ $jumlahPengajuan }} Baru
                    </span>
                @endif
            </h2>
            <p class="text-gray-600">Kelola peminjaman dan pengembalian buku.</p>
        </div>

        {{-- Kotak Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-100 p-4 rounded-lg">
                <p class="text-yellow-800 font-semibold">⏳ Menunggu Konfirmasi</p>
                <p class="text-3xl font-bold text-yellow-900">{{ $menunggu }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg">
                <p class="text-blue-800 font-semibold">📖 Sedang Dipinjam</p>
                <p class="text-3xl font-bold text-blue-900">{{ $dipinjam }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <p class="text-green-800 font-semibold">✅ Sudah Dikembalikan</p>
                <p class="text-3xl font-bold text-green-900">{{ $dikembalikan }}</p>
            </div>
        </div>

        {{-- Tabel Peminjaman --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="font-bold text-gray-800">Daftar Peminjaman</h3>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100 text-gray-700 text-sm">
                            <th class="p-3 border-b">No</th>
                            <th class="p-3 border-b">Peminjam</th>
                            <th class="p-3 border-b">Buku</th>
                            <th class="p-3 border-b">Tgl Pinjam</th>
                            <th class="p-3 border-b">Jatuh Tempo</th>
                            <th class="p-3 border-b">Status</th>
                            <th class="p-3 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $index => $loan)
                            <tr class="border-b hover:bg-gray-50 text-sm">
                                <td class="p-3">{{ $index + 1 }}</td>
                                <td class="p-3">
                                    <strong>{{ $loan->user->name ?? 'User Terhapus' }}</strong><br>
                                    <span class="text-xs text-gray-500">{{ $loan->user->nisn ?? $loan->user->nip ?? '-' }}</span>
                                </td>
                                <td class="p-3">
                                    <strong>{{ $loan->book->title ?? 'Buku Terhapus' }}</strong><br>
                                    <span class="text-xs text-gray-500">{{ $loan->book->code ?? '-' }}</span>
                                </td>
                                <td class="p-3">
                                    {{-- Ini akan otomatis menampilkan tanggal sesuai server (misal 18 Oct 2026) --}}
                                    {{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}
                                </td>
                                <td class="p-3">
                                    {{ \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d M Y') }}
                                    @if($loan->status == 'dipinjam' && \Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($loan->due_date)))
                                        <br><span class="text-xs text-red-600 font-bold">⚠️ TERLAMBAT!</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($loan->status == 'menunggu')
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">⏳ Menunggu</span>
                                    @elseif($loan->status == 'dipinjam')
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">📖 Dipinjam</span>
                                    @elseif($loan->status == 'dikembalikan')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">✅ Dikembalikan</span>
                                    @else
                                        {{-- Ini untuk status 'batal' --}}
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs">❌ Ditolak</span>
                                    @endif
                                </td>
                                <td class="p-3">
                                    @if($loan->status == 'menunggu')
                                        <div class="flex gap-1">
                                            <form method="POST" action="{{ route('petugas.setujui', $loan->id) }}">
                                                @csrf
                                                <button type="submit" class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">Setujui</button>
                                            </form>
                                            <form method="POST" action="{{ route('petugas.tolak', $loan->id) }}">
                                                @csrf
                                                <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">Tolak</button>
                                            </form>
                                        </div>
                                    @elseif($loan->status == 'dipinjam')
                                        <form method="POST" action="{{ route('petugas.kembalikan', $loan->id) }}">
                                            @csrf
                                            <button type="submit" class="bg-blue-500 hover:bg-blue-600 text-white px-3 py-1 rounded text-xs">Kembalikan</button>
                                        </form>
                                    @else
                                        -
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="p-6 text-center text-gray-500">Belum ada data peminjaman.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>