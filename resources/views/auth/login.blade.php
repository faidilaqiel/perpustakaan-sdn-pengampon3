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
        /* Mengatur background agar menutupi seluruh layar */
        .bg-custom {
            background-image: url('{{ asset('images/bg-login.jpg') }}');
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
        }
    </style>
</head>
<body class="bg-custom min-h-screen flex items-center justify-center p-4 relative">

    {{-- Lapisan Gelap Transparan agar Tulisan Terbaca --}}
    <div class="absolute inset-0 bg-black/20"></div>

    {{-- Kartu Form Login --}}
    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 z-10 border border-gray-100 relative">
        
        {{-- Logo & Judul --}}
        <div class="text-center mb-8">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-20 h-20 mx-auto object-contain mb-3 drop-shadow-md">
            <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Masuk ke Akun Anda</h1>
            <p class="text-gray-500 text-sm mt-1">Silakan login untuk melanjutkan ke aplikasi.</p>
        </div>

        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-6 text-sm font-medium text-center">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form Login --}}
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
                    <input type="password" name="password" id="login-password" required
                           class="w-full pl-10 pr-10 py-3 bg-gray-50 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition"
                           placeholder="Masukkan kata sandi">
                    <button type="button" onclick="togglePassword('login-password', 'eye-icon-login')" class="absolute inset-y-0 right-0 pr-3.5 flex items-center text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-eye" id="eye-icon-login"></i>
                    </button>
                </div>
            </div>

            <div class="flex items-center justify-between pt-2">
                <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 mr-2">
                    Ingat saya
                </label>
                <a href="#" class="text-sm text-green-600 hover:text-green-800 font-semibold hover:underline">Lupa kata sandi?</a>
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-lg shadow-lg transition transform hover:-translate-y-0.5 mt-2 text-lg">
                MASUK
            </button>
        </form>
        
        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; Copyright <strong>SDN PENGAMPON 3</strong> All Rights Reserved
        </div>
    </div>

    <script>
        function togglePassword(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            if (!input || !icon) return;

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>