{{-- ================= FLOATING CHATBOT (BOTPRESS REAL-TIME SYNC) ================= --}}
<script src="https://cdn.botpress.cloud/webchat/v5.0/inject.js"></script>
<script src="https://files.bpcontent.cloud/2026/10/20/07/20261020071537-L4781M7K.js" defer></script>

@include('partials.botpress-data')

<script>
    const sendUserToBotpress = () => {
        if (window.botpress && typeof window.botpress.updateUser === 'function') {
            window.botpress.updateUser({
                name: @json($userName),
                data: {
                    name: @json($userName),
                    role: @json($userRole),
                    userName: @json($userName),
                    userRole: @json($userRole),
                    userClass: @json($userClass ?? ''),
                    isPetugas: @json($isPetugas),
                    isKepsek: @json($isKepsek),
                    isGuru: @json($isGuru),
                    isSiswa: @json($isSiswa),
                    currentUserGreeting: @json($currentUserGreeting),
                    totalBuku: @json($totalBukuCount),
                    totalAnggota: @json($totalAnggotaCount),
                    totalSiswa: @json($totalSiswaCount),
                    totalGuru: @json($totalGuruCount),
                    totalDipinjam: @json($totalDipinjamCount),
                    bukuSedangDipinjam: @json($peminjamanRealtimeText),
                    stokBuku: @json($stokBukuList),
                    bukuTahunTerbit: @json($bukuTahun2020Summary),
                    bukuPalingFavorit: @json($bukuFavoritSummary),
                    riwayatPeminjamanTerbaru: @json($riwayatTerbaruSummary),
                    scanQrGuide: @json($scanQrSummary),
                    tanggunganBukuSiswaGuru: @json($tanggunganBukuSummary),
                    fiturAplikasi: @json($fiturAplikasiSummary),
                    rincianSiswaPerKelas: @json($rincianSiswaPerKelasSummary),
                    daftarGuru: @json($daftarGuruSummary),
                    daftarSeluruhAnggota: @json($daftarAnggotaList),
                    aturanDendaHariLibur: @json($aturanDendaLibur),
                    statusPerpustakaan: @json($statusPerpustakaan),
                }
            });
            console.log("✅ Data user real-time disinkronkan ke Botpress:", @json($userName), "(Role:", @json($userRole), ")");
        }
    };

    const initBotpressSync = () => {
        if (window.botpress) {
            if (typeof window.botpress.on === 'function') {
                window.botpress.on('webchat:initialized', sendUserToBotpress);
                window.botpress.on('webchat:opened', sendUserToBotpress);
            }
            sendUserToBotpress();
        }
    };

    let bpAttempts = 0;
    const waitForBotpress = setInterval(() => {
        bpAttempts++;
        if (window.botpress && typeof window.botpress.updateUser === 'function') {
            clearInterval(waitForBotpress);
            initBotpressSync();
        } else if (bpAttempts > 30) {
            clearInterval(waitForBotpress);
        }
    }, 300);
</script>
