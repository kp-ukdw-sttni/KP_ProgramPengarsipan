<?php

namespace App\Http\Controllers;

use App\Models\KategoriArsip;
use App\Services\KategoriService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class KategoriController extends Controller
{
    public function __construct(private KategoriService $kategoriService) {}

    /**
     * Display a listing of categories and sub-categories.
     */
    public function index()
    {
        return Inertia::render('Kategori/Index', $this->kategoriService->getIndexData());
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

        $this->kategoriService->create($request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori baru berhasil dibuat.');
    }

    /**
     * Show the edit form for a category.
     */
    public function edit(KategoriArsip $kategori)
    {
        return Inertia::render('Kategori/Edit', $this->kategoriService->getEditData($kategori));
    }

    /**
     * Update the category details.
     */
    public function update(Request $request, KategoriArsip $kategori)
    {
        $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:kategori_arsip,kode,'.$kategori->id],
            'name' => ['required', 'string', 'max:255'],
            'deskripsi' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:kategori_arsip,id', 'different:id'],
        ]);

        $this->kategoriService->update($kategori, $request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    /**
     * Delete a category.
     */
    public function destroy(KategoriArsip $kategori)
    {
        $result = $this->kategoriService->delete($kategori);

        return redirect()->route('kategori.index')
            ->with($result['success'] ? 'success' : 'error', $result['message']);
    }
}
