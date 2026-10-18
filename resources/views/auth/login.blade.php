<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <!-- Kunci viewport agar tidak bisa di-zoom sembarangan -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Login - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        /* Mencegah iOS auto-zoom pada input */
        input[type="text"], input[type="password"] {
            font-size: 16px !important; 
        }
    </style>
</head>
<body class="bg-gray-100 min-h-screen flex items-center justify-center p-4 overflow-x-hidden">

    <div class="bg-white rounded-lg shadow-xl w-full max-w-md p-6 md:p-8">
        <div class="text-center mb-6">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-20 h-20 mx-auto object-contain mb-3">
            <h1 class="text-2xl font-bold text-gray-800">Perpustakaan Digital</h1>
            <p class="text-gray-500">SDN Pengampon 3</p>
        </div>

        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-800 px-4 py-3 rounded mb-4 text-sm">
                {{ session('error') }}
            </div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="mb-4">
                <label class="block text-gray-700 text-sm font-bold mb-2">Username / NISN / NIP</label>
                <input type="text" name="username" required autofocus
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Masukkan NISN atau NIP">
            </div>
            
            <div class="mb-6">
                <label class="block text-gray-700 text-sm font-bold mb-2">Kata Sandi</label>
                <input type="password" name="password" required
                       class="w-full px-4 py-3 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Masukkan kata sandi">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg shadow">
                MASUK
            </button>
        </form>
    </div>

</body>
</html>