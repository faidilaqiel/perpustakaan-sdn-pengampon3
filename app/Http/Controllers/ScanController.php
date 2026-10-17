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
    // ===== HALAMAN UTAMA SCAN =====
    public function index()
    {
        return view('petugas.scan.index');
    }

    // ===== PROSES HASIL SCAN =====
    public function hasil(Request $request)
    {
        $request->validate([
            'qr_code' => 'required'
        ]);

        // Cari user berdasarkan NISN atau NIP yang ada di dalam QR Code
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

    // ===== TAMPILKAN QR CODE ANGGOTA =====
    public function qrCode($id)
    {
        $user = User::findOrFail($id);
        
        // 🔥 PENGAMAN BUG: Cek jika NISN dan NIP kosong
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