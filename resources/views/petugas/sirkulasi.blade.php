@extends('layouts.app')

@section('title', 'Sirkulasi - Perpustakaan Digital')

@section('content')
    {{-- Header Halaman --}}
    <div class="bg-white rounded-xl shadow-sm p-5 mb-6 border border-gray-100 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3">
            <h2 class="text-xl font-bold text-gray-800">Sirkulasi Peminjaman</h2>
            <span id="badge-notif" class="bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full animate-pulse {{ $jumlahPengajuan > 0 ? '' : 'hidden' }}">
                {{ $jumlahPengajuan }} Baru
            </span>
        </div>
        <div class="flex flex-wrap items-center gap-2 w-full sm:w-auto">
            <form method="POST" action="{{ route('petugas.wa.sendReminders') }}" onsubmit="return confirm('Kirim notifikasi pengingat H-1 dan peringatan keterlambatan WhatsApp ke seluruh anggota sekarang?')" class="flex-1 sm:flex-none">
                @csrf
                <button type="submit" class="w-full sm:w-auto bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition" title="Jalankan pengecekan WA pengingat H-1 dan denda keterlambatan">
                    <i class="fab fa-whatsapp text-sm"></i> Kirim Pengingat WA
                </button>
            </form>
            <a href="{{ route('petugas.scan') }}" class="flex-1 sm:flex-none bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-lg font-bold text-xs flex items-center justify-center gap-1.5 shadow-sm transition">
                <i class="fas fa-qrcode"></i> Scan QR Pinjam
            </a>
        </div>
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

    {{-- ============================================================== --}}
    {{-- STATISTIK KARTU FILTER INTERAKTIF (KLIK UNTUK MEMFILTER DATA) --}}
    {{-- ============================================================== --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4 mb-6">
        
        {{-- Card 1: Semua Data --}}
        <div id="card-filter-all" onclick="setFilterStatus('all')"
             class="group p-4 rounded-xl shadow-sm border transition-all duration-200 cursor-pointer relative bg-slate-50 border-slate-200 hover:bg-slate-100 hover:shadow-md hover:-translate-y-0.5 select-none">
            <div class="flex items-center justify-between">
                <p class="text-slate-700 font-bold text-xs md:text-sm flex items-center gap-1.5">
                    <i class="fas fa-layer-group text-slate-500"></i>
                    <span>Semua Data</span>
                </p>
                <span class="active-badge hidden bg-slate-700 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                    Aktif
                </span>
            </div>
            <p class="text-2xl md:text-3xl font-black text-slate-900 mt-2" id="stat-total">{{ $total }}</p>
            <p class="text-[11px] text-slate-500 mt-1 flex items-center justify-between">
                <span>Total transaksi</span>
                <i class="fas fa-filter text-[10px] opacity-40 group-hover:opacity-100 transition"></i>
            </p>
        </div>

        {{-- Card 2: Menunggu Konfirmasi --}}
        <div id="card-filter-menunggu" onclick="setFilterStatus('menunggu')"
             class="group p-4 rounded-xl shadow-sm border transition-all duration-200 cursor-pointer relative bg-amber-50 border-amber-200 hover:bg-amber-100/70 hover:shadow-md hover:-translate-y-0.5 select-none">
            <div class="flex items-center justify-between">
                <p class="text-amber-800 font-bold text-xs md:text-sm flex items-center gap-1.5">
                    <i class="fas fa-hourglass-half text-amber-600"></i>
                    <span>Menunggu</span>
                </p>
                <span class="active-badge hidden bg-amber-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                    Aktif
                </span>
            </div>
            <p class="text-2xl md:text-3xl font-black text-amber-900 mt-2" id="stat-menunggu">{{ $menunggu }}</p>
            <p class="text-[11px] text-amber-600 mt-1 flex items-center justify-between">
                <span>Perlu persetujuan</span>
                <i class="fas fa-filter text-[10px] opacity-40 group-hover:opacity-100 transition"></i>
            </p>
        </div>

        {{-- Card 3: Sedang Dipinjam --}}
        <div id="card-filter-dipinjam" onclick="setFilterStatus('dipinjam')"
             class="group p-4 rounded-xl shadow-sm border transition-all duration-200 cursor-pointer relative bg-blue-50 border-blue-200 hover:bg-blue-100/70 hover:shadow-md hover:-translate-y-0.5 select-none">
            <div class="flex items-center justify-between">
                <p class="text-blue-800 font-bold text-xs md:text-sm flex items-center gap-1.5">
                    <i class="fas fa-book-reader text-blue-600"></i>
                    <span>Sedang Dipinjam</span>
                </p>
                <span class="active-badge hidden bg-blue-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                    Aktif
                </span>
            </div>
            <p class="text-2xl md:text-3xl font-black text-blue-900 mt-2" id="stat-dipinjam">{{ $dipinjam }}</p>
            <p class="text-[11px] text-blue-600 mt-1 flex items-center justify-between">
                <span>Belum dikembalikan</span>
                <i class="fas fa-filter text-[10px] opacity-40 group-hover:opacity-100 transition"></i>
            </p>
        </div>

        {{-- Card 4: Sudah Dikembalikan --}}
        <div id="card-filter-dikembalikan" onclick="setFilterStatus('dikembalikan')"
             class="group p-4 rounded-xl shadow-sm border transition-all duration-200 cursor-pointer relative bg-emerald-50 border-emerald-200 hover:bg-emerald-100/70 hover:shadow-md hover:-translate-y-0.5 select-none">
            <div class="flex items-center justify-between">
                <p class="text-emerald-800 font-bold text-xs md:text-sm flex items-center gap-1.5">
                    <i class="fas fa-check-circle text-emerald-600"></i>
                    <span>Dikembalikan</span>
                </p>
                <span class="active-badge hidden bg-emerald-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full">
                    Aktif
                </span>
            </div>
            <p class="text-2xl md:text-3xl font-black text-emerald-900 mt-2" id="stat-dikembalikan">{{ $dikembalikan }}</p>
            <p class="text-[11px] text-emerald-600 mt-1 flex items-center justify-between">
                <span>Selesai / di rak</span>
                <i class="fas fa-filter text-[10px] opacity-40 group-hover:opacity-100 transition"></i>
            </p>
        </div>

    </div>

    {{-- Filter Bar & Pencarian Cepat --}}
    <div class="bg-white rounded-xl shadow-sm p-4 mb-4 border border-gray-100 flex flex-col md:flex-row items-stretch md:items-center justify-between gap-3">
        {{-- Search Input --}}
        <div class="relative flex-1">
            <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center text-gray-400">
                <i class="fas fa-search text-xs"></i>
            </span>
            <input type="text" id="search-sirkulasi" oninput="handleSearch()"
                   placeholder="Cari nama peminjam, NISN, judul buku, atau kode..."
                   class="w-full pl-9 pr-4 py-2 border border-gray-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-green-500 bg-gray-50/50">
        </div>

        {{-- Quick Pills Tab Filter --}}
        <div class="flex items-center gap-1.5 overflow-x-auto pb-1 md:pb-0 text-xs font-semibold">
            <button type="button" onclick="setFilterStatus('all')" id="pill-all"
                    class="pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-gray-800 text-white border-gray-800 shadow-sm">
                Semua
            </button>
            <button type="button" onclick="setFilterStatus('menunggu')" id="pill-menunggu"
                    class="pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-white text-gray-600 border-gray-200 hover:bg-gray-50">
                ⏳ Menunggu
            </button>
            <button type="button" onclick="setFilterStatus('dipinjam')" id="pill-dipinjam"
                    class="pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-white text-gray-600 border-gray-200 hover:bg-gray-50">
                📖 Dipinjam
            </button>
            <button type="button" onclick="setFilterStatus('dikembalikan')" id="pill-dikembalikan"
                    class="pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-white text-gray-600 border-gray-200 hover:bg-gray-50">
                ✅ Dikembalikan
            </button>
            <button type="button" onclick="setFilterStatus('terlambat')" id="pill-terlambat"
                    class="pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-white text-red-600 border-red-200 hover:bg-red-50">
                ⚠️ Terlambat
            </button>
        </div>
    </div>

    {{-- Daftar Peminjaman Tabel Container --}}
    <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-4 border-b border-gray-100 bg-gray-50/80 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <h3 class="font-bold text-gray-800">Daftar Peminjaman</h3>
                <span id="filter-indicator-badge" class="text-xs font-bold px-2.5 py-0.5 rounded-full bg-gray-200 text-gray-700">
                    Semua
                </span>
                <span id="filtered-count-info" class="text-xs text-gray-500 font-medium">
                    (0 data)
                </span>
            </div>
            <div class="flex items-center gap-2 text-xs">
                <button type="button" id="btn-reset-filter" onclick="setFilterStatus('all')" class="hidden text-green-600 hover:text-green-800 font-semibold underline">
                    Tampilkan Semua
                </button>
                <span class="text-gray-400">•</span>
                <span class="text-gray-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span> Auto-refresh
                </span>
            </div>
        </div>
        
        {{-- Tabel Desktop --}}
        <div class="hidden md:block overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-gray-50 text-gray-600 text-sm border-b border-gray-200">
                        <th class="p-4 font-semibold w-12 text-center">No</th>
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

    {{-- Script Filter Interaktif & Auto-Refresh --}}
    <script>
        let allLoansData = [];
        let currentFilter = '{{ $initialFilter ?? "all" }}'; // 'all', 'menunggu', 'dipinjam', 'dikembalikan', 'terlambat'
        let searchQuery = '';

        // Update status filter saat kartu atau tab diklik
        function setFilterStatus(status) {
            currentFilter = status;
            updateFilterUI();
            applyFilterAndRender();
        }

        // Handle pencarian cepat
        function handleSearch() {
            const input = document.getElementById('search-sirkulasi');
            searchQuery = input ? input.value.toLowerCase().trim() : '';
            applyFilterAndRender();
        }

        // Terapkan filter & render ke layar
        function applyFilterAndRender() {
            let filtered = allLoansData || [];

            // 1. Filter Kategori Status
            if (currentFilter === 'terlambat') {
                filtered = filtered.filter(loan => loan.is_late);
            } else if (currentFilter !== 'all') {
                filtered = filtered.filter(loan => loan.status === currentFilter);
            }

            // 2. Filter Teks Pencarian
            if (searchQuery) {
                filtered = filtered.filter(loan => {
                    const userName = (loan.user_name || '').toLowerCase();
                    const userId = (loan.user_id || '').toLowerCase();
                    const bookTitle = (loan.book_title || '').toLowerCase();
                    const bookCode = (loan.book_code || '').toLowerCase();
                    return userName.includes(searchQuery) ||
                           userId.includes(searchQuery) ||
                           bookTitle.includes(searchQuery) ||
                           bookCode.includes(searchQuery);
                });
            }

            renderFilteredTable(filtered);
            updateCountHeader(filtered.length);
        }

        // Render baris tabel desktop & mobile
        function renderFilteredTable(loans) {
            const tbodyDesktop = document.getElementById('table-body-desktop');
            const tbodyMobile = document.getElementById('table-body-mobile');
            tbodyDesktop.innerHTML = ''; 
            tbodyMobile.innerHTML = '';

            if (loans.length === 0) {
                let filterLabel = 'Kategori ini';
                if (currentFilter === 'menunggu') filterLabel = 'Menunggu Konfirmasi';
                else if (currentFilter === 'dipinjam') filterLabel = 'Sedang Dipinjam';
                else if (currentFilter === 'dikembalikan') filterLabel = 'Sudah Dikembalikan';
                else if (currentFilter === 'terlambat') filterLabel = 'Terlambat';

                const emptyHtml = `
                    <div class="py-12 text-center text-gray-500">
                        <div class="text-4xl mb-3">📭</div>
                        <p class="font-medium text-sm">Tidak ada data peminjaman untuk <strong>${filterLabel}</strong>.</p>
                        ${currentFilter !== 'all' || searchQuery ? `
                            <button type="button" onclick="setFilterStatus('all')" class="mt-3 inline-flex items-center gap-1.5 px-3 py-1.5 bg-gray-100 hover:bg-gray-200 text-gray-700 text-xs font-semibold rounded-lg transition">
                                <i class="fas fa-undo"></i> Tampilkan Semua Data
                            </button>
                        ` : ''}
                    </div>
                `;
                tbodyDesktop.innerHTML = `<tr><td colspan="7">${emptyHtml}</td></tr>`;
                tbodyMobile.innerHTML = emptyHtml;
                return;
            }

            loans.forEach((loan, index) => {
                let statusBadge = '';
                if (loan.status === 'menunggu') statusBadge = `<span class="bg-yellow-100 text-yellow-800 px-2 py-1 rounded text-xs font-semibold">⏳ Menunggu</span>`;
                else if (loan.status === 'dipinjam') statusBadge = `<span class="bg-blue-100 text-blue-800 px-2 py-1 rounded text-xs font-semibold">📖 Dipinjam</span>`;
                else if (loan.status === 'dikembalikan') statusBadge = `<span class="bg-green-100 text-green-800 px-2 py-1 rounded text-xs font-semibold">✅ Dikembalikan</span>`;
                else statusBadge = `<span class="bg-red-100 text-red-800 px-2 py-1 rounded text-xs font-semibold">❌ Ditolak</span>`;

                let waButton = '';
                if (loan.wa_link) {
                    const waTitle = loan.is_late ? 'Kirim Peringatan Terlambat via WA' : 'Kirim Pengingat via WA';
                    const waBadge = loan.is_late ? 'bg-red-50 text-red-700 border-red-200' : 'bg-green-50 text-green-700 border-green-200';
                    waButton = `
                        <a href="${loan.wa_link}" target="_blank" class="inline-flex items-center gap-1 px-2 py-1 rounded border ${waBadge} hover:opacity-80 transition text-[11px] font-semibold mt-1" title="${waTitle}">
                            <i class="fab fa-whatsapp"></i> Chat WA
                        </a>
                    `;
                }

                let aksi = '';
                if (loan.status === 'menunggu') {
                    // FORM INPUT TANGGAL MANUAL
                    aksi = `
                        <form method="POST" action="/petugas/setujui/${loan.id}" class="flex flex-col gap-1 w-48">
                            <input type="hidden" name="_token" value="{{ csrf_token() }}">
                            <div class="flex gap-1">
                                <div class="flex-1">
                                    <label class="text-[9px] text-gray-500 block">Tgl Pinjam</label>
                                    <input type="date" name="loan_date" required value="{{ date('Y-m-d') }}" class="w-full text-xs p-1 border rounded">
                                </div>
                                <div class="flex-1">
                                    <label class="text-[9px] text-gray-500 block">Jatuh Tempo</label>
                                    <input type="date" name="due_date" required value="{{ date('Y-m-d', strtotime('+7 days')) }}" class="w-full text-xs p-1 border rounded">
                                </div>
                            </div>
                            <div class="flex gap-1 mt-1">
                                <button type="submit" class="flex-1 bg-green-500 hover:bg-green-600 text-white px-2 py-1 rounded text-xs font-semibold shadow">Setujui</button>
                                <button type="button" onclick="tolakPinjam(${loan.id})" class="flex-1 bg-red-500 hover:bg-red-600 text-white px-2 py-1 rounded text-xs font-semibold shadow">Tolak</button>
                            </div>
                        </form>`;
                } else if (loan.status === 'dipinjam') {
                    aksi = `
                        <div class="flex flex-col items-center gap-1.5">
                            <form method="POST" action="/petugas/kembalikan/${loan.id}" class="w-full text-center">
                                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                                <button type="submit" class="w-full bg-blue-500 hover:bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-semibold shadow">Kembalikan</button>
                            </form>
                            ${waButton}
                        </div>`;
                } else {
                    aksi = '<span class="text-gray-400 text-xs text-center block">-</span>';
                }

                let lateLabel = '';
                if (loan.is_late) {
                    const formattedDenda = new Intl.NumberFormat('id-ID').format(loan.denda || 0);
                    lateLabel = `<br><span class="text-xs text-red-600 font-bold">⚠️ Terlambat ${loan.days_late} Hari<br>(Denda: Rp ${formattedDenda})</span>`;
                }

                let phoneInfo = loan.user_phone ? `<span class="text-[11px] text-gray-500 block"><i class="fab fa-whatsapp text-green-600"></i> ${loan.user_phone}</span>` : '';

                // Desktop Row
                tbodyDesktop.innerHTML += `
                    <tr class="border-b border-gray-100 hover:bg-gray-50 transition text-sm">
                        <td class="p-4 text-gray-500 text-center font-mono">${index + 1}</td>
                        <td class="p-4">
                            <strong class="text-gray-800">${loan.user_name}</strong>
                            <br><span class="text-xs text-gray-500 font-mono">${loan.user_id}</span>
                            ${phoneInfo}
                        </td>
                        <td class="p-4"><strong class="text-gray-800">${loan.book_title}</strong><br><span class="text-xs text-gray-500 font-mono">${loan.book_code}</span></td>
                        <td class="p-4 text-gray-600">${loan.loan_date}</td>
                        <td class="p-4 text-gray-600">${loan.due_date} ${lateLabel}</td>
                        <td class="p-4">${statusBadge}</td>
                        <td class="p-4">${aksi}</td>
                    </tr>
                `;

                // Mobile Card
                tbodyMobile.innerHTML += `
                    <div class="border border-gray-200 rounded-lg p-4 shadow-sm bg-gray-50">
                        <div class="flex justify-between items-start mb-3 border-b border-gray-200 pb-2">
                            <div>
                                <strong class="text-gray-800 text-sm block">${loan.user_name}</strong>
                                <span class="text-xs text-gray-500 font-mono">${loan.user_id}</span>
                                ${phoneInfo}
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
                        ${lateLabel ? `<div class="text-xs text-red-600 font-bold mb-3">${lateLabel.replace('<br>', '')}</div>` : ''}
                        <div class="mt-2 pt-3 border-t border-gray-200">${aksi}</div>
                    </div>
                `;
            });
        }

        // Update visual kartu & pill tombol aktif
        function updateFilterUI() {
            const cardMap = {
                'all': { el: document.getElementById('card-filter-all'), ring: 'ring-slate-500', border: 'border-slate-500', bg: 'bg-slate-100' },
                'menunggu': { el: document.getElementById('card-filter-menunggu'), ring: 'ring-amber-500', border: 'border-amber-500', bg: 'bg-amber-100' },
                'dipinjam': { el: document.getElementById('card-filter-dipinjam'), ring: 'ring-blue-500', border: 'border-blue-500', bg: 'bg-blue-100' },
                'dikembalikan': { el: document.getElementById('card-filter-dikembalikan'), ring: 'ring-emerald-500', border: 'border-emerald-500', bg: 'bg-emerald-100' }
            };

            // Update Card Styles
            for (const [key, conf] of Object.entries(cardMap)) {
                if (!conf.el) continue;
                const badge = conf.el.querySelector('.active-badge');
                if (key === currentFilter) {
                    conf.el.classList.add('ring-2', 'ring-offset-2', conf.ring, conf.border, conf.bg);
                    if (badge) badge.classList.remove('hidden');
                } else {
                    conf.el.classList.remove('ring-2', 'ring-offset-2', 'ring-slate-500', 'ring-amber-500', 'ring-blue-500', 'ring-emerald-500', 'border-slate-500', 'border-amber-500', 'border-blue-500', 'border-emerald-500', 'bg-slate-100', 'bg-amber-100', 'bg-blue-100', 'bg-emerald-100');
                    if (badge) badge.classList.add('hidden');
                }
            }

            // Update Pills Styles
            const pills = ['all', 'menunggu', 'dipinjam', 'dikembalikan', 'terlambat'];
            pills.forEach(p => {
                const pillEl = document.getElementById('pill-' + p);
                if (!pillEl) return;
                if (p === currentFilter) {
                    pillEl.className = 'pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-gray-800 text-white border-gray-800 shadow-sm';
                } else if (p === 'terlambat') {
                    pillEl.className = 'pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-white text-red-600 border-red-200 hover:bg-red-50';
                } else {
                    pillEl.className = 'pill-btn px-3 py-1.5 rounded-lg border transition flex items-center gap-1 bg-white text-gray-600 border-gray-200 hover:bg-gray-50';
                }
            });

            // Update Indicator Header
            const indicator = document.getElementById('filter-indicator-badge');
            const resetBtn = document.getElementById('btn-reset-filter');
            if (indicator) {
                if (currentFilter === 'all') {
                    indicator.innerText = 'Semua';
                    indicator.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-gray-200 text-gray-700';
                    if (resetBtn) resetBtn.classList.add('hidden');
                } else if (currentFilter === 'menunggu') {
                    indicator.innerText = 'Menunggu Konfirmasi';
                    indicator.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-800 border border-amber-300';
                    if (resetBtn) resetBtn.classList.remove('hidden');
                } else if (currentFilter === 'dipinjam') {
                    indicator.innerText = 'Sedang Dipinjam';
                    indicator.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-blue-100 text-blue-800 border border-blue-300';
                    if (resetBtn) resetBtn.classList.remove('hidden');
                } else if (currentFilter === 'dikembalikan') {
                    indicator.innerText = 'Sudah Dikembalikan';
                    indicator.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 border border-emerald-300';
                    if (resetBtn) resetBtn.classList.remove('hidden');
                } else if (currentFilter === 'terlambat') {
                    indicator.innerText = '⚠️ Terlambat Saja';
                    indicator.className = 'text-xs font-bold px-2.5 py-0.5 rounded-full bg-red-100 text-red-800 border border-red-300';
                    if (resetBtn) resetBtn.classList.remove('hidden');
                }
            }
        }

        function updateCountHeader(count) {
            const countInfo = document.getElementById('filtered-count-info');
            if (countInfo) {
                countInfo.innerText = `(${count} transaksi)`;
            }
        }

        // Fungsi Tolak menggunakan Form sementara agar aman dari CSRF
        function tolakPinjam(id) {
            if(confirm('Yakin ingin menolak pinjaman ini?')) {
                let form = document.createElement('form');
                form.method = 'POST';
                form.action = '/petugas/tolak/' + id;
                form.innerHTML = `@csrf`;
                document.body.appendChild(form);
                form.submit();
            }
        }

        // Ambil data terbaru dari server
        function fetchLatestData() {
            fetch("{{ route('petugas.api.sirkulasi') }}")
                .then(res => res.json())
                .then(data => {
                    allLoansData = data.loans || [];
                    
                    // Update angka di kartu statistik
                    if (document.getElementById('stat-total')) document.getElementById('stat-total').innerText = data.total ?? allLoansData.length;
                    if (document.getElementById('stat-menunggu')) document.getElementById('stat-menunggu').innerText = data.menunggu ?? 0;
                    if (document.getElementById('stat-dipinjam')) document.getElementById('stat-dipinjam').innerText = data.dipinjam ?? 0;
                    if (document.getElementById('stat-dikembalikan')) document.getElementById('stat-dikembalikan').innerText = data.dikembalikan ?? 0;

                    // Update badge header
                    const badge = document.getElementById('badge-notif');
                    if (badge) {
                        if (data.menunggu > 0) {
                            badge.innerText = data.menunggu + ' Baru';
                            badge.classList.remove('hidden');
                        } else {
                            badge.classList.add('hidden');
                        }
                    }

                    // Terapkan filter aktif ke data baru
                    applyFilterAndRender();
                })
                .catch(err => console.error(err));
        }

        // Matikan auto-refresh sementara jika Petugas sedang mengetik/mengisi form tanggal
        let isTyping = false;
        document.addEventListener('focusin', function(e) {
            if (e.target.tagName === 'INPUT') isTyping = true;
        });
        document.addEventListener('focusout', function(e) {
            if (e.target.tagName === 'INPUT') isTyping = false;
        });

        setInterval(() => {
            if (!isTyping) fetchLatestData();
        }, 3000);
        
        document.addEventListener('DOMContentLoaded', function() {
            updateFilterUI();
            fetchLatestData();
        });
    </script>
@endsection