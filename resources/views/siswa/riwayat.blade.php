@extends('layouts.siswa')

@section('title', 'Riwayat Pinjam - Perpustakaan Digital')

@section('content')
    <div class="mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-2">
        <div>
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Pinjam</h2>
            <p class="text-gray-500 text-sm mt-1">Daftar buku yang pernah Anda pinjam.</p>
        </div>
        <div class="inline-flex items-center gap-2 bg-green-50 border border-green-200 px-3 py-1.5 rounded-full text-xs text-green-700 font-medium">
            <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
            Pembaruan Real-Time Aktif
        </div>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden" id="riwayat-card">
        <div id="riwayat-empty" class="{{ $loans->isEmpty() ? '' : 'hidden' }} p-10 text-center text-gray-500">
            <i class="fas fa-history text-4xl mb-3 text-gray-300"></i>
            <p>Belum ada riwayat peminjaman.</p>
        </div>

        <div id="riwayat-table-wrapper" class="{{ $loans->isEmpty() ? 'hidden' : '' }} overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="p-4 font-semibold">Buku</th>
                        <th class="p-4 font-semibold">Tgl Pinjam</th>
                        <th class="p-4 font-semibold">Jatuh Tempo</th>
                        <th class="p-4 font-semibold">Status</th>
                    </tr>
                </thead>
                <tbody id="riwayat-tbody">
                    @foreach($loans as $loan)
                        <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                            <td class="p-4">
                                <strong class="text-gray-800 block">{{ $loan->book->title ?? 'Buku Terhapus' }}</strong>
                                <span class="text-xs text-gray-500">{{ $loan->book->author ?? '-' }}</span>
                            </td>
                            <td class="p-4 text-gray-600">{{ \Carbon\Carbon::parse($loan->loan_date)->translatedFormat('d M Y') }}</td>
                            <td class="p-4 text-gray-600">{{ \Carbon\Carbon::parse($loan->due_date)->translatedFormat('d M Y') }}</td>
                            <td class="p-4">
                                @if($loan->status == 'menunggu')
                                    <span class="bg-yellow-100 text-yellow-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">⏳ Menunggu</span>
                                @elseif($loan->status == 'dipinjam')
                                    <span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">📖 Dipinjam</span>
                                @elseif($loan->status == 'dikembalikan')
                                    <span class="bg-green-100 text-green-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">✅ Dikembalikan</span>
                                @else
                                    <span class="bg-red-100 text-red-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">❌ Ditolak</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Script Real-Time Auto-Refresh Riwayat --}}
    <script>
        let lastRiwayatJson = '';

        function renderRiwayatTable(loans) {
            const emptyEl = document.getElementById('riwayat-empty');
            const wrapperEl = document.getElementById('riwayat-table-wrapper');
            const tbody = document.getElementById('riwayat-tbody');

            if (!loans || loans.length === 0) {
                emptyEl.classList.remove('hidden');
                wrapperEl.classList.add('hidden');
                return;
            }

            emptyEl.classList.add('hidden');
            wrapperEl.classList.remove('hidden');

            let html = '';
            loans.forEach(loan => {
                let badge = '';
                if (loan.status === 'menunggu') {
                    badge = '<span class="bg-yellow-100 text-yellow-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">⏳ Menunggu</span>';
                } else if (loan.status === 'dipinjam') {
                    badge = '<span class="bg-blue-100 text-blue-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">📖 Dipinjam</span>';
                } else if (loan.status === 'dikembalikan') {
                    badge = '<span class="bg-green-100 text-green-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">✅ Dikembalikan</span>';
                } else {
                    badge = '<span class="bg-red-100 text-red-800 px-2.5 py-1 rounded-full text-xs font-semibold inline-flex items-center gap-1">❌ Ditolak</span>';
                }

                html += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                        <td class="p-4">
                            <strong class="text-gray-800 block">${loan.title}</strong>
                            <span class="text-xs text-gray-500">${loan.author}</span>
                        </td>
                        <td class="p-4 text-gray-600">${loan.loan_date}</td>
                        <td class="p-4 text-gray-600">${loan.due_date}</td>
                        <td class="p-4">${badge}</td>
                    </tr>
                `;
            });

            tbody.innerHTML = html;
        }

        function pollRiwayatSiswa() {
            fetch("{{ route('siswa.api.riwayat') }}")
                .then(res => res.json())
                .then(data => {
                    const currentJson = JSON.stringify(data.loans);
                    if (currentJson !== lastRiwayatJson) {
                        lastRiwayatJson = currentJson;
                        renderRiwayatTable(data.loans);
                    }
                })
                .catch(err => console.error('Error poll riwayat:', err));
        }

        // Jalankan polling setiap 3 detik
        setInterval(pollRiwayatSiswa, 3000);
    </script>
@endsection
