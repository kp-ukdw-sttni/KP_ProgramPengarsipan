<?php

namespace App\Support;

use App\Models\Arsip;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\StudyProgram;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

/**
 * Short-lived cache for the reference data (unit kerja, jenis dokumen, program
 * studi, daftar tahun) that every page re-reads on each request.
 *
 * These rows almost never change, but they were previously re-queried on every
 * single request — including on each keystroke of the arsip search box. They are
 * cached for a short window and dropped immediately whenever the underlying
 * master data is edited, so the UI never shows stale options.
 */
class MasterData
{
    /**
     * How long cached reference data stays fresh, in seconds.
     */
    public const TTL = 300;

    /**
     * Cache every reference-data key at once.
     *
     * Called from the model hooks in AppServiceProvider whenever a divisi,
     * kategori, program studi or arsip record changes.
     */
    public static function flush(): void
    {
        foreach (['kategori-tree', 'kategori-flat', 'divisi-tree', 'divisi-flat', 'divisi-options', 'prodi', 'tahun', 'stats'] as $key) {
            Cache::forget(self::key($key));
        }
    }

    /**
     * Nested kategori tree used by every dropdown & filter bar.
     */
    public static function kategoriTree(): array
    {
        return Cache::remember(self::key('kategori-tree'), self::TTL, fn () => KategoriArsip::whereNull('parent_id')
            ->with(['children' => fn ($q) => $q->orderBy('name')])
            ->orderBy('name')
            ->get()
            ->map(fn ($k) => [
                'id' => $k->id,
                'name' => $k->name,
                'kode' => $k->kode,
                'children' => $k->children->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'kode' => $c->kode,
                ])->all(),
            ])
            ->all());
    }

    /**
     * Flat kategori list, alphabetical.
     */
    public static function kategoriFlat(): Collection
    {
        return Cache::remember(self::key('kategori-flat'), self::TTL, fn () => KategoriArsip::orderBy('name')->get());
    }

    /**
     * Full unit kerja tree: faculties with their children, plus standalone
     * organisational units.
     */
    public static function divisiTree(): array
    {
        return Cache::remember(self::key('divisi-tree'), self::TTL, function () {
            $fakultas = Divisi::with('children')->whereNull('parent_id')->orderBy('name')->get();
            $orgUnits = Divisi::whereNull('parent_id')->whereNull('kode')->orderBy('name')->get();

            $map = fn ($d) => [
                'id' => $d->id,
                'name' => $d->name,
                'kode' => $d->kode,
                'children' => ($d->children ?? collect())->map(fn ($c) => [
                    'id' => $c->id,
                    'name' => $c->name,
                    'kode' => $c->kode,
                    'children' => [],
                ])->all(),
            ];

            return collect($fakultas)->map($map)
                ->concat($orgUnits->map($map))
                ->values()
                ->all();
        });
    }

    /**
     * Flat unit kerja list, for select inputs.
     */
    public static function divisi(): Collection
    {
        return Cache::remember(self::key('divisi-flat'), self::TTL, fn () => Divisi::orderBy('name')->get());
    }

    /**
     * Unit kerja list trimmed to the three columns the select inputs actually
     * send to the browser.
     */
    public static function divisiOptions(): Collection
    {
        return Cache::remember(self::key('divisi-options'), self::TTL, fn () => Divisi::orderBy('name')->get(['id', 'name', 'kode']));
    }

    /**
     * Dashboard counters that only move when arsip or master data changes.
     *
     * The pending-access counter is deliberately left out: it changes on every
     * borrow request, which does not flush this cache.
     *
     * @return array{arsip: int, kategori: int, divisi: int}
     */
    public static function stats(): array
    {
        return Cache::remember(self::key('stats'), self::TTL, fn () => [
            'arsip' => Arsip::count(),
            'kategori' => KategoriArsip::whereNull('parent_id')->count(),
            'divisi' => Divisi::count(),
        ]);
    }

    /**
     * Program studi list, alphabetical.
     */
    public static function studyPrograms(): Collection
    {
        return Cache::remember(self::key('prodi'), self::TTL, fn () => StudyProgram::orderBy('name')->get());
    }

    /**
     * Years from the current year down to 1970, plus any year present in the
     * arsip table. Keyed per year so the list rolls over on 1 January.
     */
    public static function tahunList(): array
    {
        $year = (int) date('Y');

        return Cache::remember(self::key('tahun').":{$year}", self::TTL, function () use ($year) {
            $dbYears = Arsip::whereNotNull('tahun')
                ->distinct()
                ->pluck('tahun')
                ->map(fn ($t) => (int) $t)
                ->all();

            return collect(array_merge(range($year, 1970), $dbYears))
                ->unique()
                ->sortDesc()
                ->values()
                ->all();
        });
    }

    /**
     * Namespaced cache key.
     */
    private static function key(string $name): string
    {
        return "master-data:{$name}";
    }
}
