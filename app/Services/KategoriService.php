<?php

namespace App\Services;

use App\Models\KategoriArsip;

class KategoriService
{
    /**
     * Build the categories listing with their child sub-categories.
     */
    public function getIndexData(): array
    {
        $categories = KategoriArsip::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->withCount('arsip')])
            ->withCount('arsip')
            ->orderBy('name')
            ->paginate(15)
            ->withQueryString();

        $parentCategories = KategoriArsip::whereNull('parent_id')->orderBy('name')->get();

        return [
            'categories' => $categories,
            'parentCategories' => $parentCategories,
        ];
    }

    /**
     * Create a new category or sub-category.
     */
    public function create(array $data): void
    {
        KategoriArsip::create([
            'kode' => $data['kode'],
            'name' => $data['name'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    /**
     * Build the edit form data.
     */
    public function getEditData(KategoriArsip $kategori): array
    {
        $parentCategories = KategoriArsip::whereNull('parent_id')
            ->where('id', '!=', $kategori->id) // Prevent self-referencing as parent
            ->orderBy('name')
            ->get();

        return [
            'kategori' => $kategori,
            'parentCategories' => $parentCategories,
        ];
    }

    /**
     * Update the category details.
     */
    public function update(KategoriArsip $kategori, array $data): void
    {
        $kategori->update([
            'kode' => $data['kode'],
            'name' => $data['name'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    /**
     * Delete a category, validating that no documents or sub-categories are linked.
     *
     * @return array{success: bool, message: string}
     */
    public function delete(KategoriArsip $kategori): array
    {
        if ($kategori->arsip()->exists()) {
            return [
                'success' => false,
                'message' => 'Kategori ini tidak dapat dihapus karena memiliki dokumen terkait.',
            ];
        }

        if ($kategori->children()->exists()) {
            return [
                'success' => false,
                'message' => 'Kategori ini tidak dapat dihapus karena memiliki sub-kategori terkait.',
            ];
        }

        $kategori->delete();

        return [
            'success' => true,
            'message' => 'Kategori berhasil dihapus.',
        ];
    }
}
