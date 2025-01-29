<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\CompensationService;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // $this->app->singleton(CompensationService::class, function ($app) {
        //     return new CompensationService();
        // });
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
