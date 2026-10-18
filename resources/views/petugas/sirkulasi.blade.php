@extends('layouts.app')

@section('title', 'Sirkulasi - Perpustakaan Digital')

@section('content')
    {{-- Header Halaman --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex items-center gap-3">
        <h2 class="text-xl font-bold text-gray-800">Sirkulasi Peminjaman</h2>
        <span id="badge-notif" class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full animate-pulse {{ $jumlahPengajuan > 0 ? '' : 'hidden' }}">
            {{ $jumlahPengajuan }} Baru
        </span>
    </div>

    {{-- Notifikasi --}}
    @if(session('success'))
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6 rounded shadow-sm text-sm">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-6 rounded shadow-sm text-sm">
            {{ session('error') }}
        </div>
    @endif

    {{-- Statistik --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-yellow-50 border border-yellow-200 p-4 rounded-xl shadow-sm">
            <p class="text-yellow-800 font-semibold text-sm">⏳ Menunggu Konfirmasi</p>
            <p class="text-3xl font-bold text-yellow-900 mt-1" id="stat-menunggu">{{ $menunggu }}</p>
        </div>
        <div class="bg-blue-50 border border-blue-200 p-4 rounded-xl shadow-sm">
            <p class="text-blue-800 font-semibold text-sm">📖 Sedang Dipinjam</p>
            <p class="text-3xl font-bold text-blue-900 mt-1">{{ $dipinjam }}</p>
        </div>
        <div class="bg-green-50 border border-green-200 p-4 rounded-xl shadow-sm">
            <p class="text-green-800 font-semibold text-sm">✅ Sudah Dikembalikan</p>
            <p class="text-3xl font-bold text-green-900 mt-1">{{ $dikembalikan }}</p>
        </div>
    </div>

    {{-- Daftar Peminjaman --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50">
            <h3 class="font-bold text-gray-800">Daftar Peminjaman</h3>
        </div>
        
        {{-- Tabel Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="p-4 font-semibold">No</th>
                        <th class="p-4 font-semibold">Peminjam</th>
                        <th class="p-4 font-semibold">Buku</th>
                        <th class="p-4 font-semibold">Tgl Pinjam</th>
                        <th class="p-4 font-semibold">Jatuh Tempo</th>
                        <th class="p-4 font-semibold">Status</th>
                        <th class="p-4 font-semibold text-center">Aksi</th>
                    </tr>
                </thead>
                <tbody id="table-body-desktop">
                    {{-- Diisi JavaScript --}}
                </tbody>
            </table>
        </div>

        {{-- Tampilan HP (Kartu) --}}
        <div class="block md:hidden p-4 space-y-4" id="table-body-mobile">
            {{-- Diisi JavaScript --}}
        </div>
    </div>

    {{-- Script Auto-Refresh --}}
    <script>
        function renderTable(data) {
            const tbodyDesktop = document.getElementById('table-body-desktop');
            const tbodyMobile = document.getElementById('table-body-mobile');
            tbodyDesktop.innerHTML = ''; tbodyMobile.innerHTML = '';

            if (data.loans.length === 0) {
                tbodyDesktop.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-500">Belum ada data peminjaman.</td></tr>`;
                tbodyMobile.innerHTML = `<div class="text-center text-gray-500 py-6">Belum ada data peminjaman.</div>`;
                return;
            }

            data.loans.forEach((loan, index) => {
                let statusBadge = '';
                if (loan.status === 'menunggu') statusBadge = `<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">⏳ Menunggu</span>`;
                else if (loan.status === 'dipinjam') statusBadge = `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">📖 Dipinjam</span>`;
                else if (loan.status === 'dikembalikan') statusBadge = `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✅ Dikembalikan</span>`;
                else statusBadge = `<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Ditolak</span>`;

                let aksi = '';
                if (loan.status === 'menunggu') {
                    aksi = `
                        <div class="flex flex-col md:flex-row gap-1 md:gap-2 w-full justify-center">
                            <form method="POST" action="/petugas/setujui/${loan.id}" class="w-full md:w-auto">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="w-full bg-green-500 hover:bg-green-600 text-white px-2 py-1.5 rounded text-xs font-semibold shadow">Setujui</button>
                            </form>
                            <form method="POST" action="/petugas/tolak/${loan.id}" class="w-full md:w-auto">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="w-full bg-red-500 hover:bg-red-600 text-white px-2 py-1.5 rounded text-xs font-semibold shadow">Tolak</button>
                            </form>
                        </div>`;
                } else if (loan.status === 'dipinjam') {
                    aksi = `
                        <form method="POST" action="/petugas/kembalikan/${loan.id}" class="w-full md:w-auto text-center">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold shadow">Kembalikan</button>
                        </form>`;
                } else {
                    aksi = '<span class="text-gray-400 text-xs text-center block">-</span>';
                }

                let lateLabel = loan.is_late ? `<br><span class="text-xs text-red-600 font-bold">⚠️ TERLAMBAT!</span>` : '';

                // Desktop
                tbodyDesktop.innerHTML += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                        <td class="p-4 text-gray-500">${index + 1}</td>
                        <td class="p-4"><strong class="text-gray-800">${loan.user_name}</strong><br><span class="text-xs text-gray-500 font-mono">${loan.user_id}</span></td>
                        <td class="p-4"><strong class="text-gray-800">${loan.book_title}</strong><br><span class="text-xs text-gray-500 font-mono">${loan.book_code}</span></td>
                        <td class="p-4 text-gray-600">${loan.loan_date}</td>
                        <td class="p-4 text-gray-600">${loan.due_date} ${lateLabel}</td>
                        <td class="p-4">${statusBadge}</td>
                        <td class="p-4">${aksi}</td>
                    </tr>
                `;

                // Mobile
                tbodyMobile.innerHTML += `
                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm bg-gray-50">
                        <div class="flex justify-between items-start mb-3 border-b border-gray-200 pb-2">
                            <div>
                                <strong class="text-gray-800 text-sm block">${loan.user_name}</strong>
                                <span class="text-xs text-gray-500 font-mono">${loan.user_id}</span>
                            </div>
                            <div>${statusBadge}</div>
                        </div>
                        <div class="mb-3">
                            <span class="text-xs text-gray-500 block">Buku:</span>
                            <strong class="text-sm text-gray-800">${loan.book_title}</strong>
                            <span class="text-xs text-gray-500 font-mono block">${loan.book_code}</span>
                        </div>
                        <div class="flex justify-between text-xs text-gray-600 mb-2">
                            <span>Pinjam: ${loan.loan_date}</span>
                            <span>Tempo: ${loan.due_date}</span>
                        </div>
                        ${lateLabel ? `<div class="text-xs text-red-600 font-bold mb-3">⚠️ TERLAMBAT!</div>` : ''}
                        <div class="mt-2 pt-3 border-t border-gray-200">${aksi}</div>
                    </div>
                `;
            });
        }

        function fetchLatestData() {
            fetch("{{ route('petugas.api.sirkulasi') }}")
                .then(res => res.json())
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
                .catch(err => console.error(err));
        }

        setInterval(fetchLatestData, 3000);
        document.addEventListener('DOMContentLoaded', fetchLatestData);
    </script>
@endsection