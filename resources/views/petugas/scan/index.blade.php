@extends('layouts.app')

@section('title', 'Scan QR - Perpustakaan Digital')

@section('content')
    <div class="max-w-3xl mx-auto">
        <div class="bg-white rounded-xl shadow-sm p-6 md:p-10 border border-gray-100 text-center">
            <h2 class="text-xl md:text-2xl font-bold text-gray-800 mb-2">Scan Kartu Anggota</h2>
            <p class="text-sm text-gray-500 mb-6">Gunakan kamera untuk memindai QR Code pada kartu, atau ketik manual.</p>

            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6 text-sm">
                    {{ session('error') }}
                </div>
            @endif

            {{-- AREA KAMERA --}}
            <div id="reader" class="w-full max-w-sm mx-auto mb-6 bg-gray-100 rounded-xl overflow-hidden border-2 border-dashed border-gray-300" style="display: none;"></div>

            <button id="btn-start-camera" class="w-full max-w-sm mx-auto bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-xl shadow-lg transition flex items-center justify-center gap-2 mb-6">
                <i class="fas fa-camera"></i> BUKA KAMERA
            </button>

            <div class="relative flex py-4 items-center">
                <div class="flex-grow border-t border-gray-300"></div>
                <span class="flex-shrink-0 mx-4 text-gray-400 text-sm">ATAU KETIK MANUAL</span>
                <div class="flex-grow border-t border-gray-300"></div>
            </div>

            {{-- FORM INPUT MANUAL --}}
            <form method="POST" action="{{ route('petugas.scan.hasil') }}" class="max-w-md mx-auto">
                @csrf
                <div class="mb-4">
                    <input type="text" name="qr_code" id="qr_code_input" required
                           class="w-full px-4 py-4 text-center text-lg border-2 border-green-500 rounded-xl focus:outline-none focus:ring-4 focus:ring-green-200 transition font-mono tracking-widest"
                           placeholder="Ketik NISN / NIP...">
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold text-lg py-3.5 rounded-xl shadow-lg transition flex items-center justify-center gap-2">
                    <i class="fas fa-search"></i> CARI ANGGOTA
                </button>
            </form>
        </div>
    </div>

    {{-- LIBRARY HTML5 QR CODE SCANNER --}}
    <script src="https://unpkg.com/html5-qrcode"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const btnStart = document.getElementById('btn-start-camera');
            const readerDiv = document.getElementById('reader');
            const inputField = document.getElementById('qr_code_input');
            let html5QrCode;

            btnStart.addEventListener('click', function() {
                readerDiv.style.display = 'block';
                btnStart.style.display = 'none';

                // Inisialisasi kamera
                html5QrCode = new Html5Qrcode("reader");
                
                html5QrCode.start(
                    { facingMode: "environment" }, // Gunakan kamera belakang
                    {
                        fps: 10,
                        qrbox: { width: 250, height: 250 }
                    },
                    (decodedText, decodedResult) => {
                        // Saat QR terdeteksi:
                        inputField.value = decodedText; // Masukkan hasil scan ke input
                        html5QrCode.stop().then(() => {
                            readerDiv.style.display = 'none';
                            btnStart.style.display = 'block';
                            // Otomatis submit form
                            inputField.form.submit();
                        });
                    },
                    (errorMessage) => {
                        // Abaikan error pembacaan frame per frame
                    }
                ).catch((err) => {
                    alert("Gagal membuka kamera: " + err);
                    readerDiv.style.display = 'none';
                    btnStart.style.display = 'block';
                });
            });
        });
    </script>
@endsection