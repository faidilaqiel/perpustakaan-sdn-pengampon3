<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Kunjungan;
use Illuminate\Support\Facades\Auth;

class KunjunganController extends Controller
{
    // Fungsi untuk menyimpan data kunjungan siswa
    public function simpan(Request $request)
    {
        // 1. Validasi: Pastikan kolom 'keterangan' diisi dan tidak lebih dari 255 karakter
        $request->validate([
            'keterangan' => 'required|max:255'
        ]);

        // 2. Simpan ke database
        Kunjungan::create([
            'user_id'    => Auth::id(), // Otomatis mengambil ID user yang sedang login
            'tanggal'    => date('Y-m-d'), // Tanggal hari ini
            'jam_masuk'  => date('H:i:s'), // Jam saat ini
            'keterangan' => $request->keterangan,
        ]);

        // 3. Kembalikan ke halaman sebelumnya dengan pesan sukses
        return back()->with('sukses', 'Terima kasih, kunjungan Anda sudah berhasil dicatat!');
    }
}