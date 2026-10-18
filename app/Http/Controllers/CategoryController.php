<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Category;

class CategoryController extends Controller
{
    public function index()
    {
        // Mengambil semua kategori beserta jumlah bukunya
        $categories = Category::withCount('books')->get();
        return view('petugas.kategori.index', compact('categories'));
    }

    public function create()
    {
        return view('petugas.kategori.form');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|unique:categories,name|max:100'
        ], [
            'name.required' => 'Nama kategori wajib diisi.',
            'name.unique' => 'Kategori ini sudah ada.',
        ]);

        Category::create(['name' => $request->name]);
        return redirect()->route('petugas.kategori.index')->with('success', '✅ Kategori berhasil ditambahkan!');
    }

    public function edit($id)
    {
        $category = Category::findOrFail($id);
        return view('petugas.kategori.form', compact('category'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|unique:categories,name,' . $id . '|max:100'
        ]);

        $category = Category::findOrFail($id);
        $category->update(['name' => $request->name]);
        return redirect()->route('petugas.kategori.index')->with('success', '✅ Kategori berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $category = Category::findOrFail($id);
        
        // Cegah penghapusan jika kategori masih dipakai oleh buku
        if ($category->books()->count() > 0) {
            return back()->with('error', '❌ Tidak bisa hapus! Kategori ini masih memiliki buku.');
        }
        
        $category->delete();
        return redirect()->route('petugas.kategori.index')->with('success', '✅ Kategori berhasil dihapus!');
    }
}