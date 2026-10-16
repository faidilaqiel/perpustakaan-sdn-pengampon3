<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Loan;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // ===== TAMPILKAN DAFTAR ANGGOTA =====
    public function index(Request $request)
    {
        $keyword = $request->input('q');
        $filterRole = $request->input('role');

        $query = User::query();

        if ($filterRole && in_array($filterRole, ['siswa', 'guru'])) {
            $query->where('role', $filterRole);
        } else {
            $query->whereIn('role', ['siswa', 'guru']);
        }

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('name', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('nisn', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('nip', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('class', 'LIKE', '%' . $keyword . '%');
            });
        }

        $users = $query->orderBy('role', 'asc')
                       ->orderBy('name', 'asc')
                       ->paginate(10);

        $totalSiswa = User::where('role', 'siswa')->count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalAnggota = $totalSiswa + $totalGuru;

        return view('petugas.kelola-anggota.index', compact(
            'users', 'keyword', 'filterRole', 'totalSiswa', 'totalGuru', 'totalAnggota'
        ));
    }

    // ===== TAMPILKAN FORM TAMBAH ANGGOTA =====
    public function create()
    {
        return view('petugas.kelola-anggota.form');
    }

    // ===== SIMPAN ANGGOTA BARU =====
    public function store(Request $request)
    {
        $request->validate([
            'role' => 'required|in:siswa,guru',
            'name' => 'required|max:100',
            'gender' => 'required|in:L,P',
            'class' => 'required_if:role,siswa|max:10|nullable',
            'nisn' => 'required_if:role,siswa|unique:users,nisn|max:10|nullable',
            'nip' => 'required_if:role,guru|unique:users,nip|max:30|nullable',
            'password' => 'required|min:6',
            'photo' => 'nullable|image|max:2048',
        ], [
            'role.required' => 'Role wajib dipilih.',
            'name.required' => 'Nama wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'class.required_if' => 'Kelas wajib diisi untuk siswa.',
            'nisn.required_if' => 'NISN wajib diisi untuk siswa.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nisn.max' => 'NISN maksimal 10 digit.',
            'nip.required_if' => 'NIP wajib diisi untuk guru.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'nip.max' => 'NIP maksimal 30 digit.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.max' => 'Ukuran foto maksimal 2MB.',
        ]);

        $photoName = null;
        if ($request->hasFile('photo')) {
            $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->move(public_path('photos'), $photoName);
        }

        User::create([
            'role' => $request->role,
            'name' => $request->name,
            'gender' => $request->gender,
            'class' => $request->role === 'siswa' ? $request->class : null,
            'nisn' => $request->role === 'siswa' ? $request->nisn : null,
            'nip' => $request->role === 'guru' ? $request->nip : null,
            'password' => Hash::make($request->password),
            'photo' => $photoName,
            'status' => 'aktif',
        ]);

        return redirect()->route('petugas.kelolaAnggota')
                         ->with('success', '✅ Anggota baru berhasil ditambahkan!');
    }

    // ===== TAMPILKAN FORM EDIT ANGGOTA =====
    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['siswa', 'guru'])) {
            return redirect()->route('petugas.kelolaAnggota')
                             ->with('error', 'Tidak bisa edit petugas/kepsek dari sini.');
        }

        return view('petugas.kelola-anggota.form', compact('user'));
    }

    // ===== SIMPAN PERUBAHAN ANGGOTA =====
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:siswa,guru',
            'name' => 'required|max:100',
            'gender' => 'required|in:L,P',
            'class' => 'required_if:role,siswa|max:10|nullable',
            'nisn' => 'required_if:role,siswa|unique:users,nisn,' . $id . '|max:10|nullable',
            'nip' => 'required_if:role,guru|unique:users,nip,' . $id . '|max:30|nullable',
            'password' => 'nullable|min:6',
            'photo' => 'nullable|image|max:2048',
        ]);

        $photoName = $user->photo;
        if ($request->hasFile('photo')) {
            if ($user->photo && file_exists(public_path('photos/' . $user->photo))) {
                unlink(public_path('photos/' . $user->photo));
            }
            $photoName = time() . '_' . $request->file('photo')->getClientOriginalName();
            $request->file('photo')->move(public_path('photos'), $photoName);
        }

        $data = [
            'role' => $request->role,
            'name' => $request->name,
            'gender' => $request->gender,
            'class' => $request->role === 'siswa' ? $request->class : null,
            'nisn' => $request->role === 'siswa' ? $request->nisn : null,
            'nip' => $request->role === 'guru' ? $request->nip : null,
            'photo' => $photoName,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        return redirect()->route('petugas.kelolaAnggota')
                         ->with('success', '✅ Data anggota berhasil diperbarui!');
    }

    // ===== HAPUS ANGGOTA =====
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['siswa', 'guru'])) {
            return back()->with('error', 'Tidak bisa hapus petugas/kepsek dari sini.');
        }

        $peminjamanAktif = Loan::where('user_id', $user->id)
                               ->whereIn('status', ['dipinjam', 'menunggu'])
                               ->count();
        if ($peminjamanAktif > 0) {
            return back()->with('error', '❌ Tidak bisa hapus! Anggota masih punya peminjaman aktif.');
        }

        if ($user->photo && file_exists(public_path('photos/' . $user->photo))) {
            unlink(public_path('photos/' . $user->photo));
        }

        $user->delete();

        return redirect()->route('petugas.kelolaAnggota')
                         ->with('success', '✅ Anggota berhasil dihapus!');
    }

    // ===== CETAK KARTU ANGGOTA (PDF) =====
    public function cetakKartu($id)
    {
        $user = User::findOrFail($id);

        // Cegah cetak kartu petugas/kepsek (hanya siswa & guru)
        if (!in_array($user->role, ['siswa', 'guru'])) {
            return redirect()->route('petugas.kelolaAnggota')
                             ->with('error', 'Hanya bisa cetak kartu untuk siswa & guru.');
        }

        // Tentukan konten QR Code (NISN untuk siswa, NIP untuk guru)
        $qrContent = $user->role === 'siswa' ? $user->nisn : $user->nip;

        // Generate QR Code sebagai SVG (tidak butuh Imagick)
        $qrCode = \SimpleSoftwareIO\QrCode\Facades\QrCode::format('svg')
                    ->size(300)
                    ->margin(1)
                    ->errorCorrection('H')
                    ->generate($qrContent);

        $qrCodeBase64 = 'data:image/svg+xml;base64,' . base64_encode($qrCode);

        // Load view khusus kartu
        $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('petugas.kartu-anggota', compact('user', 'qrCodeBase64'));

        // Atur ukuran kertas custom (85mm x 54mm = ukuran kartu ATM)
        // 85mm = 240.94 pt, 54mm = 153.07 pt
        $pdf->setPaper([0, 0, 240.94, 153.07], 'landscape');

        // Stream PDF (buka di tab baru)
        $namaFile = 'kartu-anggota-' . ($user->nisn ?? $user->nip) . '.pdf';
        return $pdf->stream($namaFile);
    }
}