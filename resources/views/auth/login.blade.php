<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Perpustakaan Digital SDN Pengampon 3</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #e0f2f1 0%, #ffffff 50%, #e0f2f1 100%);
        }
        .logo-circle {
            box-shadow: 0 10px 25px -5px rgba(16, 185, 129, 0.3);
        }
        .tab-active {
            background-color: #ffffff;
            color: #047857;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .tab-inactive {
            color: #9ca3af;
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">

    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-md p-8">

        {{-- ===== LOGO ===== --}}
        <div class="flex justify-center mb-6">
            <div class="logo-circle bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-2xl p-4 w-24 h-24 flex items-center justify-center">
                <img src="{{ asset('images/logo-sdn.png') }}"
                     alt="Logo SDN Pengampon 3"
                     class="w-16 h-16 object-contain">
            </div>
        </div>

        {{-- ===== HEADER ===== --}}
        <div class="text-center mb-6">
            <h1 class="text-2xl font-extrabold text-gray-800 tracking-tight">PERPUSTAKAAN DIGITAL</h1>
            <p class="text-sm font-semibold text-emerald-600 mt-1">SDN PENGAMPON 3</p>
        </div>

        {{-- ===== TAB ROLE (Visual) ===== --}}
        <div class="bg-gray-100 rounded-xl p-1 flex mb-6">
            <button type="button" onclick="setRole('siswa')" id="tab-siswa"
                    class="tab-active flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1">
                🎓 Siswa
            </button>
            <button type="button" onclick="setRole('guru')" id="tab-guru"
                    class="tab-inactive flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1">
                👨‍🏫 Guru
            </button>
            <button type="button" onclick="setRole('petugas')" id="tab-petugas"
                    class="tab-inactive flex-1 py-2 px-3 rounded-lg text-sm font-semibold transition flex items-center justify-center gap-1">
                👤 Petugas
            </button>
        </div>

        {{-- ===== PESAN ERROR ===== --}}
        @if(session('error'))
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm">
                ❌ {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-2 rounded-lg mb-4 text-sm">
                ❌ {{ $errors->first() }}
            </div>
        @endif

        {{-- ===== FORM LOGIN ===== --}}
        <form method="POST" action="{{ route('login.post') }}">
            @csrf

            {{-- Field NISN/NIP --}}
            <div class="mb-4">
                <label id="label-username" class="block text-gray-700 font-bold mb-2 text-sm uppercase tracking-wide">
                    NISN (NOMOR INDUK SISWA)
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <input type="text" name="username" id="input-username"
                           value="{{ old('username') }}"
                           class="w-full pl-11 pr-4 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="Masukkan NISN siswa"
                           required autofocus>
                </div>
            </div>

            {{-- Field Password --}}
            <div class="mb-4">
                <label class="block text-gray-700 font-bold mb-2 text-sm uppercase tracking-wide">
                    KATA SANDI
                </label>
                <div class="relative">
                    <span class="absolute left-3 top-1/2 -translate-y-1/2 text-gray-400">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                        </svg>
                    </span>
                    <input type="password" name="password" id="input-password"
                           class="w-full pl-11 pr-12 py-3 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-transparent transition"
                           placeholder="Masukkan kata sandi"
                           required>
                    <button type="button" onclick="togglePassword()"
                            class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-400 hover:text-gray-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/>
                            <path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/>
                        </svg>
                    </button>
                </div>
            </div>

            {{-- Ingat Saya & Lupa Kata Sandi --}}
            <div class="flex items-center justify-between mb-6">
                <label class="flex items-center gap-2 text-sm text-gray-600 cursor-pointer">
                    <input type="checkbox" name="remember" class="w-4 h-4 text-emerald-600 border-gray-300 rounded focus:ring-emerald-500">
                    <span>Ingat Saya</span>
                </label>
                <a href="#" class="text-sm text-emerald-600 font-semibold hover:text-emerald-700 hover:underline">
                    Lupa Kata Sandi?
                </a>
            </div>

            {{-- Tombol MASUK --}}
            <button type="submit"
                    class="w-full bg-gradient-to-r from-emerald-600 to-emerald-700 hover:from-emerald-700 hover:to-emerald-800 text-white font-bold py-3.5 rounded-xl shadow-lg hover:shadow-xl transition-all flex items-center justify-center gap-2">
                <span>MASUK KE PERPUSTAKAAN</span>
                <svg xmlns="http://www.w3.org/2000/svg" class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M10.293 3.293a1 1 0 011.414 0l6 6a1 1 0 010 1.414l-6 6a1 1 0 01-1.414-1.414L14.586 11H3a1 1 0 110-2h11.586l-4.293-4.293a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>

        </form>

        {{-- ===== FOOTER ===== --}}
        <div class="text-center mt-6 text-xs text-gray-400">
            © 2026 <strong class="text-gray-500">SDN PENGAMPON 3</strong>. All Rights Reserved.
        </div>

    </div>

    {{-- ===== SCRIPT ===== --}}
    <script>
        // Toggle password visibility
        function togglePassword() {
            const input = document.getElementById('input-password');
            input.type = input.type === 'password' ? 'text' : 'password';
        }

        // Set role tab (visual only)
        function setRole(role) {
            // Reset semua tab
            ['siswa', 'guru', 'petugas'].forEach(r => {
                const tab = document.getElementById('tab-' + r);
                tab.classList.remove('tab-active');
                tab.classList.add('tab-inactive');
            });

            // Aktifkan tab yang dipilih
            const activeTab = document.getElementById('tab-' + role);
            activeTab.classList.remove('tab-inactive');
            activeTab.classList.add('tab-active');

            // Update label & placeholder
            const label = document.getElementById('label-username');
            const input = document.getElementById('input-username');

            if (role === 'siswa') {
                label.textContent = 'NISN (NOMOR INDUK SISWA)';
                input.placeholder = 'Masukkan NISN siswa';
            } else if (role === 'guru') {
                label.textContent = 'NIP (NOMOR INDUK PEGAWAI)';
                input.placeholder = 'Masukkan NIP guru';
            } else if (role === 'petugas') {
                label.textContent = 'NIP / USERNAME';
                input.placeholder = 'Masukkan NIP atau username';
            }

            input.focus();
        }

        // Auto-focus
        document.getElementById('input-username').focus();
    </script>

</body>
</html>