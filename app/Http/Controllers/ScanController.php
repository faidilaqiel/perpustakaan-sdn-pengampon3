<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Loan;
use App\Models\Book;

class ScanController extends Controller
{
    // ===== HALAMAN SCAN QR =====
    public function index()
    {
        return view('petugas.scan.index');
    }

    // ===== PROSES SCAN / HASIL =====
    public function hasil(Request $request)
    {
        $request->validate([
            'kode' => 'required',
        ], [
            'kode.required' => 'Kode QR / NISN / NIP wajib diisi!',
        ]);

        $kode = trim($request->kode);

        // Cari user berdasarkan NISN atau NIP
        $user = User::where('nisn', $kode)
                    ->orWhere('nip', $kode)
                    ->first();

        if (!$user) {
            return back()->with('error', '❌ Anggota dengan kode "' . $kode . '" tidak ditemukan!');
        }

        // Ambil peminjaman aktif
        $peminjamanAktif = Loan::with('book')
                               ->where('user_id', $user->id)
                               ->whereIn('status', ['dipinjam', 'menunggu'])
                               ->orderBy('created_at', 'desc')
                               ->get();

        // Ambil riwayat (5 terakhir)
        $riwayat = Loan::with('book')
                       ->where('user_id', $user->id)
                       ->where('status', 'dikembalikan')
                       ->orderBy('return_date', 'desc')
                       ->limit(5)
                       ->get();

        return view('petugas.scan.hasil', compact('user', 'peminjamanAktif', 'riwayat'));
    }

    // ===== TAMPILKAN QR CODE ANGGOTA =====
    public function qrCode($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['siswa', 'guru'])) {
            return redirect()->route('petugas.kelolaAnggota')
                             ->with('error', 'Hanya untuk siswa & guru.');
        }

        // Tentukan konten QR (NISN / NIP)
        $qrContent = $user->role === 'siswa' ? $user->nisn : $user->nip;

        // Generate QR Code sebagai SVG
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(400)
                    ->margin(2)
                    ->errorCorrection('H')
                    ->generate($qrContent);

        // Convert ke base64
        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);

        return view('petugas.scan.qr-code', compact('user', 'qrCodeBase64'));
    }
}