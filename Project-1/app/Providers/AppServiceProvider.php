<?php

namespace App\Providers;

use App\Repositories\AirportRepository;
use App\Repositories\Interfaces\PlaneTripRepositoryInterface;
use App\Repositories\PlaneTripRepository;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(PlaneTripRepositoryInterface::class,PlaneTripRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
