@extends('layouts.app')

@section('title', 'Scan QR Peminjaman Buku - Perpustakaan Digital')

@section('content')
<div class="max-w-3xl mx-auto space-y-6">

    {{-- HEADER HALAMAN --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 border border-gray-100 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
        <div>
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold uppercase tracking-wider mb-2">
                <i class="fas fa-qrcode"></i> Meja Sirkulasi Digital
            </div>
            <h1 class="text-2xl font-black text-gray-800 tracking-tight">Scan QR Peminjaman Buku</h1>
            <p class="text-sm text-gray-500 mt-1">Pindai kartu anggota siswa/guru untuk meminjamkan buku secara otomatis tanpa tulis manual.</p>
        </div>
        <a href="{{ route('petugas.sirkulasi') }}" class="text-xs font-semibold text-emerald-700 hover:text-emerald-800 bg-emerald-50 hover:bg-emerald-100 px-3.5 py-2 rounded-xl transition flex items-center gap-1.5 border border-emerald-200">
            <i class="fas fa-exchange-alt"></i> Lihat Sirkulasi
        </a>
    </div>

    {{-- ALUR PROSES (BREADCRUMB STEPS) --}}
    <div class="bg-white rounded-2xl shadow-sm p-4 border border-gray-100">
        <div class="grid grid-cols-3 gap-2 text-center text-xs font-semibold">
            <div class="flex items-center justify-center gap-1.5 p-2 rounded-xl {{ isset($selectedMember) ? 'bg-emerald-50 text-emerald-700 font-bold border border-emerald-200' : 'bg-emerald-600 text-white shadow-sm' }}">
                <span>1. Scan Kartu</span>
                @if(isset($selectedMember)) <i class="fas fa-check-circle"></i> @endif
            </div>
            <div class="flex items-center justify-center gap-1.5 p-2 rounded-xl {{ isset($selectedMember) ? 'bg-emerald-600 text-white shadow-sm' : 'bg-gray-100 text-gray-400' }}">
                <span>2. Pilih Buku</span>
            </div>
            <div class="flex items-center justify-center gap-1.5 p-2 rounded-xl bg-gray-100 text-gray-400">
                <span>3. Selesai</span>
            </div>
        </div>
    </div>

    {{-- ALERT ERROR --}}
    @if(session('error'))
        <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-4 rounded-2xl shadow-sm flex items-center gap-3 text-sm">
            <div class="w-8 h-8 rounded-full bg-red-100 text-red-600 flex items-center justify-center font-bold flex-shrink-0">⚠️</div>
            <div>{{ session('error') }}</div>
        </div>
    @endif

    {{-- ============================================================== --}}
    {{-- LANGKAH 1: SCAN KARTU ANGGOTA --}}
    {{-- ============================================================== --}}
    <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8 border border-gray-100" id="section-step-1">
        
        @if(isset($selectedMember))
            {{-- STATUS BERHASIL SCAN --}}
            <div class="bg-emerald-50 border border-emerald-300 rounded-2xl p-5 mb-6 text-center animate-pulse-once">
                <div class="w-12 h-12 bg-emerald-600 text-white rounded-full flex items-center justify-center mx-auto mb-2 text-xl shadow-md">
                    <i class="fas fa-check"></i>
                </div>
                <h3 class="text-xl font-extrabold text-emerald-900" id="scan-success-title">
                    Berhasil, {{ $selectedMember->name }}
                </h3>
                <p class="text-xs text-emerald-700 mt-1">Kartu anggota terverifikasi di sistem perpustakaan.</p>
            </div>

            {{-- KARTU INFO ANGGOTA --}}
            <div class="bg-gradient-to-r from-gray-50 to-emerald-50/30 rounded-2xl p-5 border border-gray-200/80 mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-4 text-left w-full md:w-auto">
                    @if($selectedMember->photo && file_exists(public_path('photos/' . $selectedMember->photo)))
                        <img src="{{ asset('photos/' . $selectedMember->photo) }}" alt="{{ $selectedMember->name }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-white shadow-md">
                    @else
                        <div class="w-16 h-16 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-bold text-2xl shadow-md flex-shrink-0">
                            {{ strtoupper(substr($selectedMember->name, 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <div class="flex items-center gap-2">
                            <h4 class="font-extrabold text-lg text-gray-800 leading-tight">{{ $selectedMember->name }}</h4>
                            @if($selectedMember->role === 'siswa')
                                <span class="bg-blue-100 text-blue-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Siswa</span>
                            @else
                                <span class="bg-purple-100 text-purple-700 text-[10px] font-bold px-2 py-0.5 rounded-full uppercase">Guru</span>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 font-mono mt-0.5">
                            {{ $selectedMember->role === 'siswa' ? 'NISN: ' . ($selectedMember->nisn ?? '-') : 'NIP: ' . ($selectedMember->nip ?? '-') }}
                            @if($selectedMember->class) • Kelas {{ $selectedMember->class }} @endif
                        </p>
                        <div class="mt-2 inline-flex items-center gap-1.5 text-xs {{ ($activeLoansCount ?? 0) >= 2 ? 'text-amber-700 bg-amber-50' : 'text-emerald-700 bg-emerald-100/60' }} px-2.5 py-1 rounded-lg font-medium">
                            <i class="fas fa-book-reader"></i>
                            <span>Sedang meminjam: <strong>{{ $activeLoansCount ?? 0 }}</strong> dari maks 2 buku</span>
                        </div>
                    </div>
                </div>

                <a href="{{ route('petugas.scan') }}" class="w-full md:w-auto text-center px-4 py-2 bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-xl text-xs font-bold transition flex items-center justify-center gap-1.5">
                    <i class="fas fa-sync-alt"></i> Ganti Anggota
                </a>
            </div>

        @else
            {{-- AREA SCANNER BELUM DI-SCAN --}}
            <div class="text-center">
                <div class="w-14 h-14 bg-emerald-100 text-emerald-700 rounded-2xl flex items-center justify-center mx-auto mb-3 text-2xl shadow-inner">
                    <i class="fas fa-camera"></i>
                </div>
                <h2 class="text-xl md:text-2xl font-black text-gray-800 mb-1">Scan Kartu Anggota</h2>
                <p class="text-sm text-gray-500 max-w-md mx-auto mb-6">Arahkan kamera ke QR Code pada kartu fisik siswa/guru, atau masukkan nomor NISN/NIP.</p>

                {{-- AREA KAMERA PREVIEW --}}
                <div id="reader" class="w-full max-w-sm mx-auto mb-5 bg-black/90 rounded-2xl overflow-hidden shadow-xl border-4 border-emerald-500 relative" style="display: none; min-height: 260px;"></div>

                <div class="flex flex-col sm:flex-row items-center justify-center gap-3 mb-6">
                    <button id="btn-start-camera" type="button" class="w-full sm:w-auto px-6 py-3.5 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-xl shadow-lg hover:shadow-blue-200 transition flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-camera"></i> BUKA KAMERA SCANNER
                    </button>
                    <button id="btn-stop-camera" type="button" style="display:none;" class="w-full sm:w-auto px-6 py-3.5 bg-gray-600 hover:bg-gray-700 text-white font-bold rounded-xl shadow-lg transition flex items-center justify-center gap-2 text-sm">
                        <i class="fas fa-stop"></i> TUTUP KAMERA
                    </button>
                </div>

                <div class="relative flex py-2 items-center max-w-md mx-auto">
                    <div class="flex-grow border-t border-gray-200"></div>
                    <span class="flex-shrink-0 mx-4 text-gray-400 text-xs font-bold uppercase tracking-wider">Atau Ketik Manual</span>
                    <div class="flex-grow border-t border-gray-200"></div>
                </div>

                {{-- FORM INPUT MANUAL --}}
                <form id="form-scan-manual" method="POST" action="{{ route('petugas.scan.hasil') }}" class="max-w-md mx-auto mt-4">
                    @csrf
                    <div class="mb-3">
                        <input type="text" name="qr_code" id="qr_code_input" required autofocus
                               class="w-full px-5 py-3.5 text-center text-lg border-2 border-emerald-500 rounded-2xl focus:outline-none focus:ring-4 focus:ring-emerald-200 transition font-mono tracking-widest placeholder-gray-400"
                               placeholder="Ketik NISN / NIP...">
                    </div>

                    <button type="submit" id="btn-submit-manual" class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-base py-3.5 rounded-2xl shadow-lg hover:shadow-emerald-200 transition flex items-center justify-center gap-2">
                        <i class="fas fa-search"></i> CARI ANGGOTA
                    </button>
                </form>
            </div>
        @endif

    </div>

    {{-- ============================================================== --}}
    {{-- LANGKAH 2 & 3: FORM PILIH BUKU & PINJAMKAN BUKU --}}
    {{-- ============================================================== --}}
    @if(isset($selectedMember))
        <div class="bg-white rounded-2xl shadow-sm p-6 md:p-8 border-2 border-emerald-500/50 relative overflow-hidden" id="section-step-2">
            
            <div class="absolute top-0 right-0 bg-emerald-600 text-white text-[11px] font-extrabold uppercase px-4 py-1.5 rounded-bl-xl tracking-wider">
                Langkah 2: Pilih Buku
            </div>

            <div class="mb-6">
                <h3 class="text-xl font-black text-gray-800 flex items-center gap-2">
                    <i class="fas fa-book-open text-emerald-600"></i> Pilih Buku untuk Dipinjamkan
                </h3>
                <p class="text-xs text-gray-500 mt-1">Pilih judul buku dari koleksi yang tersedia untuk dipinjamkan kepada <strong>{{ $selectedMember->name }}</strong>.</p>
            </div>

            <form method="POST" action="{{ route('petugas.scan.pinjam') }}" id="form-pinjam-buku">
                @csrf
                <input type="hidden" name="user_id" value="{{ $selectedMember->id }}">

                {{-- KOLOM PILIH BUKU --}}
                <div class="mb-5">
                    <label class="block text-sm font-extrabold text-gray-700 mb-2">
                        <i class="fas fa-book text-emerald-600 mr-1"></i> Pilih Buku <span class="text-red-500">*</span>
                    </label>

                    {{-- Search Filter Cepat --}}
                    <div class="mb-2">
                        <input type="text" id="quick-filter-book" placeholder="🔍 Ketik judul atau kode buku untuk filter cepat..." 
                               class="w-full px-4 py-2 text-xs border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none bg-gray-50">
                    </div>

                    <select name="book_id" id="select_book_id" required
                            class="w-full px-4 py-3.5 border-2 border-gray-300 rounded-xl focus:border-emerald-600 focus:ring-2 focus:ring-emerald-200 focus:outline-none text-sm font-medium bg-white text-gray-800">
                        <option value="" disabled selected>-- Klik untuk memilih buku yang tersedia --</option>
                        @foreach($books as $book)
                            <option value="{{ $book->id }}" 
                                    data-title="{{ $book->title }}" 
                                    data-code="{{ $book->code }}"
                                    data-author="{{ $book->author }}"
                                    data-stock="{{ $book->stock_available }}"
                                    data-category="{{ $book->category ? $book->category->name : 'Umum' }}">
                                [{{ $book->code }}] {{ $book->title }} — Stok: {{ $book->stock_available }} (Kategori: {{ $book->category ? $book->category->name : 'Umum' }})
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- PREVIEW DETAIL BUKU TERPILIH --}}
                <div id="selected-book-preview" class="p-4 rounded-xl bg-gray-50 border border-gray-200 mb-5" style="display:none;">
                    <div class="flex items-start justify-between">
                        <div>
                            <span class="text-[10px] font-bold uppercase tracking-wider text-emerald-700 bg-emerald-100 px-2 py-0.5 rounded-full" id="preview-category">Kategori</span>
                            <h4 class="font-extrabold text-gray-900 text-base mt-1" id="preview-title">Judul Buku</h4>
                            <p class="text-xs text-gray-500" id="preview-author">Penulis: -</p>
                        </div>
                        <div class="text-right">
                            <span class="text-xs text-gray-400 block">Stok Tersedia</span>
                            <span class="text-lg font-black text-emerald-600" id="preview-stock">0</span>
                        </div>
                    </div>
                </div>

                {{-- TANGGAL PINJAM & JATUH TEMPO --}}
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">
                            <i class="far fa-calendar-alt text-emerald-600 mr-1"></i> Tanggal Pinjam
                        </label>
                        <input type="date" name="loan_date" value="{{ \Carbon\Carbon::now()->format('Y-m-d') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none text-sm bg-gray-50">
                        <span class="text-[11px] text-gray-400 mt-1 block">Otomatis hari ini (bisa disesuaikan).</span>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-600 mb-1">
                            <i class="far fa-calendar-check text-emerald-600 mr-1"></i> Batas Pengembalian (Jatuh Tempo)
                        </label>
                        <input type="date" name="due_date" value="{{ \Carbon\Carbon::now()->addDays(7)->format('Y-m-d') }}" required
                               class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:ring-2 focus:ring-emerald-500 focus:outline-none text-sm bg-gray-50">
                        <span class="text-[11px] text-gray-400 mt-1 block">Standar 7 hari kalender.</span>
                    </div>
                </div>

                {{-- TOMBOL KONFIRMASI PINJAMKAN BUKU --}}
                <button type="submit" id="btn-pinjam-buku"
                        class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-extrabold text-base md:text-lg py-4 rounded-2xl shadow-xl hover:shadow-emerald-200 transition-all flex items-center justify-center gap-3">
                    <i class="fas fa-hand-holding-heart text-xl"></i> PINJAMKAN BUKU
                </button>
            </form>
        </div>
    @endif

</div>

{{-- ============================================================== --}}
{{-- MODAL POPUP NOTIFIKASI AKHIR (BERHASIL DIPINJAMKAN) --}}
{{-- ============================================================== --}}
<div id="modal-success-loan" 
     class="fixed inset-0 z-50 bg-black/60 backdrop-blur-sm flex items-center justify-center p-4 transition-opacity {{ session('loan_success') ? '' : 'hidden' }}">
    <div class="bg-white rounded-3xl shadow-2xl max-w-md w-full p-6 md:p-8 text-center relative border border-gray-100 transform transition-all scale-100">
        
        {{-- Ikon Animasi Sukses --}}
        <div class="w-20 h-20 bg-emerald-100 text-emerald-600 rounded-3xl flex items-center justify-center text-4xl mx-auto mb-4 shadow-lg shadow-emerald-100 ring-8 ring-emerald-50 animate-bounce">
            🎉
        </div>

        <h3 class="text-2xl font-black text-gray-800 mb-1">Peminjaman Berhasil!</h3>
        <p class="text-xs text-gray-400 uppercase tracking-widest font-bold mb-5">Sirkulasi Perpustakaan Digital</p>

        {{-- Pesan Spesifik Sesuai Permintaan User --}}
        <div class="bg-gradient-to-br from-emerald-50 to-green-50 border border-emerald-200 rounded-2xl p-5 mb-6 text-left">
            <p class="text-base font-medium text-gray-800 leading-relaxed">
                Buku <strong class="text-emerald-700 font-extrabold text-lg" id="modal-book-title">{{ session('loan_book_title') ?? 'Buku' }}</strong> 
                berhasil dipinjamkan kepada 
                <strong class="text-blue-700 font-extrabold text-lg" id="modal-user-name">{{ session('loan_user_name') ?? 'Anggota' }}</strong>.
            </p>

            <div class="mt-4 pt-3 border-t border-emerald-200/80 flex justify-between items-center text-xs">
                <span class="text-gray-500 font-semibold">Batas Jatuh Tempo:</span>
                <span class="font-bold text-emerald-800 bg-white px-2.5 py-1 rounded-lg border border-emerald-200" id="modal-due-date">
                    {{ session('loan_due_date') ?? \Carbon\Carbon::now()->addDays(7)->translatedFormat('d F Y') }}
                </span>
            </div>
        </div>

        <div class="space-y-2">
            <a href="{{ route('petugas.scan') }}" 
               class="w-full bg-emerald-600 hover:bg-emerald-700 text-white font-bold py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2 text-sm">
                <i class="fas fa-qrcode"></i> Scan Anggota Lain
            </a>
            <div class="flex gap-2">
                <a href="{{ route('petugas.sirkulasi') }}" 
                   class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition text-xs flex items-center justify-center gap-1.5">
                    <i class="fas fa-list"></i> Lihat Sirkulasi
                </a>
                <button type="button" onclick="closeSuccessModal()"
                        class="flex-1 bg-gray-100 hover:bg-gray-200 text-gray-700 font-semibold py-2.5 rounded-xl transition text-xs">
                    Tutup
                </button>
            </div>
        </div>

    </div>
</div>

{{-- LIBRARY HTML5 QR CODE SCANNER --}}
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function closeSuccessModal() {
        const modal = document.getElementById('modal-success-loan');
        if (modal) {
            modal.classList.add('hidden');
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const btnStart = document.getElementById('btn-start-camera');
        const btnStop = document.getElementById('btn-stop-camera');
        const readerDiv = document.getElementById('reader');
        const inputField = document.getElementById('qr_code_input');
        const selectBook = document.getElementById('select_book_id');
        const previewBox = document.getElementById('selected-book-preview');
        const quickFilter = document.getElementById('quick-filter-book');
        let html5QrCode = null;

        // Preview Detail Buku saat dipilih
        if (selectBook) {
            selectBook.addEventListener('change', function() {
                const opt = this.options[this.selectedIndex];
                if (opt && opt.value) {
                    document.getElementById('preview-title').innerText = opt.getAttribute('data-title');
                    document.getElementById('preview-author').innerText = 'Penulis: ' + opt.getAttribute('data-author') + ' (' + opt.getAttribute('data-code') + ')';
                    document.getElementById('preview-stock').innerText = opt.getAttribute('data-stock') + ' Eksemplar';
                    document.getElementById('preview-category').innerText = opt.getAttribute('data-category');
                    previewBox.style.display = 'block';
                } else {
                    previewBox.style.display = 'none';
                }
            });
        }

        // Quick Filter Buku
        if (quickFilter && selectBook) {
            quickFilter.addEventListener('input', function() {
                const val = this.value.toLowerCase().trim();
                const options = selectBook.options;
                for (let i = 1; i < options.length; i++) {
                    const text = options[i].text.toLowerCase();
                    options[i].style.display = text.includes(val) ? '' : 'none';
                }
            });
        }

        // START KAMERA SCANNER
        if (btnStart) {
            btnStart.addEventListener('click', function() {
                readerDiv.style.display = 'block';
                btnStart.style.display = 'none';
                if (btnStop) btnStop.style.display = 'inline-flex';

                html5QrCode = new Html5Qrcode("reader");

                html5QrCode.start(
                    { facingMode: "environment" },
                    {
                        fps: 10,
                        qrbox: { width: 240, height: 240 }
                    },
                    (decodedText, decodedResult) => {
                        console.log("QR Code Terdeteksi:", decodedText);
                        
                        // Isi field input manual
                        if (inputField) inputField.value = decodedText;

                        // Hentikan kamera
                        html5QrCode.stop().then(() => {
                            readerDiv.style.display = 'none';
                            btnStart.style.display = 'inline-flex';
                            if (btnStop) btnStop.style.display = 'none';

                            // Auto submit form untuk server-side rendering langkah 2
                            const form = document.getElementById('form-scan-manual');
                            if (form) form.submit();
                        }).catch(err => {
                            const form = document.getElementById('form-scan-manual');
                            if (form) form.submit();
                        });
                    },
                    (errorMessage) => {
                        // Abaikan pembacaan frame gagal
                    }
                ).catch((err) => {
                    alert("Kamera tidak dapat diakses: " + err);
                    readerDiv.style.display = 'none';
                    btnStart.style.display = 'inline-flex';
                    if (btnStop) btnStop.style.display = 'none';
                });
            });
        }

        // STOP KAMERA
        if (btnStop) {
            btnStop.addEventListener('click', function() {
                if (html5QrCode) {
                    html5QrCode.stop().then(() => {
                        readerDiv.style.display = 'none';
                        btnStart.style.display = 'inline-flex';
                        btnStop.style.display = 'none';
                    });
                }
            });
        }

        // Intercept form submit pinjam buku via AJAX untuk pop up instan
        const formPinjam = document.getElementById('form-pinjam-buku');
        if (formPinjam) {
            formPinjam.addEventListener('submit', function(e) {
                const bookVal = selectBook ? selectBook.value : '';
                if (!bookVal) {
                    e.preventDefault();
                    alert('Silakan pilih buku terlebih dahulu!');
                    return;
                }
            });
        }
    });
</script>
@endsection