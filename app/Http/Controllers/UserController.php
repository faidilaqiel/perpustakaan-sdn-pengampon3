<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Loan;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\File;

class UserController extends Controller
{
    // ===== TAMPILKAN DAFTAR ANGGOTA =====
    public function index(Request $request)
    {
        $keyword = $request->input('q');
        $filterRole = $request->input('role');
        $filterClass = $request->input('class'); 

        $query = User::query();

        if ($filterRole && in_array($filterRole, ['siswa', 'guru'])) {
            $query->where('role', $filterRole);
        } else {
            $query->whereIn('role', ['siswa', 'guru']);
        }

        if ($filterClass) {
            $query->where('class', $filterClass);
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
                       ->paginate(10)
                       ->withQueryString(); 

        $listKelas = User::where('role', 'siswa')
                         ->whereNotNull('class')
                         ->where('class', '!=', '')
                         ->distinct()
                         ->pluck('class')
                         ->sort();

        $totalSiswa = User::where('role', 'siswa')->count();
        $totalGuru = User::where('role', 'guru')->count();
        $totalAnggota = $totalSiswa + $totalGuru;

        return view('petugas.kelola-anggota.index', compact(
            'users', 'keyword', 'filterRole', 'filterClass', 'listKelas', 'totalSiswa', 'totalGuru', 'totalAnggota'
        ));
    }

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
            // Validasi NISN: Hanya angka, maksimal 10 digit
            'nisn' => 'required_if:role,siswa|numeric|digits_between:5,10|unique:users,nisn|nullable',
            // Validasi NIP: Hanya angka, maksimal 18 digit (sesuai standar NIP)
            'nip' => 'required_if:role,guru|numeric|digits_between:5,18|unique:users,nip|nullable',
            'password' => 'required|min:6',
            // Validasi Foto: Hanya jpg, jpeg, png, maksimal 2MB
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'role.required' => 'Role wajib dipilih.',
            'name.required' => 'Nama wajib diisi.',
            'gender.required' => 'Jenis kelamin wajib dipilih.',
            'class.required_if' => 'Kelas wajib diisi untuk siswa.',
            'nisn.required_if' => 'NISN wajib diisi untuk siswa.',
            'nisn.numeric' => 'NISN harus berupa angka.',
            'nisn.digits_between' => 'NISN harus 5-10 digit.',
            'nisn.unique' => 'NISN sudah terdaftar.',
            'nip.required_if' => 'NIP wajib diisi untuk guru.',
            'nip.numeric' => 'NIP harus berupa angka.',
            'nip.digits_between' => 'NIP harus 5-18 digit.',
            'nip.unique' => 'NIP sudah terdaftar.',
            'password.required' => 'Password wajib diisi.',
            'password.min' => 'Password minimal 6 karakter.',
            'photo.image' => 'File harus berupa gambar.',
            'photo.mimes' => 'Format foto harus JPG, JPEG, atau PNG.',
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

    public function edit($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['siswa', 'guru'])) {
            return redirect()->route('petugas.kelolaAnggota')
                             ->with('error', 'Tidak bisa edit petugas/kepsek dari sini.');
        }

        return view('petugas.kelola-anggota.form', compact('user'));
    }

    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'role' => 'required|in:siswa,guru',
            'name' => 'required|max:100',
            'gender' => 'required|in:L,P',
            'class' => 'required_if:role,siswa|max:10|nullable',
            'nisn' => 'required_if:role,siswa|numeric|digits_between:5,10|unique:users,nisn,' . $id . '|nullable',
            'nip' => 'required_if:role,guru|numeric|digits_between:5,18|unique:users,nip,' . $id . '|nullable',
            'password' => 'nullable|min:6',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $photoName = $user->photo;
        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
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

    // ===== HAPUS ANGGOTA (ANTI BUG RELASI) =====
    public function destroy($id)
    {
        $user = User::findOrFail($id);

        if (!in_array($user->role, ['siswa', 'guru'])) {
            return back()->with('error', 'Tidak bisa hapus petugas/kepsek dari sini.');
        }

        // Cek apakah user masih punya pinjaman
        $peminjamanAktif = Loan::where('user_id', $user->id)
                               ->whereIn('status', ['dipinjam', 'menunggu'])
                               ->count();
                               
        if ($peminjamanAktif > 0) {
            return back()->with('error', '❌ Tidak bisa hapus! Anggota masih punya peminjaman aktif.');
        }

        // Hapus foto fisik jika ada
        if ($user->photo && file_exists(public_path('photos/' . $user->photo))) {
            unlink(public_path('photos/' . $user->photo));
        }

        // HAPUS SEMUA RIWAYAT PINJAMAN (SELESAI/LUNAS) AGAR TIDAK JADI DATA YATIM
        Loan::where('user_id', $user->id)->delete();

        // Hapus user
        $user->delete();

        return redirect()->route('petugas.kelolaAnggota')
                         ->with('success', '✅ Anggota dan riwayat pinjamannya berhasil dihapus!');
    }
}