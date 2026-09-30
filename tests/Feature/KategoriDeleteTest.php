<?php

namespace Tests\Feature;

use App\Models\Arsip;
use App\Models\KategoriArsip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KategoriDeleteTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private int $arsipSequence = 0;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    private function parentWithChildren(int $childCount = 2): KategoriArsip
    {
        $parent = KategoriArsip::create(['kode' => 'SUR', 'name' => 'Surat']);

        for ($i = 1; $i <= $childCount; $i++) {
            KategoriArsip::create([
                'kode' => "SUR-{$i}",
                'name' => "Sub {$i}",
                'parent_id' => $parent->id,
            ]);
        }

        return $parent;
    }

    private function arsipFor(KategoriArsip $kategori): void
    {
        $this->arsipSequence++;

        Arsip::create([
            'nomor_arsip' => 'ARSIP-'.$this->arsipSequence,
            'judul' => 'Dokumen untuk '.$kategori->name,
            'kategori_id' => $kategori->id,
            'file_path' => 'arsip/test.pdf',
            'retention_date' => now()->addYear()->toDateString(),
        ]);
    }

    public function test_sub_category_can_be_deleted_on_its_own(): void
    {
        $parent = $this->parentWithChildren();
        $child = $parent->children()->first();

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->delete(route('kategori.destroy', $child))
            ->assertRedirect(route('kategori.index'))
            ->assertSessionHasNoErrors();

        $this->assertDatabaseMissing('kategori_arsip', ['id' => $child->id]);
        $this->assertDatabaseHas('kategori_arsip', ['id' => $parent->id]);
    }

    public function test_parent_with_sub_categories_is_kept_without_cascade(): void
    {
        $parent = $this->parentWithChildren();

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->delete(route('kategori.destroy', $parent))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_arsip', ['id' => $parent->id]);
        $this->assertSame(
            2,
            KategoriArsip::where('parent_id', $parent->id)->count(),
            'Sub-kategori harus tetap ada saat cascade tidak diminta.'
        );
    }

    public function test_parent_with_sub_categories_is_removed_when_cascade_is_confirmed(): void
    {
        $parent = $this->parentWithChildren(3);

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->delete(route('kategori.destroy', $parent), ['cascade' => true])
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('kategori_arsip', ['id' => $parent->id]);
        $this->assertSame(0, KategoriArsip::where('parent_id', $parent->id)->count());
    }

    public function test_category_holding_archives_is_never_deleted(): void
    {
        $parent = KategoriArsip::create(['kode' => 'KEP', 'name' => 'Keputusan']);
        $this->arsipFor($parent);

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->delete(route('kategori.destroy', $parent), ['cascade' => true])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_arsip', ['id' => $parent->id]);
        $this->assertDatabaseHas('arsip', ['kategori_id' => $parent->id]);
    }

    public function test_cascade_is_refused_when_a_sub_category_holds_archives(): void
    {
        $parent = $this->parentWithChildren();
        $archivedChild = $parent->children()->first();
        $this->arsipFor($archivedChild);

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->delete(route('kategori.destroy', $parent), ['cascade' => true])
            ->assertSessionHas('error');

        $this->assertDatabaseHas('kategori_arsip', ['id' => $parent->id]);
        $this->assertDatabaseHas('kategori_arsip', ['id' => $archivedChild->id]);
        $this->assertDatabaseHas('arsip', ['kategori_id' => $archivedChild->id]);
    }

    public function test_delete_returns_to_the_edit_page_when_a_child_is_removed_from_it(): void
    {
        $parent = $this->parentWithChildren();
        $child = $parent->children()->first();

        $this->actingAs($this->admin)
            ->from(route('kategori.edit', $parent))
            ->delete(route('kategori.destroy', $child))
            ->assertRedirect(route('kategori.edit', $parent));

        $this->assertDatabaseMissing('kategori_arsip', ['id' => $child->id]);
    }

    public function test_edit_page_reports_archive_counts_for_every_sub_category(): void
    {
        $parent = $this->parentWithChildren();
        $this->arsipFor($parent->children()->first());

        $this->actingAs($this->admin)
            ->get(route('kategori.edit', $parent))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Kategori/Edit')
                ->has('children', 2)
                ->where('children.0.arsip_count', 1)
            );
    }

    public function test_non_admin_cannot_delete_categories(): void
    {
        Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);

        $dosen = User::factory()->create();
        $dosen->assignRole('Dosen');

        $category = KategoriArsip::create(['kode' => 'UND', 'name' => 'Undangan']);

        $this->actingAs($dosen)
            ->delete(route('kategori.destroy', $category))
            ->assertForbidden();

        $this->assertDatabaseHas('kategori_arsip', ['id' => $category->id]);
    }
}
