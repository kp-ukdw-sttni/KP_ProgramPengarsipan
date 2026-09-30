<?php

namespace App\Services;

use App\Models\KategoriArsip;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

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

        return [
            'categories' => $categories,
        ];
    }

    /**
     * Create a new category or sub-category.
     */
    public function create(array $data): KategoriArsip
    {
        return KategoriArsip::create([
            'kode' => $data['kode'],
            'name' => $data['name'],
            'deskripsi' => $data['deskripsi'] ?? null,
            'parent_id' => $data['parent_id'] ?? null,
        ]);
    }

    /**
     * Create several sub-categories under one parent in a single transaction.
     *
     * The wizard only asks for a name per sub-category, so the kode is derived
     * from it. Existing kodes (AKRED, KERJASAMA, ...) are short and stand on
     * their own, so the generated value is uppercased and hyphenated rather
     * than prefixed with the parent kode.
     *
     * @param  array<int, string>  $names
     * @return int  how many sub-categories were actually created
     */
    public function createChildren(KategoriArsip $parent, array $names): int
    {
        return DB::transaction(function () use ($parent, $names) {
            $created = 0;

            foreach ($names as $name) {
                $name = trim((string) $name);

                if ($name === '') {
                    continue;
                }

                KategoriArsip::create([
                    'kode' => $this->uniqueKode($name),
                    'name' => $name,
                    'parent_id' => $parent->id,
                ]);

                $created++;
            }

            return $created;
        });
    }

    /**
     * Derive a kode from a sub-category name that no other category uses yet.
     */
    private function uniqueKode(string $name): string
    {
        $base = trim(Str::upper((string) preg_replace('/[^A-Za-z0-9]+/', '-', $name)), '-');

        // A name made only of punctuation leaves nothing to slugify.
        if ($base === '') {
            $base = 'SUB';
        }

        $base = Str::limit($base, 50, '');
        $kode = $base;
        $suffix = 1;

        while (KategoriArsip::where('kode', $kode)->exists()) {
            $suffix++;
            $kode = Str::limit($base, 50 - strlen((string) $suffix) - 1, '').'-'.$suffix;
        }

        return $kode;
    }

    /**
     * Build the edit form data.
     *
     * The parent is no longer selectable: the wizard decides the hierarchy when
     * the category is created, so editing must not reshuffle it. We only read it
     * back so the page can show which category a sub-category belongs to.
     */
    public function getEditData(KategoriArsip $kategori): array
    {
        return [
            'kategori' => $kategori->loadCount('arsip'),
            'parent' => $kategori->parent,
            'children' => $kategori->children()
                ->orderBy('name')
                ->withCount('arsip')
                ->get(['id', 'kode', 'name']),
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
        ]);
    }

    /**
     * Delete a category, validating that no documents or sub-categories are linked.
     *
     * Deleting a category that still owns sub-categories is refused unless the
     * caller explicitly asks for a cascade, because it silently drops rows the
     * user may not have seen. Archives are never cascaded: a sub-category can
     * hold documents even when its parent has none.
     *
     * @return array{success: bool, message: string}
     */
    public function delete(KategoriArsip $kategori, bool $cascade = false): array
    {
        if ($kategori->arsip()->exists()) {
            return [
                'success' => false,
                'message' => 'Kategori ini tidak dapat dihapus karena memiliki dokumen terkait.',
            ];
        }

        if ($kategori->children()->whereHas('arsip')->exists()) {
            return [
                'success' => false,
                'message' => 'Sub-kategori dari kategori ini memiliki dokumen terkait. Pindahkan atau hapus dokumennya terlebih dahulu.',
            ];
        }

        $childCount = $kategori->children()->count();

        if ($childCount > 0 && ! $cascade) {
            return [
                'success' => false,
                'message' => 'Kategori ini memiliki '.$childCount.' sub-kategori. Hapus sub-kategori satu per satu, atau hapus semuanya sekaligus.',
            ];
        }

        DB::transaction(function () use ($kategori, $childCount) {
            $kategori->children()->delete();
            $kategori->delete();
        });

        return [
            'success' => true,
            'message' => $childCount > 0
                ? 'Kategori beserta '.$childCount.' sub-kategori berhasil dihapus.'
                : 'Kategori berhasil dihapus.',
        ];
    }
}
