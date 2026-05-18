<?php

namespace Alimarchal\IdGenerator;

use Illuminate\Support\ServiceProvider;

class IdGeneratorServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register the IdGenerator as a singleton
        $this->app->singleton(IdGenerator::class, function ($app) {
            return new IdGenerator;
        });

        // Register the helper function
        if (! function_exists('generateUniqueId')) {
            require_once __DIR__.'/helpers.php';
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Publish migration if needed
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../database/migrations/' => database_path('migrations'),
            ], 'id-generator-migrations');
        }
    }
}
