<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sirkulasi - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 min-h-screen">

    {{-- HEADER RESPONSIF --}}
    <header class="bg-green-600 text-white p-4 shadow-lg">
        <div class="container mx-auto flex flex-col md:flex-row justify-between items-center gap-3 text-center md:text-left">
            <div class="flex flex-col md:flex-row items-center gap-3">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-12 h-12 object-contain bg-white rounded-full p-1">
                <div>
                    <h1 class="text-xl font-bold">Perpustakaan Digital</h1>
                    <p class="text-sm">SDN Pengampon 3</p>
                </div>
            </div>
            <div class="mt-2 md:mt-0">
                <p class="font-semibold">{{ Auth::user()->name }}</p>
                <p class="text-sm">Role: {{ Auth::user()->role }}</p>
                <form method="POST" action="{{ route('logout') }}" class="mt-2">
                    @csrf
                    <button type="submit" class="bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-lg text-sm w-full md:w-auto font-semibold shadow">
                        Logout
                    </button>
                </form>
            </div>
        </div>
    </header>

    {{-- KONTEN --}}
    <div class="container mx-auto p-4 md:p-6">

        <a href="{{ route('petugas.dashboard') }}" class="inline-block mb-4 text-green-600 hover:text-green-800 font-semibold text-sm">
            ← Kembali ke Dashboard
        </a>

        {{-- Notifikasi --}}
        @if(session('success'))
            <div class="bg-green-100 border border-green-400 text-green-800 px-4 py-3 rounded mb-4 text-sm">
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        {{-- JUDUL & BADGE --}}
        <div class="bg-white rounded-lg shadow-lg p-4 md:p-6 mb-6">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-2 flex flex-wrap items-center gap-2">
                📋 Sirkulasi Peminjaman
                <span id="badge-notif" class="bg-red-500 text-white text-xs font-bold px-2 py-1 rounded-full animate-pulse {{ $jumlahPengajuan > 0 ? '' : 'hidden' }}">
                    {{ $jumlahPengajuan }} Baru
                </span>
            </h2>
            <p class="text-gray-600 text-sm">Kelola peminjaman dan pengembalian buku.</p>
        </div>

        {{-- STATISTIK (Kotak) --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-3 md:gap-4 mb-6">
            <div class="bg-yellow-100 p-4 rounded-lg shadow-sm">
                <p class="text-yellow-800 font-semibold text-sm">⏳ Menunggu Konfirmasi</p>
                <p class="text-2xl md:text-3xl font-bold text-yellow-900" id="stat-menunggu">{{ $menunggu }}</p>
            </div>
            <div class="bg-blue-100 p-4 rounded-lg shadow-sm">
                <p class="text-blue-800 font-semibold text-sm">📖 Sedang Dipinjam</p>
                <p class="text-2xl md:text-3xl font-bold text-blue-900">{{ $dipinjam }}</p>
            </div>
            <div class="bg-green-100 p-4 rounded-lg shadow-sm">
                <p class="text-green-800 font-semibold text-sm">✅ Sudah Dikembalikan</p>
                <p class="text-2xl md:text-3xl font-bold text-green-900">{{ $dikembalikan }}</p>
            </div>
        </div>

        {{-- DAFTAR PEMINJAMAN (RESPONSIF) --}}
        <div class="bg-white rounded-lg shadow-lg overflow-hidden">
            <div class="p-4 border-b bg-gray-50">
                <h3 class="font-bold text-gray-800">Daftar Peminjaman</h3>
            </div>
            
            {{-- TAMPILAN UNTUK LAPTOP (Tabel) --}}
            <div class="hidden md:block overflow-x-auto">
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
                    <tbody id="table-body-desktop">
                        {{-- Diisi JavaScript --}}
                    </tbody>
                </table>
            </div>

            {{-- TAMPILAN UNTUK HP (Kartu) --}}
            <div class="block md:hidden p-4 space-y-4" id="table-body-mobile">
                {{-- Diisi JavaScript --}}
            </div>
        </div>

    </div>

    <footer class="text-center text-sm text-gray-500 py-4 mt-6">
        © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
    </footer>

    {{-- SCRIPT AUTO-REFRESH & RENDER GANDA (HP & LAPTOP) --}}
    <script>
        function renderTable(data) {
            const tbodyDesktop = document.getElementById('table-body-desktop');
            const tbodyMobile = document.getElementById('table-body-mobile');
            
            tbodyDesktop.innerHTML = ''; 
            tbodyMobile.innerHTML = '';

            if (data.loans.length === 0) {
                tbodyDesktop.innerHTML = `<tr><td colspan="7" class="p-6 text-center text-gray-500">Belum ada data peminjaman.</td></tr>`;
                tbodyMobile.innerHTML = `<div class="text-center text-gray-500 py-6">Belum ada data peminjaman.</div>`;
                return;
            }

            data.loans.forEach((loan, index) => {
                // Status Badge
                let statusBadge = '';
                if (loan.status === 'menunggu') {
                    statusBadge = `<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">⏳ Menunggu</span>`;
                } else if (loan.status === 'dipinjam') {
                    statusBadge = `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">📖 Dipinjam</span>`;
                } else if (loan.status === 'dikembalikan') {
                    statusBadge = `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✅ Dikembalikan</span>`;
                } else {
                    statusBadge = `<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Ditolak</span>`;
                }

                // Tombol Aksi
                let aksi = '';
                if (loan.status === 'menunggu') {
                    aksi = `
                        <div class="flex flex-col md:flex-row gap-1 md:gap-2 w-full">
                            <form method="POST" action="/petugas/setujui/${loan.id}" class="w-full md:w-auto">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white px-2 py-2 md:py-1 rounded text-xs font-semibold shadow">Setujui</button>
                            </form>
                            <form method="POST" action="/petugas/tolak/${loan.id}" class="w-full md:w-auto">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-2 py-2 md:py-1 rounded text-xs font-semibold shadow">Tolak</button>
                            </form>
                        </div>`;
                } else if (loan.status === 'dipinjam') {
                    aksi = `
                        <form method="POST" action="/petugas/kembalikan/${loan.id}" class="w-full md:w-auto">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-3 py-2 md:py-1 rounded text-xs font-semibold shadow">Kembalikan</button>
                        </form>`;
                } else {
                    aksi = '<span class="text-gray-400 text-xs">-</span>';
                }

                let lateLabel = loan.is_late ? `<br><span class="text-xs text-red-600 font-bold">⚠️ TERLAMBAT!</span>` : '';

                // Render Desktop (Tabel)
                tbodyDesktop.innerHTML += `
                    <tr class="border-b hover:bg-gray-50 text-sm">
                        <td class="p-3">${index + 1}</td>
                        <td class="p-3"><strong>${loan.user_name}</strong><br><span class="text-xs text-gray-500">${loan.user_id}</span></td>
                        <td class="p-3"><strong>${loan.book_title}</strong><br><span class="text-xs text-gray-500">${loan.book_code}</span></td>
                        <td class="p-3">${loan.loan_date}</td>
                        <td class="p-3">${loan.due_date} ${lateLabel}</td>
                        <td class="p-3">${statusBadge}</td>
                        <td class="p-3">${aksi}</td>
                    </tr>
                `;

                // Render Mobile (Kartu)
                tbodyMobile.innerHTML += `
                    <div class="border rounded-lg p-3 shadow-sm bg-gray-50">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <strong class="text-gray-800 text-sm block">${loan.user_name}</strong>
                                <span class="text-xs text-gray-500">${loan.user_id}</span>
                            </div>
                            <div>${statusBadge}</div>
                        </div>
                        <div class="mb-2">
                            <span class="text-xs text-gray-600 block">Buku:</span>
                            <strong class="text-sm text-gray-800">${loan.book_title}</strong>
                            <span class="text-xs text-gray-500 block">${loan.book_code}</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-500 mb-3">
                            <span>Pinjam: ${loan.loan_date}</span>
                            <span>Tempo: ${loan.due_date}</span>
                        </div>
                        ${lateLabel ? `<div class="text-xs text-red-600 font-bold mb-2">⚠️ TERLAMBAT!</div>` : ''}
                        <div class="mt-2 border-t pt-2">
                            ${aksi}
                        </div>
                    </div>
                `;
            });
        }

        function fetchLatestData() {
            fetch("{{ route('petugas.api.sirkulasi') }}")
                .then(response => response.json())
                .then(data => {
                    renderTable(data);
                    const badge = document.getElementById('badge-notif');
                    const stat = document.getElementById('stat-menunggu');
                    
                    stat.innerText = data.menunggu;
                    if (data.menunggu > 0) {
                        badge.innerText = data.menunggu + ' Baru';
                        badge.classList.remove('hidden');
                    } else {
                        badge.classList.add('hidden');
                    }
                })
                .catch(error => console.error('Error:', error));
        }

        setInterval(fetchLatestData, 3000);
        document.addEventListener('DOMContentLoaded', fetchLatestData);
    </script>

</body>
</html>