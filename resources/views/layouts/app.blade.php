<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Perpustakaan Digital')</title>
    <!-- Favicon dengan nama file baru -->
    <link rel="icon" href="{{ asset('images/logo-perpus.png') }}" type="image/png">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        input { font-size: 16px !important; }

        /* ===== NOTIFICATION STYLES ===== */
        .notif-bell { position: relative; cursor: pointer; }
        .notif-badge {
            position: absolute; top: -4px; right: -6px;
            background: #ef4444; color: #fff; font-size: 10px; font-weight: 700;
            min-width: 18px; height: 18px; border-radius: 999px;
            display: flex; align-items: center; justify-content: center;
            padding: 0 4px; line-height: 1;
            box-shadow: 0 0 0 2px #fff;
            animation: notifPulse 2s infinite;
        }
        @keyframes notifPulse {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.15); }
        }
        .notif-badge.hidden { display: none; }

        /* Dropdown panel */
        .notif-dropdown {
            position: absolute; top: calc(100% + 8px); right: 0;
            width: 340px; max-height: 400px;
            background: #fff; border-radius: 12px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.15), 0 2px 8px rgba(0,0,0,0.08);
            z-index: 9999; overflow: hidden;
            transform: translateY(-8px); opacity: 0;
            pointer-events: none;
            transition: all 0.25s cubic-bezier(0.4,0,0.2,1);
        }
        .notif-dropdown.show {
            transform: translateY(0); opacity: 1; pointer-events: auto;
        }
        .notif-dropdown-header {
            padding: 14px 16px; font-weight: 700; font-size: 14px;
            border-bottom: 1px solid #f1f5f9; display: flex;
            align-items: center; justify-content: space-between;
            color: #1e293b;
        }
        .notif-dropdown-body {
            max-height: 320px; overflow-y: auto;
        }
        .notif-item {
            display: flex; align-items: flex-start; gap: 12px;
            padding: 12px 16px; border-bottom: 1px solid #f8fafc;
            transition: background 0.15s;
            text-decoration: none; color: inherit;
        }
        .notif-item:hover { background: #f0fdf4; }
        .notif-item-icon {
            width: 36px; height: 36px; border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .notif-item-content { flex: 1; min-width: 0; }
        .notif-item-title { font-size: 13px; font-weight: 600; color: #1e293b; line-height: 1.3; }
        .notif-item-desc { font-size: 12px; color: #64748b; margin-top: 2px; line-height: 1.3; }
        .notif-item-time { font-size: 11px; color: #94a3b8; margin-top: 3px; }
        .notif-empty {
            padding: 32px 16px; text-align: center; color: #94a3b8; font-size: 13px;
        }
        .notif-empty i { font-size: 28px; margin-bottom: 8px; display: block; }

        /* Toast notifications */
        #toast-container {
            position: fixed; top: 80px; right: 16px; z-index: 99999;
            display: flex; flex-direction: column; gap: 8px;
            pointer-events: none;
        }
        .toast {
            background: #fff; border-radius: 12px; padding: 14px 18px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.12), 0 2px 8px rgba(0,0,0,0.06);
            display: flex; align-items: flex-start; gap: 12px;
            max-width: 360px; pointer-events: auto;
            animation: toastSlideIn 0.4s cubic-bezier(0.4,0,0.2,1), toastFadeOut 0.4s 4.6s forwards;
            border-left: 4px solid #22c55e;
        }
        .toast.toast-warning { border-left-color: #f59e0b; }
        .toast.toast-info { border-left-color: #3b82f6; }
        .toast.toast-error { border-left-color: #ef4444; }
        .toast-icon {
            width: 32px; height: 32px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; flex-shrink: 0;
        }
        .toast-body { flex: 1; }
        .toast-title { font-size: 13px; font-weight: 700; color: #1e293b; }
        .toast-desc { font-size: 12px; color: #64748b; margin-top: 2px; }
        @keyframes toastSlideIn {
            from { transform: translateX(120%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }
        @keyframes toastFadeOut {
            from { opacity: 1; transform: translateX(0); }
            to { opacity: 0; transform: translateX(60px); }
        }

        /* Mobile notif dropdown */
        @media (max-width: 767px) {
            .notif-dropdown { width: calc(100vw - 32px); right: -8px; }
        }
    </style>
</head>
<body class="bg-gray-50 min-h-screen flex overflow-hidden">

    {{-- TOAST CONTAINER --}}
    <div id="toast-container"></div>

    {{-- SIDEBAR (LAPTOP) --}}
    <aside class="hidden md:flex flex-col w-64 bg-green-800 text-white h-screen fixed top-0 left-0 z-50 shadow-xl">
        <div class="h-20 flex items-center gap-3 px-6 bg-green-900 border-b border-green-700">
            <img src="{{ asset('images/logo-sdn.png') }}" alt="Logo" class="w-10 h-10 bg-white rounded-full p-1">
            <div>
                <h1 class="font-bold text-sm leading-tight">Perpustakaan Digital</h1>
                <p class="text-xs text-green-300">SDN Pengampon 3</p>
            </div>
        </div>

        <nav class="flex-1 px-4 py-6 space-y-2 overflow-y-auto">
            @if(Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.dashboard') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-home w-5 text-center"></i> Dashboard</a>
                <a href="{{ route('petugas.kelolaBuku') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kelolaBuku') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-book w-5 text-center"></i> Daftar Buku</a>
                <a href="{{ route('petugas.kategori.index') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kategori.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-folder w-5 text-center"></i> Kategori Buku</a>
                <a href="{{ route('petugas.sirkulasi') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.sirkulasi') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-exchange-alt w-5 text-center"></i> Sirkulasi</a>
                <a href="{{ route('petugas.kelolaAnggota') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.kelolaAnggota') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-users w-5 text-center"></i> Data Anggota</a>
                <a href="{{ route('petugas.scan') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('petugas.scan') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-qrcode w-5 text-center"></i> Scan QR</a>
            @elseif(Auth::user()->role === 'kepsek')
                <a href="{{ route('kepsek.laporan') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('kepsek.laporan') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-chart-pie w-5 text-center"></i> Laporan</a>
            @endif
            <a href="{{ route('profil.edit') }}" class="flex items-center gap-3 px-4 py-3 rounded-lg {{ request()->routeIs('profil.*') ? 'bg-green-600 font-semibold' : 'hover:bg-green-700 transition' }}"><i class="fas fa-user w-5 text-center"></i> Profil Saya</a>
        </nav>

        <div class="p-4 bg-green-900 border-t border-green-700 flex items-center gap-3">
            @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
            @else
                <div class="w-10 h-10 bg-green-500 rounded-full flex items-center justify-center text-lg font-bold">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
            @endif
            <div class="flex-1">
                <p class="text-sm font-bold truncate">{{ Auth::user()->name }}</p>
                <p class="text-xs text-green-300 capitalize">{{ Auth::user()->role }}</p>
            </div>
        </div>
    </aside>

    {{-- KONTEN UTAMA --}}
    <div class="flex-1 flex flex-col h-screen md:ml-64 overflow-hidden relative">
        <header class="bg-white shadow-sm h-16 flex items-center justify-between px-4 md:px-6 z-40">
            <div class="hidden md:flex items-center bg-gray-100 rounded-lg px-4 py-2 w-96">
                <i class="fas fa-search text-gray-400 mr-2"></i>
                <input type="text" placeholder="Cari buku, anggota..." class="bg-transparent border-none outline-none text-sm w-full">
            </div>
            
            {{-- MOBILE HEADER --}}
            <div class="flex md:hidden items-center gap-3 w-full justify-between">
                <div class="flex items-center gap-3">
                    @if(Auth::user()->photo && file_exists(public_path('photos/' . Auth::user()->photo)))
                        <img src="{{ asset('photos/' . Auth::user()->photo) }}" class="w-10 h-10 rounded-full object-cover border-2 border-green-500">
                    @else
                        <div class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center font-bold text-lg border border-green-200">{{ substr(Auth::user()->name ?? 'A', 0, 1) }}</div>
                    @endif
                    <div>
                        <p class="text-xs text-gray-500">Selamat datang,</p>
                        <p class="text-sm font-bold text-gray-800 truncate max-w-[150px]">{{ Auth::user()->name }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    {{-- NOTIF BELL MOBILE --}}
                    <div class="notif-bell" id="notifBellMobile" onclick="toggleNotifDropdown('mobile')">
                        <div class="w-9 h-9 bg-green-50 text-green-600 rounded-full flex items-center justify-center hover:bg-green-100 transition">
                            <i class="fas fa-bell text-base"></i>
                        </div>
                        <span class="notif-badge hidden" id="notifBadgeMobile">0</span>
                        <div class="notif-dropdown" id="notifDropdownMobile">
                            <div class="notif-dropdown-header">
                                <span><i class="fas fa-bell mr-2 text-green-500"></i>Notifikasi</span>
                                <span class="text-xs font-normal text-gray-400" id="notifCountTextMobile">0 baru</span>
                            </div>
                            <div class="notif-dropdown-body" id="notifListMobile"></div>
                        </div>
                    </div>
                    <a href="{{ route('profil.edit') }}" class="w-9 h-9 bg-gray-100 text-gray-600 rounded-full flex items-center justify-center hover:bg-gray-200"><i class="fas fa-ellipsis-v"></i></a>
                </div>
            </div>

            {{-- DESKTOP HEADER --}}
            <div class="hidden md:flex items-center gap-3">
                {{-- NOTIF BELL DESKTOP --}}
                <div class="notif-bell relative" id="notifBellDesktop" onclick="toggleNotifDropdown('desktop')">
                    <div class="w-10 h-10 bg-green-50 text-green-600 rounded-full flex items-center justify-center hover:bg-green-100 transition cursor-pointer">
                        <i class="fas fa-bell text-lg"></i>
                    </div>
                    <span class="notif-badge hidden" id="notifBadgeDesktop">0</span>
                    <div class="notif-dropdown" id="notifDropdownDesktop">
                        <div class="notif-dropdown-header">
                            <span><i class="fas fa-bell mr-2 text-green-500"></i>Notifikasi</span>
                            <span class="text-xs font-normal text-gray-400" id="notifCountTextDesktop">0 baru</span>
                        </div>
                        <div class="notif-dropdown-body" id="notifListDesktop"></div>
                    </div>
                </div>
                <a href="{{ route('profil.edit') }}" class="w-10 h-10 bg-green-100 text-green-600 rounded-full flex items-center justify-center hover:bg-green-200 transition" title="Profil"><i class="fas fa-user"></i></a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-10 h-10 bg-red-100 text-red-600 rounded-full flex items-center justify-center hover:bg-red-200 transition" title="Logout"><i class="fas fa-sign-out-alt"></i></button>
                </form>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-4 md:p-6 pb-24 md:pb-6">
            @yield('content')
        </main>

        <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t border-gray-200 flex justify-around items-center h-16 z-50 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)]">
            @if(Auth::user()->role === 'petugas')
                <a href="{{ route('petugas.dashboard') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.dashboard') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-home text-lg mb-1"></i><span class="text-[10px] font-medium">Beranda</span></a>
                <a href="{{ route('petugas.kelolaBuku') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.kelolaBuku') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-book text-lg mb-1"></i><span class="text-[10px] font-medium">Buku</span></a>
                <a href="{{ route('petugas.sirkulasi') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.sirkulasi') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-exchange-alt text-lg mb-1"></i><span class="text-[10px] font-medium">Sirkulasi</span></a>
                <a href="{{ route('petugas.kelolaAnggota') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('petugas.kelolaAnggota') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-users text-lg mb-1"></i><span class="text-[10px] font-medium">Anggota</span></a>
            @elseif(Auth::user()->role === 'kepsek')
                <a href="{{ route('kepsek.laporan') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('kepsek.laporan') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-chart-pie text-lg mb-1"></i><span class="text-[10px] font-medium">Laporan</span></a>
            @endif
            <a href="{{ route('profil.edit') }}" class="flex flex-col items-center justify-center w-full h-full {{ request()->routeIs('profil.*') ? 'text-green-600' : 'text-gray-400 hover:text-green-500' }}"><i class="fas fa-user text-lg mb-1"></i><span class="text-[10px] font-medium">Akun</span></a>
        </nav>
    </div>

    @include('partials.botpress-script')

    {{-- ===== NOTIFICATION POLLING SCRIPT ===== --}}
    <script>
    (function() {
        const userRole = @json(Auth::user()->role ?? '');
        let lastKnownIds = [];
        let isFirstLoad = true;

        // ── Toast helper ──
        function showToast(title, desc, type = 'success') {
            const container = document.getElementById('toast-container');
            const colors = {
                success: { bg: '#f0fdf4', icon: '#22c55e', iconBg: '#dcfce7', fa: 'fa-check-circle' },
                warning: { bg: '#fffbeb', icon: '#f59e0b', iconBg: '#fef3c7', fa: 'fa-exclamation-triangle' },
                info:    { bg: '#eff6ff', icon: '#3b82f6', iconBg: '#dbeafe', fa: 'fa-info-circle' },
                error:   { bg: '#fef2f2', icon: '#ef4444', iconBg: '#fee2e2', fa: 'fa-times-circle' },
            };
            const c = colors[type] || colors.success;
            const toast = document.createElement('div');
            toast.className = 'toast toast-' + type;
            toast.innerHTML = `
                <div class="toast-icon" style="background:${c.iconBg};color:${c.icon}">
                    <i class="fas ${c.fa}"></i>
                </div>
                <div class="toast-body">
                    <div class="toast-title">${title}</div>
                    <div class="toast-desc">${desc}</div>
                </div>`;
            container.appendChild(toast);
            setTimeout(() => { if (toast.parentNode) toast.remove(); }, 5200);
        }

        // ── Toggle dropdown ──
        window.toggleNotifDropdown = function(variant) {
            const dd = document.getElementById('notifDropdown' + (variant === 'mobile' ? 'Mobile' : 'Desktop'));
            // close the other one
            const other = document.getElementById('notifDropdown' + (variant === 'mobile' ? 'Desktop' : 'Mobile'));
            if (other) other.classList.remove('show');
            dd.classList.toggle('show');
        };

        // Close dropdown on click outside
        document.addEventListener('click', function(e) {
            ['Desktop', 'Mobile'].forEach(v => {
                const bell = document.getElementById('notifBell' + v);
                const dd = document.getElementById('notifDropdown' + v);
                if (bell && dd && !bell.contains(e.target)) {
                    dd.classList.remove('show');
                }
            });
        });

        // ── Render items in both dropdowns ──
        function renderNotifItems(items, count) {
            ['Desktop', 'Mobile'].forEach(variant => {
                const badge = document.getElementById('notifBadge' + variant);
                const countText = document.getElementById('notifCountText' + variant);
                const list = document.getElementById('notifList' + variant);
                if (!badge || !list) return;

                if (count > 0) {
                    badge.textContent = count > 99 ? '99+' : count;
                    badge.classList.remove('hidden');
                } else {
                    badge.classList.add('hidden');
                }
                if (countText) countText.textContent = count + ' baru';

                if (items.length === 0) {
                    list.innerHTML = `<div class="notif-empty"><i class="fas fa-bell-slash"></i>Tidak ada notifikasi baru</div>`;
                    return;
                }

                list.innerHTML = items.map(item => item.html).join('');
            });
        }

        // ══════════════════════════════════════════════
        //  PETUGAS: Poll pending loan requests
        // ══════════════════════════════════════════════
        @if(Auth::user()->role === 'petugas')
        function pollPetugasNotif() {
            fetch('{{ route("petugas.api.cekNotifikasi") }}')
                .then(r => r.json())
                .then(data => {
                    const items = (data.items || []).map(item => {
                        const kelas = item.user_class ? ` (${item.user_class})` : '';
                        return {
                            id: item.id,
                            html: `<a href="{{ route('petugas.sirkulasi') }}?status=menunggu" class="notif-item">
                                <div class="notif-item-icon" style="background:#fef3c7;color:#f59e0b"><i class="fas fa-clock"></i></div>
                                <div class="notif-item-content">
                                    <div class="notif-item-title">Pengajuan Pinjam Baru</div>
                                    <div class="notif-item-desc"><b>${item.user_name}</b>${kelas} ingin meminjam <b>"${item.book_title}"</b></div>
                                    <div class="notif-item-time"><i class="far fa-clock mr-1"></i>${item.created_at}</div>
                                </div>
                            </a>`
                        };
                    });

                    // Detect truly new items (not on first load)
                    if (!isFirstLoad) {
                        const currentIds = items.map(i => i.id);
                        const newIds = currentIds.filter(id => !lastKnownIds.includes(id));
                        if (newIds.length > 0) {
                            const newest = data.items.find(i => i.id === newIds[0]);
                            if (newest) {
                                const kelas = newest.user_class ? ` (${newest.user_class})` : '';
                                showToast(
                                    '📚 Pengajuan Pinjam Baru!',
                                    `${newest.user_name}${kelas} ingin meminjam "${newest.book_title}"`,
                                    'warning'
                                );
                            }
                        }
                        lastKnownIds = currentIds;
                    } else {
                        lastKnownIds = items.map(i => i.id);
                        isFirstLoad = false;
                    }

                    renderNotifItems(items, data.count);
                })
                .catch(() => {});
        }
        pollPetugasNotif();
        setInterval(pollPetugasNotif, 15000);
        @endif

        // ══════════════════════════════════════════════
        //  SISWA / GURU: Poll own notification column
        // ══════════════════════════════════════════════
        @if(in_array(Auth::user()->role, ['siswa', 'guru']))
        let siswaNotifShown = false;

        function pollSiswaNotif() {
            fetch('{{ route("siswa.cekNotif") }}')
                .then(r => r.json())
                .then(data => {
                    if (!data.notifikasi) {
                        renderNotifItems([], 0);
                        siswaNotifShown = false;
                        return;
                    }

                    const parts = data.notifikasi.split('|');
                    const tipe = parts[0]; // disetujui / ditolak
                    const pesan = parts.slice(1).join('|');

                    let iconBg, iconColor, iconClass, toastType;
                    if (tipe === 'disetujui') {
                        iconBg = '#dcfce7'; iconColor = '#22c55e'; iconClass = 'fa-check-circle'; toastType = 'success';
                    } else {
                        iconBg = '#fee2e2'; iconColor = '#ef4444'; iconClass = 'fa-times-circle'; toastType = 'error';
                    }

                    const items = [{
                        html: `<div class="notif-item" onclick="clearSiswaNotif()" style="cursor:pointer">
                            <div class="notif-item-icon" style="background:${iconBg};color:${iconColor}"><i class="fas ${iconClass}"></i></div>
                            <div class="notif-item-content">
                                <div class="notif-item-title">${tipe === 'disetujui' ? 'Peminjaman Disetujui ✅' : 'Peminjaman Ditolak ❌'}</div>
                                <div class="notif-item-desc">${pesan}</div>
                                <div class="notif-item-time"><i class="fas fa-hand-pointer mr-1"></i>Klik untuk menghapus notifikasi</div>
                            </div>
                        </div>`
                    }];

                    renderNotifItems(items, 1);

                    // Show toast only once
                    if (!siswaNotifShown) {
                        showToast(
                            tipe === 'disetujui' ? '✅ Peminjaman Disetujui!' : '❌ Peminjaman Ditolak',
                            pesan,
                            toastType
                        );
                        siswaNotifShown = true;
                    }
                })
                .catch(() => {});
        }

        window.clearSiswaNotif = function() {
            fetch('{{ route("siswa.hapusNotif") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            }).then(() => {
                renderNotifItems([], 0);
                siswaNotifShown = false;
                // close dropdowns
                ['Desktop', 'Mobile'].forEach(v => {
                    const dd = document.getElementById('notifDropdown' + v);
                    if (dd) dd.classList.remove('show');
                });
                showToast('Notifikasi dihapus', 'Notifikasi telah dibersihkan', 'info');
            });
        };

        pollSiswaNotif();
        setInterval(pollSiswaNotif, 15000);
        @endif

    })();
    </script>
</body>
</html>