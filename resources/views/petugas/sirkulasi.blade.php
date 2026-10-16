<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sirkulasi - Perpustakaan Digital SDN Pengampon 3</title>
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

        {{-- Tombol Kembali --}}
        <a href="{{ route('petugas.dashboard') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Kembali ke Dashboard
        </a>

        {{-- Pesan Sukses/Error --}}
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

        {{-- Judul --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-2">📋 Sirkulasi Peminjaman</h2>
            <p class="text-gray-600">Kelola peminjaman dan pengembalian buku.</p>
        </div>

        {{-- Statistik --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-yellow-100 p-4 rounded-lg">
                <p class="text-yellow-800 font-semibold">⏳ Menunggu Konfirmasi</p>
                <p class="text-3xl font-bold text-yellow-900">{{ $totalMenunggu }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg">
                <p class="text-blue-800 font-semibold">📚 Sedang Dipinjam</p>
                <p class="text-3xl font-bold text-blue-900">{{ $totalDipinjam }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg">
                <p class="text-green-800 font-semibold">✅ Sudah Dikembalikan</p>
                <p class="text-3xl font-bold text-green-900">{{ $totalDikembalikan }}</p>
            </div>
        </div>

        {{-- Tabel Peminjaman --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 bg-gray-50 border-b">
                <h3 class="font-bold text-gray-800">Daftar Peminjaman</h3>
            </div>

            @if($loans->isEmpty())
                <div class="p-6 text-center text-gray-500">
                    Belum ada peminjaman.
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-100">
                            <tr>
                                <th class="px-3 py-2 text-left text-sm font-semibold">No</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold">Peminjam</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold">Buku</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold">Tgl Pinjam</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold">Jatuh Tempo</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold">Status</th>
                                <th class="px-3 py-2 text-left text-sm font-semibold">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($loans as $index => $loan)
                                <tr class="border-b hover:bg-gray-50">
                                    <td class="px-3 py-2 text-sm">{{ $index + 1 }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        <strong>{{ $loan->user->name }}</strong><br>
                                        <span class="text-xs text-gray-500">
                                            {{ $loan->user->nisn ?? $loan->user->nik }}
                                        </span>
                                    </td>
                                    <td class="px-3 py-2 text-sm">
                                        <strong>{{ $loan->book->title }}</strong><br>
                                        <span class="text-xs text-gray-500">{{ $loan->book->code }}</span>
                                    </td>
                                    <td class="px-3 py-2 text-sm">{{ $loan->loan_date }}</td>
                                    <td class="px-3 py-2 text-sm">
                                        {{ $loan->due_date }}
                                        @if($loan->status === 'dipinjam' && \Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($loan->due_date)))
                                            <br><span class="text-xs text-red-600 font-bold">⚠️ TERLAMBAT!</span>
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-sm">
                                        @if($loan->status === 'menunggu')
                                            <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">
                                                ⏳ Menunggu
                                            </span>
                                        @elseif($loan->status === 'dipinjam')
                                            <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">
                                                📚 Dipinjam
                                            </span>
                                        @elseif($loan->status === 'dikembalikan')
                                            <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs">
                                                ✅ Dikembalikan
                                            </span>
                                            @if($loan->fine_amount > 0)
                                                <br>
                                                <span class="text-xs text-red-600 font-bold">
                                                    💰 Denda: Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                                </span>
                                                <br>
                                                @if($loan->payment_status === 'belum_lunas')
                                                    <span class="text-xs bg-red-100 text-red-800 px-1 rounded">Belum Lunas</span>
                                                @else
                                                    <span class="text-xs bg-green-100 text-green-800 px-1 rounded">Lunas</span>
                                                @endif
                                            @endif
                                        @endif
                                    </td>
                                    <td class="px-3 py-2 text-sm">
                                        @if($loan->status === 'menunggu')
                                            <div class="flex gap-1">
                                                <form method="POST" action="{{ route('petugas.setujui', $loan->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            class="bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs">
                                                        ✅ Setujui
                                                    </button>
                                                </form>
                                                <form method="POST" action="{{ route('petugas.tolak', $loan->id) }}">
                                                    @csrf
                                                    <button type="submit"
                                                            onclick="return confirm('Yakin tolak peminjaman ini?')"
                                                            class="bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs">
                                                        ❌ Tolak
                                                    </button>
                                                </form>
                                            </div>
                                        @elseif($loan->status === 'dipinjam')
                                            <form method="POST" action="{{ route('petugas.kembalikan', $loan->id) }}">
                                                @csrf
                                                <button type="submit"
                                                        onclick="return confirm('Proses pengembalian buku ini?')"
                                                        class="bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded text-xs">
                                                    📗 Kembalikan
                                                </button>
                                            </form>
                                        @elseif($loan->status === 'dikembalikan' && $loan->payment_status === 'belum_lunas')
                                            <form method="POST" action="{{ route('petugas.bebaskanDenda', $loan->id) }}">
                                                @csrf
                                                <input type="hidden" name="reason" id="reason-{{ $loan->id }}" value="">
                                                <button type="submit"
                                                        onclick="bebaskanDenda(event, {{ $loan->id }})"
                                                        class="bg-yellow-500 hover:bg-yellow-600 text-white px-2 py-1 rounded text-xs">
                                                    💸 Bebaskan Denda
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-xs text-gray-400">-</span>
                                        @endif
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

    {{-- ===== SCRIPT UNTUK BEBAS DENDA ===== --}}
    <script>
        function bebaskanDenda(event, loanId) {
            event.preventDefault();
            const reason = prompt('Masukkan alasan bebas denda (min. 5 karakter):');
            if (reason && reason.length >= 5) {
                document.getElementById('reason-' + loanId).value = reason;
                event.target.closest('form').submit();
            } else if (reason !== null) {
                alert('Alasan minimal 5 karakter!');
            }
        }
    </script>

</body>
</html>