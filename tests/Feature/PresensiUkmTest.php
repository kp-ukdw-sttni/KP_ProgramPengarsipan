<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\PresensiUkm;
use App\Models\Ukm;
use App\Models\UkmAnggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PresensiUkmTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $sieKesiswaan;

    private Ukm $ukm;

    private KategoriArsip $kategori;

    private Divisi $divisi;

    protected function setUp(): void
    {
        parent::setUp();

        Role::firstOrCreate(['name' => 'Superadmin', 'guard_name' => 'web']);
        Role::firstOrCreate(['name' => 'Sie Kesiswaan', 'guard_name' => 'web']);

        $this->kategori = KategoriArsip::firstOrCreate(
            ['kode' => 'PRESENSI'],
            ['name' => 'PRESENSI']
        );

        // A data migration already creates the BAAK unit, so reuse it instead of
        // colliding with the unique kode.
        $this->divisi = Divisi::firstOrCreate(
            ['kode' => 'BAAK'],
            ['name' => 'BAAK']
        );

        $this->admin = User::factory()->create();
        $this->admin->assignRole('Superadmin');

        $this->sieKesiswaan = User::factory()->create();
        $this->sieKesiswaan->assignRole('Sie Kesiswaan');

        $this->ukm = Ukm::create([
            'kode' => 'UKM-TEST',
            'name' => 'UKM Test',
            'status' => 'Aktif',
        ]);

        UkmAnggota::create([
            'ukm_id' => $this->ukm->id,
            'nama' => 'Anggota Satu',
            'nim' => '2311001',
            'jabatan' => 'Ketua',
            'status_keanggotaan' => 'Aktif',
        ]);
    }

    public function test_sie_kesiswaan_can_open_presensi_form(): void
    {
        $this->actingAs($this->sieKesiswaan)
            ->get(route('presensi.create'))
            ->assertOk();
    }

    public function test_the_archive_is_created_pending_and_only_becomes_verified_after_approval(): void
    {
        $this->actingAs($this->sieKesiswaan)
            ->post(route('presensi.store'), [
                'ukm_id' => $this->ukm->id,
                'judul_kegiatan' => 'Rapat Antoni',
                'tanggal_kegiatan' => '2026-09-20',
                'pertemuan_ke' => 1,
                'anggota' => [
                    ['anggota_id' => $this->ukm->anggota->first()->id, 'status_kehadiran' => 'Hadir'],
                ],
            ]);

        $presensi = PresensiUkm::firstOrFail();

        // Born pending, otherwise approve/reject could never run.
        $this->assertSame('Menunggu Verifikasi', $presensi->status_arsip);
        $this->assertSame('Menunggu Verifikasi', $presensi->arsip->verification_status);
        $this->assertNull($presensi->reviewed_at);

        $this->actingAs($this->admin)
            ->post(route('presensi.approve', $presensi), ['catatan_reviewer' => 'OK']);

        $this->assertSame('Terverifikasi', $presensi->fresh()->status_arsip);
        $this->assertSame('Terverifikasi', $presensi->fresh()->arsip->verification_status);
        $this->assertNotNull($presensi->fresh()->reviewed_at);
        $this->assertSame($this->admin->id, $presensi->fresh()->reviewer_id);
    }

    public function test_rejecting_moves_the_session_and_its_archive_to_ditolak(): void
    {
        $this->actingAs($this->sieKesiswaan)
            ->post(route('presensi.store'), [
                'ukm_id' => $this->ukm->id,
                'judul_kegiatan' => 'Rapat Antoni',
                'tanggal_kegiatan' => '2026-09-20',
                'pertemuan_ke' => 1,
                'anggota' => [
                    ['anggota_id' => $this->ukm->anggota->first()->id, 'status_kehadiran' => 'Hadir'],
                ],
            ]);

        $presensi = PresensiUkm::firstOrFail();

        $this->actingAs($this->admin)
            ->post(route('presensi.reject', $presensi), ['catatan_reviewer' => 'Format salah']);

        $this->assertSame('Ditolak', $presensi->fresh()->status_arsip);
        $this->assertSame('Ditolak', $presensi->fresh()->arsip->verification_status);
    }

    public function test_plain_dosen_cannot_open_the_presensi_form(): void
    {
        Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);

        $dosen = User::factory()->create();
        $dosen->assignRole('Dosen');

        $this->actingAs($dosen)
            ->get(route('presensi.create'))
            ->assertForbidden();
    }

    public function test_sie_kesiswaan_can_submit_presensi_and_generate_archive(): void
    {
        $this->actingAs($this->sieKesiswaan)
            ->post(route('presensi.store'), [
                'ukm_id' => $this->ukm->id,
                'judul_kegiatan' => 'Latihan Rutin',
                'tanggal_kegiatan' => '2026-08-20',
                'pertemuan_ke' => 1,
                'anggota' => [
                    [
                        'anggota_id' => $this->ukm->anggota->first()->id,
                        'status_kehadiran' => 'Izin',
                        'keterangan' => 'Ada keperluan keluarga',
                    ],
                ],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('presensi_ukm', [
            'ukm_id' => $this->ukm->id,
            'status_arsip' => 'Menunggu Verifikasi',
        ]);

        $this->assertDatabaseHas('arsip', [
            'verification_status' => 'Menunggu Verifikasi',
            'kategori_id' => $this->kategori->id,
        ]);
    }

    public function test_only_superadmin_can_review(): void
    {
        $this->actingAs($this->sieKesiswaan)
            ->get(route('presensi.review'))
            ->assertForbidden();

        $this->actingAs($this->admin)
            ->get(route('presensi.review'))
            ->assertOk();
    }

    public function test_anggota_endpoint_returns_json(): void
    {
        $this->actingAs($this->sieKesiswaan)
            ->getJson(route('presensi.anggota', $this->ukm))
            ->assertOk()
            ->assertJsonCount(1);
    }

    public function test_the_pengisi_can_delete_their_own_unverified_presensi(): void
    {
        $presensi = $this->buatPresensi();
        $arsipId = $presensi->arsip_id;
        $pdfPath = $presensi->arsip->file_path;

        $this->actingAs($this->sieKesiswaan)
            ->delete(route('presensi.destroy', $presensi))
            ->assertRedirect(route('presensi.index'))
            ->assertSessionHas('success');

        // Session and its detail rows are gone.
        $this->assertDatabaseMissing('presensi_ukm', ['id' => $presensi->id]);
        $this->assertDatabaseMissing('presensi_ukm_detail', ['presensi_id' => $presensi->id]);

        // The generated rekap archive follows the Recycle Bin flow, and its file
        // must survive so a restore still works.
        $this->assertSoftDeleted('arsip', ['id' => $arsipId]);
        $this->assertTrue(Storage::disk('local')->exists($pdfPath), 'File rekap harus tetap ada setelah dihapus.');
    }

    public function test_a_verified_presensi_cannot_be_deleted(): void
    {
        $presensi = $this->buatPresensi();

        $this->actingAs($this->admin)
            ->post(route('presensi.approve', $presensi), ['catatan_reviewer' => 'OK']);

        $this->actingAs($this->admin)
            ->delete(route('presensi.destroy', $presensi))
            ->assertForbidden();

        $this->assertDatabaseHas('presensi_ukm', ['id' => $presensi->id]);
        $this->assertDatabaseHas('arsip', ['id' => $presensi->arsip_id, 'deleted_at' => null]);
    }

    public function test_an_admin_can_delete_a_rejected_presensi(): void
    {
        $presensi = $this->buatPresensi();

        $this->actingAs($this->admin)
            ->post(route('presensi.reject', $presensi), ['catatan_reviewer' => 'Format salah']);

        $this->actingAs($this->admin)
            ->delete(route('presensi.destroy', $presensi))
            ->assertSessionHas('success');

        $this->assertDatabaseMissing('presensi_ukm', ['id' => $presensi->id]);
        $this->assertSoftDeleted('arsip', ['id' => $presensi->arsip_id]);
    }

    public function test_another_staff_member_cannot_delete_someone_elses_presensi(): void
    {
        $presensi = $this->buatPresensi();

        $lain = User::factory()->create();
        $lain->assignRole('Sie Kesiswaan');

        $this->actingAs($lain)
            ->delete(route('presensi.destroy', $presensi))
            ->assertForbidden();

        $this->assertDatabaseHas('presensi_ukm', ['id' => $presensi->id]);
    }

    public function test_a_plain_dosen_cannot_delete_a_presensi(): void
    {
        $presensi = $this->buatPresensi();

        Role::firstOrCreate(['name' => 'Dosen', 'guard_name' => 'web']);
        $dosen = User::factory()->create();
        $dosen->assignRole('Dosen');

        $this->actingAs($dosen)
            ->delete(route('presensi.destroy', $presensi))
            ->assertForbidden();

        $this->assertDatabaseHas('presensi_ukm', ['id' => $presensi->id]);
    }

    public function test_the_index_flags_which_presensi_may_be_deleted(): void
    {
        $presensi = $this->buatPresensi();

        $this->actingAs($this->sieKesiswaan)
            ->get(route('presensi.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Presensi/Index')
                ->where('presensi.data.0.bisa_dihapus', true)
            );

        $this->actingAs($this->admin)
            ->post(route('presensi.approve', $presensi), ['catatan_reviewer' => 'OK']);

        $this->actingAs($this->sieKesiswaan)
            ->get(route('presensi.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->where('presensi.data.0.bisa_dihapus', false)
            );
    }

    /**
     * Submit a session as Sie Kesiswaan and return the stored model.
     */
    private function buatPresensi(string $tanggal = '2026-09-20'): PresensiUkm
    {
        $this->actingAs($this->sieKesiswaan)
            ->post(route('presensi.store'), [
                'ukm_id' => $this->ukm->id,
                'judul_kegiatan' => 'Rapat Rutin',
                'tanggal_kegiatan' => $tanggal,
                'pertemuan_ke' => 1,
                'anggota' => [
                    ['anggota_id' => $this->ukm->anggota->first()->id, 'status_kehadiran' => 'Hadir'],
                ],
            ])
            ->assertRedirect();

        return PresensiUkm::firstOrFail();
    }
}
