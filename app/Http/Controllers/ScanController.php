<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class ScanController extends Controller
{
    public function index()
    {
        return view('petugas.scan.index');
    }

    // Proses setelah QR discan atau NISN diketik
    public function hasil(Request $request)
    {
        $request->validate([
            'qr_code' => 'required'
        ]);

        // Cari user berdasarkan NISN atau NIP yang di-scan
        $user = User::where('nisn', $request->qr_code)
                    ->orWhere('nip', $request->qr_code)
                    ->first();

        if (!$user) {
            return back()->with('error', '❌ QR Code tidak dikenali! Anggota tidak ditemukan.');
        }

        // Catat kunjungan ke database
        Kunjungan::create([
            'user_id' => $user->id,
            'tanggal' => Carbon::now()->toDateString(),
            'jam_masuk' => Carbon::now()->toTimeString(),
            'keterangan' => 'Datang ke Perpustakaan',
        ]);

        return back()->with('success', '✅ Berhasil! Selamat datang, ' . $user->name);
    }

    // Tampilkan QR Code Anggota (untuk dicetak)
    public function qrCode($id)
    {
        $user = User::findOrFail($id);

        // Pengaman jika NISN/NIP kosong
        $qrContent = $user->nisn ?? $user->nip;
        if (empty($qrContent)) {
            return back()->with('error', 'Gagal membuat QR Code: Siswa/Guru ini belum memiliki NISN atau NIP.');
        }

        // Generate QR Code SVG langsung via BaconQrCode (tidak bergantung facade/CDN)
        try {
            $fill = \BaconQrCode\Renderer\RendererStyle\Fill::uniformColor(
                new \BaconQrCode\Renderer\Color\Rgb(255, 255, 255),  // background putih
                new \BaconQrCode\Renderer\Color\Rgb(6, 95, 70)       // modul hijau gelap
            );
            $renderer = new \BaconQrCode\Renderer\ImageRenderer(
                new \BaconQrCode\Renderer\RendererStyle\RendererStyle(280, 1, null, null, $fill),
                new \BaconQrCode\Renderer\Image\SvgImageBackEnd()
            );
            $writer = new \BaconQrCode\Writer($renderer);
            $svgRaw  = $writer->writeString($qrContent);

            // Hapus XML declaration agar bisa di-embed inline di HTML
            $qrSvg = preg_replace('/<\?xml[^?]*\?>\s*/i', '', $svgRaw);
        } catch (\Exception $e) {
            $qrSvg = null;
        }

        return view('petugas.scan.qr-code', compact('user', 'qrContent', 'qrSvg'));
    }
}
