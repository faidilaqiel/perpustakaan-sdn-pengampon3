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
<body class="bg-gray-50 min-h-screen flex items-center justify-center p-4 relative overflow-hidden">
    
    {{-- Background Dekorasi --}}
    <div class="absolute top-0 left-0 w-full h-64 bg-green-600 rounded-b-[40px] shadow-lg z-0"></div>

    <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md p-8 z-10 border border-gray-100 relative mt-20">
        
        {{-- Logo --}}
        <div class="text-center absolute -top-16 left-1/2 transform -translate-x-1/2">
            <div class="w-28 h-28 bg-white rounded-full flex items-center justify-center shadow-lg p-2 mx-auto border-4 border-white">
                <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-full h-full object-contain rounded-full">
            </div>
        </div>

        <div class="text-center mt-12 mb-8">
            <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">Perpustakaan Digital</h1>
            <p class="text-gray-500 text-sm mt-1 font-medium">SDN Pengampon 3</p>
        </div>

        @if(session('error'))
            <div class="bg-red-50 border border-red-200 text-red-600 p-3 rounded-lg mb-6 text-sm text-center font-medium">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5">
            @csrf
            
            <div>
                <label class="block text-gray-700 text-xs font-bold mb-2 uppercase tracking-wide">Username / NISN / NIP</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-user text-gray-400"></i>
                    </div>
                    <input type="text" name="username" required autofocus
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition"
                           placeholder="Masukkan NISN atau NIP">
                </div>
            </div>
            
            <div>
                <label class="block text-gray-700 text-xs font-bold mb-2 uppercase tracking-wide">Kata Sandi</label>
                <div class="relative">
                    <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                        <i class="fas fa-lock text-gray-400"></i>
                    </div>
                    <input type="password" name="password" required
                           class="w-full pl-10 pr-4 py-3 bg-gray-50 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-green-500 focus:bg-white transition"
                           placeholder="Masukkan kata sandi">
                </div>
            </div>

            <div class="flex items-center justify-between pt-1">
                <label class="flex items-center text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-green-600 bg-gray-100 border-gray-300 rounded focus:ring-green-500 mr-2">
                    Ingat Saya
                </label>
                <a href="#" class="text-sm text-green-600 hover:text-green-800 font-semibold hover:underline">Lupa Sandi?</a>
            </div>

            <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg transition transform hover:-translate-y-0.5 mt-2 text-lg">
                MASUK
            </button>
        </form>
        
        <div class="mt-8 text-center text-xs text-gray-400">
            &copy; {{ date('Y') }} SDN Pengampon 3. All Rights Reserved.
        </div>
    </div>

</body>
</html>