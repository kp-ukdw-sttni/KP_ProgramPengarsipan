<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Verifies the upload modal actually lists jenis dokumen and unit kerja.
 *
 * The dropdowns are rendered client-side from /arsip/meta, so a plain HTTP
 * check cannot catch them being empty. This test opens the real modal in a
 * browser and counts the rendered options.
 *
 * Read-only: it never migrates or truncates the database.
 */
class UploadModalOptionsTest extends DuskTestCase
{
    /**
     * Every seeded account, so a role-specific regression cannot hide.
     *
     * @return array<string, array{0: string, 1: string}>
     */
    public static function accounts(): array
    {
        return [
            'admin' => ['admin@sttni.ac.id', 'admin123'],
            'dosen kemahasiswaan' => ['dosen.kemahasiswaan@sttni.ac.id', 'dosen123'],
            'dosen teologi' => ['dosen.teologi@sttni.ac.id', 'dosen123'],
            'dosen pak' => ['dosen.pak@sttni.ac.id', 'dosen123'],
            'dosen magister teologi' => ['staf.akademik@sttni.ac.id', 'dosen123'],
        ];
    }

    #[\PHPUnit\Framework\Attributes\DataProvider('accounts')]
    public function test_upload_modal_lists_document_types_and_work_units(string $email, string $password): void
    {
        $this->browse(function (Browser $browser) use ($email, $password) {
            $browser->visit('/login')
                ->type('input[name=email]', $email)
                ->type('input[name=password]', $password)
                ->press('button[type=submit]')
                ->waitForLocation('/dashboard')
                ->assertPathIs('/dashboard');

            $browser->waitForText('Unggah Berkas Baru')
                ->press('Unggah Berkas Baru')
                ->waitForText('Formulir Unggah Dokumen')
                ->waitForLocation('/dashboard');

            // Wait until the async meta request has populated the tree.
            $browser->waitUntil('document.querySelectorAll("select").length > 0', 10);

            $counts = $browser->script([
                'return Array.from(document.querySelectorAll("select")).map(s => ({
                    options: s.options.length,
                    placeholder: s.options.length ? s.options[0].textContent.trim() : "",
                    disabled: s.disabled,
                }))',
            ])[0];

            $this->assertNotEmpty($counts, 'Tidak ada <select> yang ter-render di modal unggah.');

            $kategori = null;
            foreach ($counts as $select) {
                if (str_contains($select['placeholder'], 'Jenis Dokumen')) {
                    $kategori = $select;
                }
            }

            $this->assertNotNull(
                $kategori,
                'Dropdown Jenis Dokumen tidak ditemukan. Placeholder yang tampil: '
                    .implode(' | ', array_column($counts, 'placeholder'))
            );

            // One placeholder plus at least one real jenis dokumen.
            $this->assertGreaterThan(
                1,
                $kategori['options'],
                'Dropdown Jenis Dokumen kosong untuk '.$email
            );
        });
    }

    public function test_admin_sees_the_work_unit_dropdown_populated(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('input[name=email]', 'admin@sttni.ac.id')
                ->type('input[name=password]', 'admin123')
                ->press('button[type=submit]')
                ->waitForLocation('/dashboard');

            $browser->waitForText('Unggah Berkas Baru')
                ->press('Unggah Berkas Baru')
                ->waitForText('Formulir Unggah Dokumen')
                ->waitUntil('document.querySelectorAll("select").length > 0', 10);

            $counts = $browser->script([
                'return Array.from(document.querySelectorAll("select")).map(s => ({
                    options: s.options.length,
                    placeholder: s.options.length ? s.options[0].textContent.trim() : "",
                }))',
            ])[0];

            $divisi = null;
            foreach ($counts as $select) {
                if (str_contains($select['placeholder'], 'Unit Kerja')) {
                    $divisi = $select;
                }
            }

            $this->assertNotNull(
                $divisi,
                'Dropdown Unit Kerja tidak ditemukan untuk Admin. Placeholder: '
                    .implode(' | ', array_column($counts, 'placeholder'))
            );

            $this->assertGreaterThan(
                1,
                $divisi['options'],
                'Dropdown Unit Kerja kosong untuk Admin'
            );
        });
    }
}
