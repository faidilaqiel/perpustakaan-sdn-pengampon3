<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Book;
use App\Models\Category;
use App\Models\Loan;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class BookController extends Controller
{
    // ===== KATALOG BUKU SISWA/GURU =====
    public function katalog(Request $request)
    {
        $keyword = $request->input('q');
        $query = Book::query();

        if ($keyword) {
            $query->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('author', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('code', 'LIKE', '%' . $keyword . '%');
        }

        $books = $query->get();
        return view('siswa.katalog', compact('books', 'keyword'));
    }

    // ===== API AUTO-REFRESH KATALOG (AJAX) =====
    public function getLatestKatalog(Request $request)
    {
        $keyword = $request->input('q');
        $query = Book::with('category');

        if ($keyword) {
            $query->where('title', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('author', 'LIKE', '%' . $keyword . '%')
                  ->orWhere('code', 'LIKE', '%' . $keyword . '%');
        }

        $books = $query->get()->map(function($book) {
            return [
                'id' => $book->id,
                'code' => $book->code,
                'title' => $book->title,
                'author' => $book->author,
                'category' => $book->category->name ?? '-',
                'cover' => $book->cover,
                'stock_available' => $book->stock_available,
            ];
        });

        return response()->json(['books' => $books]);
    }

    // ===== DETAIL BUKU =====
    public function detail($id)
    {
        $book = Book::findOrFail($id);
        return view('siswa.detail-buku', compact('book'));
    }

    // ===== PROSES PINJAM BUKU =====
    public function pinjam($id)
    {
        $book = Book::findOrFail($id);

        if ($book->stock_available <= 0) {
            return back()->with('error', '❌ Stok buku habis!');
        }

        $cekPinjam = Loan::where('user_id', Auth::id())
                         ->where('book_id', $book->id)
                         ->whereIn('status', ['menunggu', 'dipinjam'])
                         ->first();

        if ($cekPinjam) {
            return back()->with('error', '❌ Anda masih meminjam buku ini dan belum mengembalikannya.');
        }

        Loan::create([
            'user_id' => Auth::id(),
            'book_id' => $book->id,
            'loan_date' => Carbon::now()->format('Y-m-d'),
            'due_date' => Carbon::now()->addDays(7)->format('Y-m-d'),
            'status' => 'menunggu',
        ]);

        return redirect()->route('siswa.katalog')
                         ->with('success', '✅ Pengajuan pinjam berhasil! Silakan tunggu konfirmasi dari Petugas.');
    }

    // ===== KELOLA BUKU (CRUD) PETUGAS =====
    public function kelola()
    {
        $books = Book::all();
        return view('petugas.kelola-buku.index', compact('books'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('petugas.kelola-buku.form', compact('categories'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:100',
            'code' => 'required|unique:books,code',
            'stock_total' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
            'cover' => 'nullable|image|max:2048'
        ]);

        $coverName = null;
        if ($request->hasFile('cover')) {
            $coverName = time() . '_' . $request->file('cover')->getClientOriginalName();
            $request->file('cover')->move(public_path('covers'), $coverName);
        }

        Book::create([
            'title' => $request->title,
            'author' => $request->author,
            'code' => $request->code,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'stock_total' => $request->stock_total,
            'stock_available' => $request->stock_total, // Stok awal sama dengan total
            'category_id' => $request->category_id,
            'cover' => $coverName,
        ]);

        return redirect()->route('petugas.kelolaBuku')->with('success', '✅ Buku berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $book = Book::findOrFail($id);
        $categories = Category::all();
        return view('petugas.kelola-buku.form', compact('book', 'categories'));
    }

    public function update(Request $request, $id)
    {
        $book = Book::findOrFail($id);

        $request->validate([
            'title' => 'required|max:255',
            'author' => 'required|max:100',
            'code' => 'required|unique:books,code,' . $id,
            'stock_total' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id',
        ]);

        $coverName = $book->cover;
        if ($request->hasFile('cover')) {
            if ($book->cover && file_exists(public_path('covers/' . $book->cover))) {
                unlink(public_path('covers/' . $book->cover));
            }
            $coverName = time() . '_' . $request->file('cover')->getClientOriginalName();
            $request->file('cover')->move(public_path('covers'), $coverName);
        }

        $book->update([
            'title' => $request->title,
            'author' => $request->author,
            'code' => $request->code,
            'publisher' => $request->publisher,
            'year' => $request->year,
            'stock_total' => $request->stock_total,
            'category_id' => $request->category_id,
            'cover' => $coverName,
        ]);

        return redirect()->route('petugas.kelolaBuku')->with('success', '✅ Buku berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $book = Book::findOrFail($id);
        if ($book->cover && file_exists(public_path('covers/' . $book->cover))) {
            unlink(public_path('covers/' . $book->cover));
        }
        $book->delete();
        return redirect()->route('petugas.kelolaBuku')->with('success', '✅ Buku berhasil dihapus!');
    }
}