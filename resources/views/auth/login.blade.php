<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gradient-to-br from-blue-500 to-blue-700 min-h-screen flex items-center justify-center">

    <div class="bg-white rounded-2xl shadow-2xl p-8 w-full max-w-md">

        {{-- Header --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-bold text-blue-800">PERPUSTAKAAN DIGITAL</h1>
            <h2 class="text-lg font-semibold text-gray-700">SDN PENGAMPON 3</h2>
            <div class="mt-4">
                <img src="{{ asset('images/logo-sdn.png') }}" 
                     alt="Logo SDN Pengampon 3" 
                     class="w-24 h-24 mx-auto object-contain">
            </div>
        </div>

        {{-- Pesan Error --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded mb-4">
                {{ session('error') }}
            </div>
        @endif

        {{-- Form Login --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            <div class="mb-4">
                <label class="block text-gray-700 font-semibold mb-2">Username (NISN / NIK)</label>
                <input type="text" name="username" value="{{ old('username') }}"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Masukkan NISN atau NIK" required autofocus>
            </div>

            <div class="mb-6">
                <label class="block text-gray-700 font-semibold mb-2">Password</label>
                <input type="password" name="password"
                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                       placeholder="Masukkan password" required>
            </div>

            <button type="submit"
                    class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 rounded-lg transition">
                MASUK
            </button>
        </form>

        {{-- Footer Copyright --}}
        <div class="text-center mt-6 text-sm text-gray-500">
            © Copyright <strong>SDN PENGAMPON 3</strong>. All Rights Reserved
        </div>

    </div>

</body>
</html>