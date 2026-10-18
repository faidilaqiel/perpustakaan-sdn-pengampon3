@extends('layouts.siswa')

@section('title', 'Riwayat Pinjam - Perpustakaan Digital')

@section('content')
    <div class="mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Riwayat Pinjam</h2>
        <p class="text-gray-500 text-sm mt-1">Daftar buku yang pernah Anda pinjam.</p>
    </div>

    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        @if($loans->isEmpty())
            <div class="p-10 text-center text-gray-500">
                <i class="fas fa-history text-4xl mb-3 text-gray-300"></i>
                <p>Belum ada riwayat peminjaman.</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                            <th class="p-4 font-semibold">Buku</th>
                            <th class="p-4 font-semibold">Tgl Pinjam</th>
                            <th class="p-4 font-semibold">Jatuh Tempo</th>
                            <th class="p-4 font-semibold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
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
                                        <span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">⏳ Menunggu</span>
                                    @elseif($loan->status == 'dipinjam')
                                        <span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">📖 Dipinjam</span>
                                    @elseif($loan->status == 'dikembalikan')
                                        <span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✅ Dikembalikan</span>
                                    @else
                                        <span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Ditolak</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection