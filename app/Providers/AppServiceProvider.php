<?php

namespace App\Providers;

use App\Models\Medicine;
use App\Observers\MedicineObserver;
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
        // F-05 PRD: Automatically check drug interactions when a medicine is saved
        Medicine::observe(MedicineObserver::class);
    }
}
