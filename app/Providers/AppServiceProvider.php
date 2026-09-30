<?php

namespace App\Providers;

use App\Models\Arsip;
use App\Models\Divisi;
use App\Models\KategoriArsip;
use App\Models\StudyProgram;
use App\Observers\ArsipObserver;
use App\Support\MasterData;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Arsip::observe(ArsipObserver::class);

        $this->flushMasterDataOnChange();

        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
    }

    /**
     * Drop the cached reference data whenever a record it is built from is
     * added, edited or removed, so dropdowns never offer stale options.
     */
    private function flushMasterDataOnChange(): void
    {
        foreach ([Divisi::class, KategoriArsip::class, StudyProgram::class, Arsip::class] as $model) {
            $model::saved(fn () => MasterData::flush());
            $model::deleted(fn () => MasterData::flush());
        }

        // Restoring a soft-deleted arsip also changes the year list.
        Arsip::restored(fn () => MasterData::flush());
    }
}
