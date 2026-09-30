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
        $validated = $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:kategori_arsip,kode'],
            'name' => ['required', 'string', 'max:255', 'unique:kategori_arsip,name'],
            'deskripsi' => ['nullable', 'string'],
            'parent_id' => ['nullable', 'exists:kategori_arsip,id'],
        ]);

        $kategori = $this->kategoriService->create($validated);

        // The kategori wizard needs the new id to attach sub-categories in its
        // second step without leaving the page, so step one asks for JSON.
        if ($request->expectsJson()) {
            return response()->json([
                'id' => $kategori->id,
                'kode' => $kategori->kode,
                'name' => $kategori->name,
                'message' => 'Kategori "'.$kategori->name.'" berhasil dibuat.',
            ], 201);
        }

        return redirect()->route('kategori.index')
            ->with('success', 'Kategori "'.$kategori->name.'" berhasil dibuat.');
    }

    /**
     * Store several sub-categories under one parent category at once.
     */
    public function storeChildren(Request $request, KategoriArsip $kategori)
    {
        $validated = $request->validate([
            'children' => ['required', 'array', 'min:1'],
            // distinct:ignore_case mirrors the case-insensitive collation of the
            // unique index, so 'Surat Dinas' plus 'surat dinas' is rejected
            // during validation instead of blowing up on insert.
            'children.*' => ['required', 'string', 'max:255', 'distinct:ignore_case', 'unique:kategori_arsip,name'],
        ]);

        $created = $this->kategoriService->createChildren($kategori, $validated['children']);

        // Both the wizard and the edit page post here, so return to whichever one
        // the request came from instead of always bouncing to the index.
        return back()->with('success', $created.' sub-kategori ditambahkan ke "'.$kategori->name.'".');
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
     *
     * parent_id is intentionally absent: the wizard fixes the hierarchy when the
     * category is created, so it cannot be reassigned here.
     */
    public function update(Request $request, KategoriArsip $kategori)
    {
        $request->validate([
            'kode' => ['required', 'string', 'max:50', 'unique:kategori_arsip,kode,'.$kategori->id],
            'name' => ['required', 'string', 'max:255', 'unique:kategori_arsip,name,'.$kategori->id],
            'deskripsi' => ['nullable', 'string'],
        ]);

        $this->kategoriService->update($kategori, $request->all());

        return redirect()->route('kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

/**
 * Delete a category.
 *
 * cascade is what the UI sends after the user confirms that the
 * sub-categories go away together with their parent.
 */
public function destroy(Request $request, KategoriArsip $kategori)
{
    $validated = $request->validate([
        'cascade' => ['sometimes', 'boolean'],
    ]);

    $result = $this->kategoriService->delete($kategori, (bool) ($validated['cascade'] ?? false));

    // Sub-categories are deleted from the edit page and parents from the
    // listing, so return to whichever screen the request came from.
    return back()->with($result['success'] ? 'success' : 'error', $result['message']);
}
}
