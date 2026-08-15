<?php

namespace Tests\Feature;

use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\Ukm;
use App\Models\UkmAnggota;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
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

        $this->kategori = KategoriArsip::create(['name' => 'PRESENSI', 'kode' => 'PRESENSI']);
        $this->divisi = Divisi::create(['name' => 'BAAK', 'kode' => 'BAAK']);

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
}
