<?php

namespace Tests\Feature;

use App\Models\KategoriArsip;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class KategoriWizardTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Admin', 'guard_name' => 'web']);

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Admin');
    }

    public function test_index_no_longer_ships_the_parent_category_dropdown(): void
    {
        $this->actingAs($this->admin)
            ->get(route('kategori.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Kategori/Index')
                ->has('categories')
                ->missing('parentCategories')
            );
    }

    public function test_first_step_returns_the_new_category_id_as_json(): void
    {
        $this->actingAs($this->admin)
            ->postJson(route('kategori.store'), [
                'kode' => 'PERPUSTAKAAN',
                'name' => 'F. Dokumen Perpustakaan',
            ])
            ->assertCreated()
            ->assertJsonPath('kode', 'PERPUSTAKAAN')
            ->assertJsonPath('name', 'F. Dokumen Perpustakaan');

        $this->assertDatabaseHas('kategori_arsip', [
            'kode' => 'PERPUSTAKAAN',
            'name' => 'F. Dokumen Perpustakaan',
            'parent_id' => null,
        ]);
    }

    public function test_first_step_still_redirects_for_a_normal_form_post(): void
    {
        $this->actingAs($this->admin)
            ->post(route('kategori.store'), [
                'kode' => 'PERPUSTAKAAN',
                'name' => 'F. Dokumen Perpustakaan',
            ])
            ->assertRedirect(route('kategori.index'));
    }

    public function test_first_step_rejects_a_duplicate_name(): void
    {
        KategoriArsip::create(['kode' => 'ADM', 'name' => 'Administrasi']);

        $this->actingAs($this->admin)
            ->postJson(route('kategori.store'), [
                'kode' => 'ADM2',
                'name' => 'Administrasi',
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('name');
    }

    public function test_second_step_creates_every_sub_category_with_a_generated_kode(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->post(route('kategori.children.store', $parent), [
                'children' => ['Surat Masuk', 'Surat Keluar', 'Buku Peminjaman'],
            ])
            ->assertRedirect(route('kategori.index'));

        $this->assertDatabaseHas('kategori_arsip', [
            'parent_id' => $parent->id,
            'kode' => 'SURAT-MASUK',
            'name' => 'Surat Masuk',
        ]);
        $this->assertDatabaseHas('kategori_arsip', [
            'parent_id' => $parent->id,
            'kode' => 'SURAT-KELUAR',
            'name' => 'Surat Keluar',
        ]);
        $this->assertDatabaseHas('kategori_arsip', [
            'parent_id' => $parent->id,
            'kode' => 'BUKU-PEMINJAMAN',
            'name' => 'Buku Peminjaman',
        ]);
    }

    public function test_generated_kode_never_exceeds_the_column_length(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PANJANG', 'name' => 'Kategori Nama Panjang']);
        $longName = trim(str_repeat('Kata Panjang ', 12));

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->post(route('kategori.children.store', $parent), ['children' => [$longName]])
            ->assertRedirect(route('kategori.index'));

        $child = KategoriArsip::where('name', $longName)->firstOrFail();

        $this->assertSame(50, strlen($child->kode));
    }

    public function test_generated_kode_is_unique_among_sub_categories(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PANJANG', 'name' => 'Kategori Nama Panjang']);
        KategoriArsip::create([
            'kode' => 'SURAT-MASUK',
            'name' => 'Surat Masuk',
            'parent_id' => $parent->id,
        ]);
        KategoriArsip::create([
            'kode' => 'SURAT-MASUK-2',
            'name' => 'Surat Masuk Juni',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($this->admin)
            ->from(route('kategori.index'))
            ->post(route('kategori.children.store', $parent), ['children' => ['Surat Masuk Juli']])
            ->assertRedirect(route('kategori.index'));

        $child = KategoriArsip::where('name', 'Surat Masuk Juli')->firstOrFail();

        $this->assertSame('SURAT-MASUK-JULI', $child->kode);
    }

    public function test_sub_category_names_are_rejected_case_insensitively(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);

        $this->actingAs($this->admin)
            ->postJson(route('kategori.children.store', $parent), [
                'children' => ['Surat Masuk', 'surat masuk'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('children.1');

        $this->assertSame(0, KategoriArsip::where('parent_id', $parent->id)->count());
    }

    public function test_sub_categories_roll_back_entirely_when_one_name_is_taken(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);
        KategoriArsip::create(['kode' => 'BEASISWA', 'name' => 'Beasiswa', 'parent_id' => $parent->id]);

        $this->actingAs($this->admin)
            ->postJson(route('kategori.children.store', $parent), [
                'children' => ['Surat Masuk', 'Beasiswa'],
            ])
            ->assertStatus(422)
            ->assertJsonValidationErrors('children.1');

        $this->assertSame(1, KategoriArsip::where('parent_id', $parent->id)->count());
    }

    public function test_empty_sub_category_list_is_rejected(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);

        $this->actingAs($this->admin)
            ->postJson(route('kategori.children.store', $parent), ['children' => []])
            ->assertStatus(422)
            ->assertJsonValidationErrors('children');
    }

    public function test_update_rejects_a_name_belonging_to_another_category(): void
    {
        $a = KategoriArsip::create(['kode' => 'ADM', 'name' => 'Administrasi']);
        $b = KategoriArsip::create(['kode' => 'AKD', 'name' => 'Akademik']);

        $this->actingAs($this->admin)
            ->from(route('kategori.edit', $b))
            ->put(route('kategori.update', $b), [
                'kode' => 'AKD',
                'name' => 'Administrasi',
            ])
            ->assertSessionHasErrors('name');

        $this->assertDatabaseHas('kategori_arsip', ['id' => $b->id, 'name' => 'Akademik']);
    }

    public function test_edit_page_ships_parent_and_children_instead_of_the_parent_dropdown(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);
        $child = KategoriArsip::create([
            'kode' => 'SURAT-MASUK',
            'name' => 'Surat Masuk',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('kategori.edit', $child))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Kategori/Edit')
                ->has('kategori')
                ->has('parent')
                ->has('children')
                ->missing('parentCategories')
                ->where('parent.name', 'F. Dokumen Perpustakaan')
            );
    }

    public function test_edit_page_of_a_parent_lists_its_children(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);
        KategoriArsip::create([
            'kode' => 'SURAT-MASUK',
            'name' => 'Surat Masuk',
            'parent_id' => $parent->id,
        ]);

        $this->actingAs($this->admin)
            ->get(route('kategori.edit', $parent))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('parent', null)
                ->has('children', 1)
                ->where('children.0.name', 'Surat Masuk')
            );
    }

    public function test_update_ignores_a_submitted_parent_id_so_the_hierarchy_stays_fixed(): void
    {
        $oldParent = KategoriArsip::create(['kode' => 'LAMA', 'name' => 'Kategori Lama']);
        $newParent = KategoriArsip::create(['kode' => 'BARU', 'name' => 'Kategori Baru']);
        $child = KategoriArsip::create([
            'kode' => 'SURAT-MASUK',
            'name' => 'Surat Masuk',
            'parent_id' => $oldParent->id,
        ]);

        $this->actingAs($this->admin)
            ->put(route('kategori.update', $child), [
                'kode' => 'SURAT-MASUK',
                'name' => 'Surat Masuk Baru',
                'deskripsi' => null,
                'parent_id' => $newParent->id,
            ])
            ->assertRedirect(route('kategori.index'));

        $child->refresh();

        $this->assertSame('Surat Masuk Baru', $child->name);
        $this->assertSame($oldParent->id, $child->parent_id);
    }

    public function test_sub_categories_can_be_added_from_the_edit_page(): void
    {
        $parent = KategoriArsip::create(['kode' => 'PERPUSTAKAAN', 'name' => 'F. Dokumen Perpustakaan']);

        $this->actingAs($this->admin)
            ->from(route('kategori.edit', $parent))
            ->post(route('kategori.children.store', $parent), ['children' => ['Surat Dinas']])
            ->assertRedirect(route('kategori.edit', $parent));

        $this->assertDatabaseHas('kategori_arsip', [
            'parent_id' => $parent->id,
            'kode' => 'SURAT-DINAS',
            'name' => 'Surat Dinas',
        ]);
    }

    public function test_non_admin_cannot_use_the_wizard(): void
    {
        Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $dosen = User::factory()->create();
        $dosen->assignRole('Dosen');

        $this->actingAs($dosen)
            ->postJson(route('kategori.store'), ['kode' => 'X', 'name' => 'X'])
            ->assertForbidden();
    }
}
