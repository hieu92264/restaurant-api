<?php

namespace App\Providers;

use App\Http\interfaces\ITableStatusService;
use App\Http\services\TableStatusService;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ITableStatusService::class, TableStatusService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
