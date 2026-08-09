<?php

namespace App\Http\Controllers;

use App\Models\KategoriArsip;
use Illuminate\Http\Request;

class KategoriController extends Controller
{
    /**
     * Display a listing of categories and sub-categories.
     */
    public function index()
    {
        // Load parent categories with their child sub-categories
        $categories = KategoriArsip::whereNull('parent_id')
            ->with(['children'])
            ->withCount('arsip')
            ->orderBy('name')
            ->paginate(15);

        // Fetch all categories for parent selection dropdown in forms
        $parentCategories = KategoriArsip::whereNull('parent_id')->orderBy('name')->get();

        return view('kategori.index', compact('categories', 'parentCategories'));
    }

    /**
     * Store a newly created category or sub-category.
     */
    public function store(Request $request)
    {
        $request->validate([
            'kode' => ['required', 'string', 'unique:kategori_arsip,kode', 'max:50'],
            'name' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:kategori_arsip,id'],
        ]);

        KategoriArsip::create([
            'kode' => $request->kode,
            'name' => $request->name,
            'deskripsi' => $request->deskripsi,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori baru berhasil dibuat.');
    }

    /**
     * Show the edit form for a category.
     */
    public function edit(KategoriArsip $kategori)
    {
        $parentCategories = KategoriArsip::whereNull('parent_id')
            ->where('id', '!=', $kategori->id) // Prevent self-referencing as parent
            ->orderBy('name')
            ->get();

        return view('kategori.edit', compact('kategori', 'parentCategories'));
    }

    /**
     * Update the category details.
     */
    public function update(Request $request, KategoriArsip $kategori)
    {
        $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:kategori_arsip,kode,' . $kategori->id],
            'name' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:kategori_arsip,id', 'different:id'],
        ]);

        $kategori->update([
            'kode' => $request->kode,
            'name' => $request->name,
            'deskripsi' => $request->deskripsi,
            'parent_id' => $request->parent_id,
        ]);

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Delete a category.
     */
    public function destroy(KategoriArsip $kategori)
    {
        // Deletion validation: Ensure no documents are linked
        if ($kategori->arsip()->exists()) {
            return redirect()->route('kategori.index')->with('error', 'Kategori ini tidak dapat dihapus karena memiliki dokumen terkait.');
        }

        // Deletion validation: Ensure no sub-categories are linked
        if ($kategori->children()->exists()) {
            return redirect()->route('kategori.index')->with('error', 'Kategori ini tidak dapat dihapus karena memiliki sub-kategori terkait.');
        }

        $kategori->delete();

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil dihapus.');
    }
}
