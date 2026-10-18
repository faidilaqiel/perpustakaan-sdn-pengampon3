<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\Auth;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
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

        // Generate QR Code sebagai SVG
        $qrCode = QrCode::format('svg')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($qrContent);

        return view('petugas.scan.qr-code', compact('user', 'qrCode', 'qrContent'));
    }
}