<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        input { font-size: 16px !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 md:p-0">

    {{-- Container Utama --}}
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-5xl flex flex-col md:flex-row overflow-hidden min-h-[600px]">
        
        {{-- KIRI: Branding & Ilustrasi (Tampil di Laptop) --}}
        <div class="hidden md:flex w-1/2 bg-green-50 flex-col justify-center items-center p-12 relative overflow-hidden">
            {{-- Background Dekorasi --}}
            <div class="absolute top-0 left-0 w-full h-full opacity-10">
                <img src="https://www.transparenttextures.com/patterns/cubes.png" alt="pattern">
            </div>
            
            <div class="relative z-10 text-center">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-32 h-32 mx-auto object-contain mb-6 drop-shadow-md">
                <h1 class="text-3xl font-extrabold text-green-900 tracking-tight mb-2">Perpustakaan Digital</h1>
                <h2 class="text-xl font-bold text-green-700 mb-4">SDN Pengampon 3</h2>
                <p class="text-green-800 font-medium">Akses mudah untuk membaca, belajar, dan berkembang</p>
            </div>
            
            {{-- Ilustrasi Buku (Bisa diganti gambar asli Anda) --}}
            <div class="mt-10 relative z-10">
                <img src="{{ asset('images/ilustrasi-buku.png') }}" alt="Ilustrasi" class="w-64 mx-auto drop-shadow-xl" onerror="this.style.display='none'">
            </div>
        </div>

        {{-- KANAN: Form Login --}}
        <div class="w-full md:w-1/2 p-8 md:p-12 flex flex-col justify-center">
            <div class="text-center mb-8 md:text-left">
                <h2 class="text-2xl font-bold text-gray-800">Masuk ke Akun Anda</h2>
                <p class="text-gray-500 text-sm mt-1">Silakan login untuk melanjutkan ke aplikasi.</p>
            </div>

            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-6 text-sm font-medium">
                    {{ session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
                @csrf
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Username / NISN / NIP</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-user text-gray-400"></i>
                        </div>
                        <input type="text" name="username" required autofocus
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition"
                               placeholder="Masukkan NISN atau NIP">
                    </div>
                </div>
                
                <div>
                    <label class="block text-gray-700 text-sm font-bold mb-2">Kata Sandi</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <i class="fas fa-lock text-gray-400"></i>
                        </div>
                        <input type="password" name="password" required
                               class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition"
                               placeholder="Masukkan kata sandi">
                    </div>
                </div>

                <div class="flex items-center justify-between pt-2">
                    <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                        <input type="checkbox" name="remember" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 mr-2">
                        Ingat saya
                    </label>
                    <a href="#" class="text-sm text-green-600 hover:text-green-800 font-semibold hover:underline">Lupa kata sandi?</a>
                </div>

                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-lg shadow-lg transition transform hover:-translate-y-0.5 mt-4 text-lg">
                    MASUK
                </button>
            </form>
            
            <div class="mt-12 text-center text-xs text-gray-400">
                &copy; 2025 Perpustakaan Digital SDN Pengampon 3
            </div>
        </div>
    </div>

</body>
</html>