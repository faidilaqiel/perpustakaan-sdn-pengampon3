<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input { font-size: 16px !important; }
        /* Custom scrollbar untuk tampilan lebih rapi */
        ::-webkit-scrollbar { width: 0px; background: transparent; }
    </style>
</head>
<body class="bg-green-50 min-h-screen flex items-end md:items-center justify-center md:p-4 overflow-hidden relative">

    {{-- BACKGROUND DEKORASI (Awan/Gradasi) --}}
    <div class="absolute top-0 left-0 w-full h-full bg-gradient-to-b from-green-100 to-white z-0"></div>

    {{-- KARTU LOGIN UTAMA --}}
    <div class="bg-white w-full max-w-md md:max-w-lg rounded-t-[40px] md:rounded-[40px] shadow-2xl relative z-10 flex flex-col overflow-hidden pb-8 md:pb-0">
        
        {{-- BAGIAN ATAS: LOGO, JUDUL, ILUSTRASI --}}
        <div class="bg-green-50/50 pt-10 pb-8 px-6 text-center relative">
            {{-- Logo --}}
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-24 h-24 mx-auto object-contain drop-shadow-md mb-4">
            
            {{-- Judul --}}
            <h1 class="text-2xl md:text-3xl font-extrabold text-green-800 tracking-tight">Perpustakaan Digital</h1>
            <h2 class="text-lg md:text-xl font-bold text-green-600 mt-1">SDN Pengampon 3</h2>
            <p class="text-sm md:text-base text-gray-600 mt-3 font-medium">Membaca hari ini,<br>masa depan esok.</p>
            
            {{-- Ilustrasi Buku (Bisa diganti gambar PNG Anda) --}}
            <div class="mt-6 flex justify-center">
                <img src="{{ asset('images/ilustrasi-buku.png') }}" alt="Ilustrasi Buku" class="w-48 md:w-56 object-contain" onerror="this.style.display='none'">
            </div>
        </div>

        {{-- BAGIAN BAWAH: FORM LOGIN --}}
        <div class="bg-white px-6 md:px-8 pt-6 pb-8 rounded-t-3xl -mt-6 relative z-10">
            
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-5 text-sm font-medium text-center">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-4">
                @csrf
                
                {{-- Username --}}
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1.5">Username / NISN / NIP</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="username" required autofocus
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition text-sm"
                               placeholder="Masukkan NISN atau NIP">
                    </div>
                </div>
                
                {{-- Password --}}
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-1.5">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition text-sm"
                               placeholder="Masukkan kata sandi">
                        <div class="absolute inset-y-0 right-0 pr-3.5 flex items-center cursor-pointer text-gray-400 hover:text-gray-600">
                            <i class="fas fa-eye"></i>
                        </div>
                    </div>
                </div>

                {{-- Ingat Saya & Lupa Sandi --}}
                <div class="flex items-center justify-between pt-1">
                    <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 mr-2">
                        Ingat saya
                    </label>
                    <a href="#" class="text-sm text-green-600 hover:text-green-800 font-bold hover:underline">Lupa kata sandi?</a>
                </div>

                {{-- Tombol Masuk --}}
                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition transform active:scale-95 mt-2 text-base tracking-wide">
                    MASUK
                </button>
            </form>
            
            {{-- Footer --}}
            <div class="mt-8 text-center text-[11px] text-gray-400">
                &copy; 2025 Perpustakaan Digital SDN Pengampon 3
            </div>
        </div>
    </div>

</body>
</html>