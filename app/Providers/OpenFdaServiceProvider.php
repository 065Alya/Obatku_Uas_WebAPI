<?php

namespace App\Providers;

use App\Http\Clients\OpenFdaClient;
use App\Services\OpenFda\DrugInteractionService;
use App\Services\OpenFda\DrugLiteracyService;
use App\Services\OpenFda\DrugSearchService;
use App\Services\OpenFda\OpenFdaCacheService;
use App\Services\OpenFda\Transformers\DrugLabelTransformer;
use App\Services\OpenFda\Transformers\InteractionTransformer;
use Illuminate\Support\ServiceProvider;

/**
 * OpenFDA Service Provider
 *
 * Binds all OpenFDA components into the service container as singletons.
 * Registered in config/app.php under 'providers'.
 */
class OpenFdaServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        // ── Infrastructure ────────────────────────────────────────────────
        $this->app->singleton(OpenFdaClient::class);
        $this->app->singleton(OpenFdaCacheService::class);

        // ── Transformers ─────────────────────────────────────────────────
        $this->app->singleton(DrugLabelTransformer::class);
        $this->app->singleton(InteractionTransformer::class);

        // ── Domain Services ───────────────────────────────────────────────
        $this->app->singleton(DrugSearchService::class, function ($app) {
            return new DrugSearchService(
                $app->make(OpenFdaClient::class),
                $app->make(OpenFdaCacheService::class),
                $app->make(DrugLabelTransformer::class),
            );
        });

        $this->app->singleton(DrugInteractionService::class, function ($app) {
            return new DrugInteractionService(
                $app->make(OpenFdaClient::class),
                $app->make(OpenFdaCacheService::class),
                $app->make(InteractionTransformer::class),
            );
        });

        $this->app->singleton(DrugLiteracyService::class, function ($app) {
            return new DrugLiteracyService(
                $app->make(OpenFdaClient::class),
                $app->make(OpenFdaCacheService::class),
                $app->make(DrugLabelTransformer::class),
            );
        });
    }

    public function boot(): void
    {
        // Publish the config file so users can override defaults
        $this->publishes([
            __DIR__ . '/../../config/openfda.php' => config_path('openfda.php'),
        ], 'openfda-config');
    }
}
