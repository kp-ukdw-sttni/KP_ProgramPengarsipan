<?php

namespace Tests\Feature;

use App\Models\Arsip;
use App\Models\KategoriArsip;
use App\Models\Peminjaman;
use App\Models\User;
use App\Support\AksesDokumen;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * A Confidential archive is locked to its uploader and the arsip managers.
 * Anyone else must ask, and an Admin or Superadmin decides. Once approved the
 * grant is permanent; rejecting keeps the document closed and records a reason.
 */
class AksesDokumenTest extends TestCase
{
    use RefreshDatabase;

    private User $pengunggah;

    private User $pemohon;

    private Arsip $rahasia;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Superadmin', 'Dosen'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        $kategori = KategoriArsip::firstOrCreate(['kode' => 'SKRIPSI'], ['name' => 'SKRIPSI']);

        $this->pengunggah = $this->userWithRole('Dosen');
        $this->pemohon = $this->userWithRole('Dosen');

        $this->rahasia = Arsip::create([
            'nomor_arsip' => 'ARS-TEST-2026-001',
            'judul' => 'Dokumen RahasiaTes',
            'kategori_id' => $kategori->id,
            'tahun' => 2026,
            'status' => 'Aktif',
            'status_publikasi' => 'Confidential',
            'file_path' => 'private/arsip/contoh.pdf',
            'retention_date' => '2031-09-30',
            'uploader_id' => $this->pengunggah->id,
        ]);
    }

    public function test_a_confidential_document_is_locked_for_other_staff(): void
    {
        $this->assertFalse(AksesDokumen::canAccess($this->rahasia, $this->pemohon));

        $this->actingAs($this->pemohon)
            ->get(route('arsip.download', $this->rahasia->id))
            ->assertForbidden();
    }

    public function test_an_internal_document_needs_no_request(): void
    {
        $internal = Arsip::create([
            'nomor_arsip' => 'ARS-TEST-2026-002',
            'judul' => 'Dokumen Internal',
            'kategori_id' => $this->rahasia->kategori_id,
            'tahun' => 2026,
            'status' => 'Aktif',
            'status_publikasi' => 'Internal',
            'file_path' => 'private/arsip/internal.pdf',
            'retention_date' => '2031-09-30',
            'uploader_id' => $this->pengunggah->id,
        ]);

        $this->assertTrue(AksesDokumen::canAccess($internal, $this->pemohon));

        // Asking for something already visible is refused rather than silently
        // creating a pointless queue entry.
        $this->actingAs($this->pemohon)
            ->from(route('arsip.index'))
            ->post(route('peminjaman.request', $internal->id))
            ->assertRedirect(route('arsip.index'))
            ->assertSessionHas('error');
    }

    public function test_a_staff_member_can_request_and_the_admin_can_approve(): void
    {
        $this->actingAs($this->pemohon)
            ->from(route('arsip.index'))
            ->post(route('peminjaman.request', $this->rahasia->id))
            ->assertRedirect(route('arsip.index'))
            ->assertSessionHas('success');

        $request = Peminjaman::firstOrFail();
        $this->assertSame('Pending', $request->status_approval);
        $this->assertFalse(AksesDokumen::canAccess($this->rahasia, $this->pemohon));

        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)
            ->post(route('peminjaman.approve', $request->id), ['notes' => 'Untuk keperluan rapat.'])
            ->assertSessionHas('success');

        $request->refresh();

        $this->assertSame('Approved', $request->status_approval);
        $this->assertNotNull($request->reviewed_at);
        $this->assertNull($request->expired_at, 'Persetujuan baru harus permanen, tanpa kedaluwarsa.');
        $this->assertTrue(AksesDokumen::canAccess($this->rahasia, $this->pemohon));
    }

    public function test_superadmin_may_also_decide(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $request = Peminjaman::firstOrFail();

        $this->actingAs($this->userWithRole('Superadmin'))
            ->get(route('peminjaman.manage'))
            ->assertOk();

        $this->actingAs($this->userWithRole('Superadmin'))
            ->post(route('peminjaman.approve', $request->id))
            ->assertSessionHas('success');
    }

    public function test_a_dosen_cannot_reach_the_decision_queue(): void
    {
        $this->actingAs($this->pemohon)
            ->get(route('peminjaman.manage'))
            ->assertForbidden();
    }

    public function test_rejecting_keeps_the_document_closed_and_records_the_reason(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $request = Peminjaman::firstOrFail();

        $this->actingAs($this->userWithRole('Admin'))
            ->post(route('peminjaman.reject', $request->id))
            ->assertSessionHasErrors('notes');

        $this->actingAs($this->userWithRole('Admin'))
            ->post(route('peminjaman.reject', $request->id), ['notes' => 'Memuat data pribadi.'])
            ->assertSessionHas('success');

        $request->refresh();

        $this->assertSame('Rejected', $request->status_approval);
        $this->assertSame('Memuat data pribadi.', $request->notes);
        $this->assertFalse(AksesDokumen::canAccess($this->rahasia, $this->pemohon));
    }

    public function test_a_rejected_request_can_be_resubmitted_without_duplicating_the_row(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $this->actingAs($this->userWithRole('Admin'))
            ->post(route('peminjaman.reject', Peminjaman::firstOrFail()->id), ['notes' => 'Belum fibroblast.']);

        $this->actingAs($this->pemohon)
            ->from(route('arsip.index'))
            ->post(route('peminjaman.request', $this->rahasia->id))
            ->assertRedirect(route('arsip.index'))
            ->assertSessionHas('success');

        $this->assertSame(1, Peminjaman::count(), 'Pengajuan ulang harus memakai baris yang sama.');
        $this->assertSame('Pending', Peminjaman::firstOrFail()->status_approval);
        $this->assertNull(Peminjaman::firstOrFail()->notes);
    }

    public function test_a_duplicate_pending_request_is_refused(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $this->actingAs($this->pemohon)
            ->from(route('arsip.index'))
            ->post(route('peminjaman.request', $this->rahasia->id))
            ->assertRedirect(route('arsip.index'))
            ->assertSessionHas('error');

        $this->assertSame(1, Peminjaman::count());
    }

    public function test_an_admin_can_revoke_a_grant_that_was_already_approved(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $admin = $this->userWithRole('Admin');

        $this->actingAs($admin)->post(route('peminjaman.approve', Peminjaman::firstOrFail()->id));
        $this->assertTrue(AksesDokumen::canAccess($this->rahasia, $this->pemohon));

        $this->actingAs($admin)
            ->post(route('peminjaman.revoke', Peminjaman::firstOrFail()->id), ['notes' => 'Peristiwa sudah lewat.'])
            ->assertSessionHas('success');

        $this->assertFalse(AksesDokumen::canAccess($this->rahasia, $this->pemohon));

        // The row is kept for audit rather than deleted.
        $this->assertSame('Rejected', Peminjaman::firstOrFail()->status_approval);
    }

    public function test_a_decided_request_cannot_be_approved_again(): void
    {
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $request = Peminjaman::firstOrFail();

        $this->actingAs($this->userWithRole('Admin'))
            ->post(route('peminjaman.reject', $request->id), ['notes' => 'Tidak Relevan.']);

        $this->actingAs($this->userWithRole('Admin'))
            ->post(route('peminjaman.approve', $request->id))
            ->assertStatus(422);
    }

    public function test_the_arsip_list_flags_a_locked_row_and_the_request_state(): void
    {
        $response = $this->actingAs($this->pemohon)
            ->get(route('arsip.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Arsip/Index')
                ->where('arsip.data.0.bisa_diakses', false)
                ->where('arsip.data.0.butuh_persetujuan', true)
                ->where('arsip.data.0.status_permintaan', null)
            );

        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $this->actingAs($this->pemohon)
            ->get(route('arsip.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('arsip.data.0.bisa_diakses', false)
                ->where('arsip.data.0.status_permintaan', 'Pending')
            );

        $this->actingAs($this->userWithRole('Admin'))
            ->post(route('peminjaman.approve', Peminjaman::firstOrFail()->id));

        $this->actingAs($this->pemohon)
            ->get(route('arsip.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('arsip.data.0.bisa_diakses', true)
                ->where('arsip.data.0.status_permintaan', 'Approved')
            );
    }

    public function test_the_uploader_always_keeps_access_without_asking(): void
    {
        $this->assertTrue(AksesDokumen::canAccess($this->rahasia, $this->pengunggah));

        $this->actingAs($this->pengunggah)
            ->from(route('arsip.index'))
            ->post(route('peminjaman.request', $this->rahasia->id))
            ->assertSessionHas('error');
    }

    public function test_the_requester_has_no_page_of_their_own_requests(): void
    {
        // The self-service listing was dropped: the outcome is shown inline on
        // the locked document and the Admin works from the Persetujuan Akses
        // queue, so neither route may exist any more.
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $this->actingAs($this->pemohon)
            ->get('/akses-dokumen')
            ->assertNotFound();

        $this->assertTrue(
            collect(app('router')->getRoutes()->getRoutes())->every(
                fn ($route) => ! str_contains($route->uri(), 'akses-dokumen/{peminjaman}/cancel')
            ),
            'Endpoint pembatalan oleh pemohon sudah dihapus.',
        );
    }

    public function test_the_request_stays_pending_until_an_admin_decides(): void
    {
        // Without a cancel action the requester waits for the queue, so nothing
        // may decide or drop the row on their behalf.
        $this->actingAs($this->pemohon)
            ->post(route('peminjaman.request', $this->rahasia->id));

        $this->actingAs($this->pemohon)
            ->get(route('arsip.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page->where('arsip.data.0.status_permintaan', 'Pending'));

        $this->assertSame('Pending', Peminjaman::firstOrFail()->status_approval);
        $this->assertSame(1, Peminjaman::count());
    }

    public function test_a_legacy_time_boxed_grant_still_opens_the_document(): void
    {
        // The old flow let an admin pick a duration. The migration clears
        // `expired_at` for those rows, so an approved document never silently
        // locks itself again because a date went by.
        Peminjaman::create([
            'arsip_id' => $this->rahasia->id,
            'user_id' => $this->pemohon->id,
            'approved_by' => $this->pengunggah->id,
            'status_approval' => 'Approved',
            'expired_at' => now()->subYear(),
        ]);

        $request = Peminjaman::firstOrFail();
        $this->assertTrue($request->isActive(), 'Persetujuan harus permanen, termasuk sisa data lama.');
        $this->assertTrue(AksesDokumen::canAccess($this->rahasia, $this->pemohon));
    }

    public function test_the_shared_auth_props_expose_the_approval_capability(): void
    {
        $this->actingAs($this->userWithRole('Admin'))
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->where('auth.user.can_approve_access', true));

        $this->actingAs($this->pemohon)
            ->get(route('dashboard'))
            ->assertInertia(fn ($page) => $page->where('auth.user.can_approve_access', false));
    }

    private function userWithRole(string $role): User
    {
        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
