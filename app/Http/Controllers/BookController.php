<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookController extends Controller
{
    // ===== TAMPILKAN KATALOG BUKU (Siswa/Guru) =====
    public function katalog(Request $request)
    {
        $keyword = $request->input('q');

        $query = Book::with('category');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('author', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('code', 'LIKE', '%' . $keyword . '%');
            });
        }

        $books = $query->orderBy('title', 'asc')->get();
        $categories = Category::all();

        return view('siswa.katalog', compact('books', 'categories', 'keyword'));
    }

    // ===== TAMPILKAN DETAIL BUKU =====
    public function detail($id)
    {
        $book = Book::with('category')->findOrFail($id);
        return view('siswa.detail-buku', compact('book'));
    }

    // ===== PROSES PINJAM BUKU =====
    public function pinjam(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        if ($book->stock_available <= 0) {
            return back()->with('error', 'Maaf, stok buku ini sedang habis!');
        }

        Loan::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'loan_date' => now()->toDateString(),
            'due_date' => now()->addDays(7)->toDateString(),
            'status' => 'menunggu',
            'fine_amount' => 0,
            'payment_status' => 'lunas',
        ]);

        return redirect()->route('siswa.katalog')
                         ->with('success', '✅ Permintaan peminjaman berhasil! Menunggu konfirmasi petugas.');
    }

    // ============================================
    // ===== KELOLA BUKU (CRUD) — UNTUK PETUGAS ===
    // ============================================

    // ===== TAMPILKAN DAFTAR BUKU =====
    public function kelola(Request $request)
    {
        $keyword = $request->input('q');

        $query = Book::with('category');

        if ($keyword) {
            $query->where(function ($q) use ($keyword) {
                $q->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('author', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('code', 'LIKE', '%' . $keyword . '%');
            });
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(10);
        $totalBuku = Book::count();

        return view('petugas.kelola-buku.index', compact('books', 'keyword', 'totalBuku'));
    }

    // ===== TAMPILKAN FORM TAMBAH BUKU =====
    public function create()
    {
        $categories = Category::all();
        return view('petugas.kelola-buku.form', compact('categories'));
    }

    // ===== SIMPAN BUKU BARU =====
    public function store(Request $request)
    {
        $request->validate([
            'code' => 'required|unique:books,code|max:20',
            'title' => 'required|max:200',
            'author' => 'required|max:100',
            'publisher' => 'required|max:100',
            'year' => 'required|digits:4',
            'category_id' => 'required|exists:categories,id',
            'stock_total' => 'required|integer|min:1',
            'cover' => 'nullable|image|max:2048', // max 2MB
        ], [
            'code.required' => 'Kode buku wajib diisi.',
            'code.unique' => 'Kode buku sudah terdaftar.',
            'title.required' => 'Judul buku wajib diisi.',
            'author.required' => 'Penulis wajib diisi.',
            'publisher.required' => 'Penerbit wajib diisi.',
            'year.required' => 'Tahun terbit wajib diisi.',
            'year.digits' => 'Tahun harus 4 digit.',
            'category_id.required' => 'Kategori wajib dipilih.',
            'stock_total.required' => 'Stok wajib diisi.',
            'stock_total.min' => 'Stok minimal 1.',
            'cover.image' => 'File harus berupa gambar.',
            'cover.max' => 'Ukuran gambar maksimal 2MB.',
        ]);

        // Upload cover (kalau ada)
        $coverName = null;
        if ($request->hasFile('cover')) {
            $coverName = time() . '_' . $request->file('cover')->getClientOriginalName();
            $request->file('cover')->move(public_path('covers'), $coverName);
        }

        Book::create([
            'code' => $request->code,
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'category_id' => $request->category_id,
            'stock_total' => $request->stock_total,
            'stock_available' => $request->stock_total,
            'cover' => $coverName,
        ]);

        return redirect()->route('petugas.kelolaBuku')
                         ->with('success', '✅ Buku baru berhasil ditambahkan!');
    }

    // ===== TAMPILKAN FORM EDIT BUKU =====
    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = Category::all();
        return view('petugas.kelola-buku.form', compact('book', 'categories'));
    }

    // ===== SIMPAN PERUBAHAN BUKU =====
    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'code' => 'required|max:20|unique:books,code,' . $id,
            'title' => 'required|max:200',
            'author' => 'required|max:100',
            'publisher' => 'required|max:100',
            'year' => 'required|digits:4',
            'category_id' => 'required|exists:categories,id',
            'stock_total' => 'required|integer|min:1',
            'cover' => 'nullable|image|max:2048',
        ]);

        // Upload cover baru (kalau ada)
        $coverName = $book->cover;
        if ($request->hasFile('cover')) {
            // Hapus cover lama
            if ($book->cover && file_exists(public_path('covers/' . $book->cover))) {
                unlink(public_path('covers/' . $book->cover));
            }
            // Upload cover baru
            $coverName = time() . '_' . $request->file('cover')->getClientOriginalName();
            $request->file('cover')->move(public_path('covers'), $coverName);
        }

        // Hitung stok available baru (sesuaikan dengan yang sedang dipinjam)
        $sedangDipinjam = Loan::where('book_id', $book->id)
                              ->where('status', 'dipinjam')
                              ->count();
        $stockAvailable = $request->stock_total - $sedangDipinjam;

        $book->update([
            'code' => $request->code,
            'title' => $request->title,
            'author' => $request->author,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'category_id' => $request->category_id,
            'stock_total' => $request->stock_total,
            'stock_available' => max(0, $stockAvailable),
            'cover' => $coverName,
        ]);

        return redirect()->route('petugas.kelolaBuku')
                         ->with('success', '✅ Buku berhasil diperbarui!');
    }

    // ===== HAPUS BUKU =====
    public function destroy($id)
    {
        $book = Book::findOrFail($id);

        // Cek apakah buku sedang dipinjam
        $sedangDipinjam = Loan::where('book_id', $book->id)
                              ->whereIn('status', ['dipinjam', 'menunggu'])
                              ->count();
        if ($sedangDipinjam > 0) {
            return back()->with('error', '❌ Buku tidak bisa dihapus! Sedang ada peminjaman aktif.');
        }

        // Hapus cover
        if ($book->cover && file_exists(public_path('covers/' . $book->cover))) {
            unlink(public_path('covers/' . $book->cover));
        }

        $book->delete();

        return redirect()->route('petugas.kelolaBuku')
                         ->with('success', '✅ Buku berhasil dihapus!');
    }
}