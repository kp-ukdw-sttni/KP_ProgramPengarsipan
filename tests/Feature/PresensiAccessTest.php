<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\PresensiUkm;
use App\Models\Ukm;
use App\Models\UkmAnggota;
use App\Models\User;
use App\Support\PresensiAccess;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

/**
 * Attendance belongs to WK III Kemahasiswaan dan Alumni, so membership in that
 * unit — not a hand-assigned role — is what unlocks the feature.
 */
class PresensiAccessTest extends TestCase
{
    use RefreshDatabase;

    private Divisi $kms;

    private Divisi $kasubKem;

    private Divisi $kasubAlum;

    private Divisi $baak;

    private Ukm $ukm;

    protected function setUp(): void
    {
        parent::setUp();

        foreach (['Admin', 'Superadmin', 'Dosen', 'Sie Kesiswaan'] as $role) {
            Role::firstOrCreate(['name' => $role, 'guard_name' => 'web']);
        }

        // Arsip requires a category; the seeder normally supplies it.
        KategoriArsip::firstOrCreate(['kode' => 'PRESENSI'], ['name' => 'PRESENSI']);

        // The structure migration already builds this tree, so reuse it rather
        // than colliding with the unique kode.
        $this->kms = $this->divisi('KMS');
        $this->kasubKem = $this->divisi('KASUB-KEM');
        $this->kasubAlum = $this->divisi('KASUB-ALUM');
        $this->baak = $this->divisi('BAAK');

        $this->assertSame($this->kms->id, $this->kasubKem->parent_id, 'KASUB-KEM harus tetap anak dari KMS.');
        $this->assertSame($this->kms->id, $this->kasubAlum->parent_id, 'KASUB-ALUM harus tetap anak dari KMS.');

        $this->ukm = Ukm::create([
            'kode' => 'UKM-TEST',
            'name' => 'UKM Test',
            'status' => 'Aktif',
            'divisi_id' => $this->kms->id,
        ]);

        UkmAnggota::create([
            'ukm_id' => $this->ukm->id,
            'nama' => 'Anggota Satu',
            'nim' => '2311001',
            'jabatan' => 'Ketua',
            'status_keanggotaan' => 'Aktif',
        ]);
    }

    public function test_the_unit_head_and_both_sub_units_may_fill_in_attendance(): void
    {
        // Plain 'Dosen' role on purpose: membership of WK III is what counts.
        foreach ([$this->kms, $this->kasubKem, $this->kasubAlum] as $divisi) {
            $user = $this->userWithRole('Dosen', $divisi);

            $this->assertTrue(
                PresensiAccess::canAccess($user),
                "Akun pada divisi {$divisi->kode} seharusnya boleh mengisi presensi."
            );

            $this->actingAs($user)
                ->get(route('presensi.create'))
                ->assertOk();
        }
    }

    public function test_an_account_outside_wk_iii_cannot_fill_in_attendance(): void
    {
        $dosen = $this->userWithRole('Dosen', $this->baak);

        $this->assertFalse(PresensiAccess::canAccess($dosen));

        $this->actingAs($dosen)
            ->get(route('presensi.create'))
            ->assertForbidden();
    }

    public function test_sie_kesiswaan_posted_outside_the_unit_still_has_access(): void
    {
        $sie = $this->userWithRole('Sie Kesiswaan', $this->baak);

        $this->assertTrue(PresensiAccess::canAccess($sie));

        $this->actingAs($sie)
            ->get(route('presensi.create'))
            ->assertOk();
    }

    public function test_admin_and_superadmin_can_review_but_a_dosen_in_the_unit_cannot(): void
    {
        foreach (['Admin', 'Superadmin'] as $role) {
            $user = $this->userWithRole($role, $this->baak);

            $this->assertTrue(PresensiAccess::canAccess($user));
            $this->assertTrue(PresensiAccess::canReview($user));

            $this->actingAs($user)
                ->get(route('presensi.review'))
                ->assertOk();
        }

        $dosen = $this->userWithRole('Dosen', $this->kasubKem);

        // Inside the unit, so it may fill in, but verification stays with the
        // arsip managers.
        $this->assertTrue(PresensiAccess::canAccess($dosen));
        $this->assertFalse(PresensiAccess::canReview($dosen));

        $this->actingAs($dosen)
            ->get(route('presensi.review'))
            ->assertForbidden();
    }

    public function test_a_sub_unit_staff_sees_the_whole_unit_history_not_only_their_own_sessions(): void
    {
        $this->submitSession($this->userWithRole('Dosen', $this->kasubKem));

        $peer = $this->userWithRole('Dosen', $this->kms);

        $this->actingAs($peer)
            ->get(route('presensi.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Presensi/Index')
                ->has('presensi.data', 1)
            );
    }

    public function test_a_sie_kesiswaan_posted_elsewhere_only_sees_their_own_sessions(): void
    {
        $this->submitSession($this->userWithRole('Dosen', $this->kms));

        $outsider = $this->userWithRole('Sie Kesiswaan', $this->baak);

        $this->actingAs($outsider)
            ->get(route('presensi.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Presensi/Index')
                ->has('presensi.data', 0)
            );

        $this->assertSame(1, PresensiUkm::count());
    }

    public function test_an_admin_posted_outside_the_unit_still_sees_every_session(): void
    {
        $this->submitSession($this->userWithRole('Dosen', $this->kasubKem));

        $admin = $this->userWithRole('Admin', $this->baak);

        $this->actingAs($admin)
            ->get(route('presensi.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Presensi/Index')
                ->has('presensi.data', 1)
            );
    }

    public function test_the_shared_auth_props_expose_the_presensi_flags_and_divisi_as_an_object(): void
    {
        $staff = $this->userWithRole('Dosen', $this->kasubKem);

        $this->actingAs($staff)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.can_presensi', true)
                ->where('auth.user.can_review_presensi', false)
                ->where('auth.user.divisi.kode', 'KASUB-KEM')
                ->where('auth.user.divisi.name', 'Ka Sub Bid Kemahasiswaan')
            );

        $outsider = $this->userWithRole('Dosen', $this->baak);

        $this->actingAs($outsider)
            ->get(route('dashboard'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('auth.user.can_presensi', false)
            );
    }

    private function divisi(string $kode): Divisi
    {
        return Divisi::where('kode', $kode)->firstOrFail();
    }

    private function userWithRole(string $role, ?Divisi $divisi = null): User
    {
        $user = User::factory()->create(['divisi_id' => $divisi?->id]);
        $user->assignRole($role);

        return $user;
    }

    private function submitSession(User $user): void
    {
        $this->actingAs($user)
            ->post(route('presensi.store'), [
                'ukm_id' => $this->ukm->id,
                'judul_kegiatan' => 'Rapat Anggota',
                'tanggal_kegiatan' => '2026-09-20',
                'pertemuan_ke' => 1,
                'anggota' => [
                    [
                        'anggota_id' => $this->ukm->anggota()->firstOrFail()->id,
                        'status_kehadiran' => 'Hadir',
                    ],
                ],
            ])
            ->assertRedirect();
    }
}
