@extends('layouts.app')

@section('title', 'Dashboard Cetak Kartu Anggota - Perpustakaan Digital')

@section('content')
<div class="space-y-6">

    {{-- Header --}}
    <div class="bg-white rounded-xl shadow-xs p-5 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="flex items-center gap-2">
                <span class="w-8 h-8 rounded-lg bg-green-50 text-green-600 flex items-center justify-center text-base">
                    <i class="fas fa-id-card"></i>
                </span>
                <h2 class="text-xl font-bold text-gray-800">Dashboard Cetak Kartu Anggota</h2>
            </div>
            <p class="text-xs text-gray-500 mt-1">Cetak kartu anggota resmi perpustakaan dengan foto, QR Code, dan tanda tangan Kepala Sekolah.</p>
        </div>
        <div class="flex items-center gap-2">
            <button onclick="cetakTerpilih()" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2.5 rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="fas fa-print"></i> Cetak Kartu Terpilih (<span id="count-selected">0</span>)
            </button>
            <a href="{{ route('petugas.cetakKartuMassal', ['role' => request('role'), 'class' => request('class')]) }}" target="_blank" class="bg-green-600 hover:bg-green-700 text-white px-4 py-2.5 rounded-lg text-xs font-semibold shadow-xs transition flex items-center gap-1.5">
                <i class="fas fa-file-pdf"></i> Cetak Semua (Sesuai Filter)
            </a>
        </div>
    </div>

    {{-- Filter Pencarian & Kategori --}}
    <div class="bg-white rounded-xl shadow-xs p-4 border border-gray-100">
        <form method="GET" action="{{ route('petugas.cetakKartu') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3 items-end">
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Cari Nama / NISN / NIP</label>
                <div class="relative">
                    <i class="fas fa-search absolute left-3 top-3 text-gray-400 text-xs"></i>
                    <input type="text" name="q" value="{{ $keyword }}" placeholder="Ketik kata kunci..." class="w-full pl-8 pr-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">
                </div>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Filter Peran</label>
                <select name="role" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">
                    <option value="">Semua (Siswa & Guru)</option>
                    <option value="siswa" {{ $filterRole === 'siswa' ? 'selected' : '' }}>Siswa Saja</option>
                    <option value="guru" {{ $filterRole === 'guru' ? 'selected' : '' }}>Guru Saja</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-medium text-gray-600 mb-1">Filter Kelas</label>
                <select name="class" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-xs focus:ring-2 focus:ring-green-500 focus:outline-none">
                    <option value="">Semua Kelas</option>
                    @foreach($listKelas as $k)
                        <option value="{{ $k }}" {{ $filterClass == $k ? 'selected' : '' }}>Kelas {{ $k }}</option>
                    @endforeach
                </select>
            </div>
            <div class="flex gap-2">
                <button type="submit" class="flex-1 bg-green-600 hover:bg-green-700 text-white py-2 rounded-lg text-xs font-semibold transition">
                    <i class="fas fa-filter mr-1"></i> Terapkan
                </button>
                <a href="{{ route('petugas.cetakKartu') }}" class="px-3 py-2 bg-gray-100 hover:bg-gray-200 text-gray-600 rounded-lg text-xs font-semibold transition" title="Reset">
                    <i class="fas fa-undo"></i>
                </a>
            </div>
        </form>
    </div>

    {{-- Grid Kartu Anggota --}}
    <div class="bg-white rounded-xl shadow-xs p-5 border border-gray-100">
        <div class="flex justify-between items-center mb-4 pb-3 border-b border-gray-100">
            <div class="flex items-center gap-2">
                <input type="checkbox" id="check-all" onclick="toggleSelectAll(this)" class="w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500 cursor-pointer">
                <label for="check-all" class="text-xs font-semibold text-gray-700 cursor-pointer">Pilih Semua di Layar</label>
            </div>
            <span class="text-xs text-gray-400">Total ditemukan: <strong>{{ count($users) }}</strong> anggota</span>
        </div>

        @if(count($users) === 0)
            <div class="text-center py-12 text-gray-400">
                <i class="fas fa-id-badge text-4xl mb-3 text-gray-300"></i>
                <p class="text-sm font-semibold">Tidak ada data anggota ditemukan.</p>
                <p class="text-xs text-gray-400 mt-1">Coba sesuaikan kata kunci pencarian atau filter Anda.</p>
            </div>
        @else
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($users as $user)
                    <div class="border border-gray-200 rounded-xl p-4 bg-white hover:border-green-400 hover:shadow-md transition relative flex flex-col justify-between">
                        
                        {{-- Checkbox Select --}}
                        <div class="absolute top-3.5 right-3.5">
                            <input type="checkbox" value="{{ $user->id }}" class="card-checkbox w-4 h-4 text-green-600 rounded border-gray-300 focus:ring-green-500 cursor-pointer" onchange="updateSelectedCount()">
                        </div>

                        <div class="flex items-start gap-3">
                            {{-- Foto / Inisial --}}
                            @if($user->photo && file_exists(public_path('photos/' . $user->photo)))
                                <img src="{{ asset('photos/' . $user->photo) }}" class="w-12 h-14 object-cover rounded-md border border-gray-200 shrink-0">
                            @else
                                <div class="w-12 h-14 bg-emerald-100 text-emerald-800 rounded-md border border-emerald-200 flex items-center justify-center font-bold text-base shrink-0">
                                    {{ strtoupper(substr($user->name, 0, 1)) }}
                                </div>
                            @endif

                            {{-- Biodata --}}
                            <div class="flex-1 pr-6">
                                <h4 class="font-bold text-sm text-gray-900 leading-tight truncate" title="{{ $user->name }}">{{ $user->name }}</h4>
                                <p class="text-[11px] font-mono text-gray-500 mt-0.5">
                                    {{ $user->role === 'siswa' ? 'NISN: ' . ($user->nisn ?? '-') : 'NIP: ' . ($user->nip ?? '-') }}
                                </p>
                                
                                <div class="flex items-center gap-1.5 mt-2">
                                    @if($user->role === 'siswa')
                                        <span class="bg-blue-50 text-blue-700 text-[10px] font-semibold px-2 py-0.5 rounded border border-blue-200">
                                            🎓 Kelas {{ $user->class ?? '-' }}
                                        </span>
                                    @else
                                        <span class="bg-purple-50 text-purple-700 text-[10px] font-semibold px-2 py-0.5 rounded border border-purple-200">
                                            👨‍🏫 Guru {{ $user->class ? 'Wali ' . $user->class : '' }}
                                        </span>
                                    @endif
                                </div>
                            </div>
                        </div>

                        {{-- Tombol Aksi Cetak Satuan --}}
                        <div class="mt-4 pt-3 border-t border-gray-100 flex gap-2">
                            <a href="{{ route('petugas.kartuAnggotaSingle', $user->id) }}" target="_blank" class="flex-1 bg-emerald-600 hover:bg-emerald-700 text-white text-center py-1.5 rounded-lg text-xs font-semibold transition flex items-center justify-center gap-1">
                                <i class="fas fa-print"></i> Cetak Kartu
                            </a>
                            <a href="{{ route('petugas.scan.qrCode', $user->id) }}" target="_blank" class="bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg text-xs font-semibold transition" title="Lihat Hanya QR Code">
                                <i class="fas fa-qrcode"></i>
                            </a>
                        </div>

                    </div>
                @endforeach
            </div>
        @endif
    </div>

</div>

<script>
    function updateSelectedCount() {
        const checkboxes = document.querySelectorAll('.card-checkbox:checked');
        document.getElementById('count-selected').innerText = checkboxes.length;
    }

    function toggleSelectAll(master) {
        const checkboxes = document.querySelectorAll('.card-checkbox');
        checkboxes.forEach(cb => cb.checked = master.checked);
        updateSelectedCount();
    }

    function cetakTerpilih() {
        const checkboxes = document.querySelectorAll('.card-checkbox:checked');
        if (checkboxes.length === 0) {
            alert('Silakan pilih minimal satu anggota untuk dicetak.');
            return;
        }
        const ids = Array.from(checkboxes).map(cb => cb.value).join(',');
        const url = "{{ route('petugas.cetakKartuMassal') }}?ids=" + ids;
        window.open(url, '_blank');
    }
</script>
@endsection
