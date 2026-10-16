<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hasil Scan - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- HEADER --}}
    <header class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex justify-between items-center">
            <div class="flex items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" class="w-12 h-12 object-contain bg-white rounded-full p-1">
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
    <div class="container mx-auto p-6 max-w-4xl">

        <a href="{{ route('petugas.scan') }}"
           class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold">
            ← Scan Lagi
        </a>

        {{-- PROFIL ANGGOTA --}}
        <div class="bg-white rounded-lg shadow-lg p-6 mb-6">
            <div class="flex items-center gap-4 flex-wrap">

                {{-- Foto --}}
                <div class="w-24 h-32 bg-red-100 rounded-lg flex items-center justify-center overflow-hidden border-2 border-green-500">
                    @if($user->photo)
                        <img src="{{ asset('photos/' . $user->photo) }}" class="w-full h-full object-cover">
                    @else
                        <span class="text-4xl font-bold text-green-800">
                            {{ strtoupper(substr($user->name, 0, 1)) }}
                        </span>
                    @endif
                </div>

                {{-- Info --}}
                <div class="flex-1">
                    <div class="inline-block bg-green-100 text-green-800 px-3 py-1 rounded text-sm font-bold mb-2">
                        @if($user->role === 'siswa')
                            🎓 SISWA
                        @else
                            👨‍🏫 GURU
                        @endif
                    </div>
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">{{ $user->name }}</h2>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-2 text-sm">
                        <div>
                            <span class="text-gray-500">
                                @if($user->role === 'siswa') NISN @else NIP @endif:
                            </span>
                            <strong class="font-mono">
                                @if($user->role === 'siswa') {{ $user->nisn }} @else {{ $user->nip }} @endif
                            </strong>
                        </div>
                        @if($user->role === 'siswa')
                        <div>
                            <span class="text-gray-500">Kelas:</span>
                            <strong>{{ $user->class }}</strong>
                        </div>
                        @endif
                        <div>
                            <span class="text-gray-500">Gender:</span>
                            <strong>{{ $user->gender === 'L' ? 'Laki-laki' : 'Perempuan' }}</strong>
                        </div>
                        <div>
                            <span class="text-gray-500">Status:</span>
                            <span class="bg-green-100 text-green-800 px-2 py-0.5 rounded text-xs">
                                ✅ {{ ucfirst($user->status) }}
                            </span>
                        </div>
                    </div>
                </div>

                {{-- QR Code Button --}}
                <div class="text-center">
                    <a href="{{ route('petugas.scan.qrCode', $user->id) }}"
                       class="inline-block bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded-lg text-sm mb-2">
                        📱 Lihat QR Code
                    </a>
                    <p class="text-xs text-gray-500">Klik untuk lihat & screenshot</p>
                </div>

            </div>
        </div>

        {{-- PEMINJAMAN AKTIF --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden mb-6">
            <div class="bg-yellow-100 p-4 border-b">
                <h3 class="text-xl font-bold text-yellow-800">📚 Peminjaman Aktif</h3>
                <p class="text-sm text-yellow-700">Buku yang sedang dipinjam.</p>
            </div>

            @if($peminjamanAktif->isEmpty())
                <div class="p-6 text-center text-gray-500">
                    <p class="text-3xl mb-2">✨</p>
                    <p>Tidak ada peminjaman aktif.</p>
                </div>
            @else
                <table class="w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Buku</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Tgl Pinjam</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Jatuh Tempo</th>
                            <th class="px-4 py-2 text-left text-sm font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($peminjamanAktif as $loan)
                            <tr class="border-b">
                                <td class="px-4 py-2 text-sm">
                                    <strong>{{ $loan->book->title }}</strong><br>
                                    <span class="text-xs text-gray-500">{{ $loan->book->code }}</span>
                                </td>
                                <td class="px-4 py-2 text-sm">{{ $loan->loan_date }}</td>
                                <td class="px-4 py-2 text-sm">
                                    {{ $loan->due_date }}
                                    @if(\Carbon\Carbon::now()->greaterThan(\Carbon\Carbon::parse($loan->due_date)))
                                        <br><span class="text-xs text-red-600 font-bold">⚠️ TERLAMBAT!</span>
                                    @endif
                                </td>
                                <td class="px-4 py-2 text-sm">
                                    @if($loan->status === 'dipinjam')
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs">📚 Dipinjam</span>
                                    @else
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs">⏳ Menunggu</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            @endif
        </div>

        {{-- RIWAYAT --}}
        @if($riwayat->isNotEmpty())
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="bg-green-100 p-4 border-b">
                <h3 class="text-xl font-bold text-green-800">📖 Riwayat Peminjaman (5 Terakhir)</h3>
            </div>
            <table class="w-full">
                <thead class="bg-gray-100">
                    <tr>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Buku</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Tgl Kembali</th>
                        <th class="px-4 py-2 text-left text-sm font-semibold">Denda</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($riwayat as $loan)
                        <tr class="border-b">
                            <td class="px-4 py-2 text-sm">{{ $loan->book->title }}</td>
                            <td class="px-4 py-2 text-sm">{{ $loan->return_date }}</td>
                            <td class="px-4 py-2 text-sm">
                                @if($loan->fine_amount > 0)
                                    <span class="text-red-600 font-bold">Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-green-600">Tidak ada denda</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @endif

    </div>

    <footer class="text-center text-sm text-gray-500 py-4">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

</body>
</html>