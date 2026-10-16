<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Loan;

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

    // ===== HASIL SCAN VIA URL (dari QR Code) =====
    public function hasilGet(Request $request)
    {
        $kode = trim($request->input('kode'));

        if (!$kode) {
            return redirect()->route('petugas.scan')
                             ->with('error', 'Kode tidak ditemukan di URL.');
        }

        // Cari user
        $user = User::where('nisn', $kode)
                    ->orWhere('nip', $kode)
                    ->first();

        if (!$user) {
            return redirect()->route('petugas.scan')
                             ->with('error', '❌ Anggota dengan kode "' . $kode . '" tidak ditemukan!');
        }

        // Ambil peminjaman aktif
        $peminjamanAktif = Loan::with('book')
                               ->where('user_id', $user->id)
                               ->whereIn('status', ['dipinjam', 'menunggu'])
                               ->orderBy('created_at', 'desc')
                               ->get();

        // Ambil riwayat
        $riwayat = Loan::with('book')
                       ->where('user_id', $user->id)
                       ->where('status', 'dikembalikan')
                       ->orderBy('return_date', 'desc')
                       ->limit(5)
                       ->get();

        return view('petugas.scan.hasil', compact('user', 'peminjamanAktif', 'riwayat'));
    }

    // ===== TAMPILKAN HALAMAN QR CODE (GENERATE DI BROWSER) =====
    public function qrCode($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['siswa', 'guru'])) {
            return redirect()->route('petugas.kelolaAnggota')
                             ->with('error', 'Hanya untuk siswa & guru.');
        }

        // Tentukan konten QR (NISN untuk siswa, NIP untuk guru)
        $qrContent = $user->role === 'siswa' ? $user->nisn : $user->nip;

        return view('petugas.scan.qr-code', compact('user', 'qrContent'));
    }
}