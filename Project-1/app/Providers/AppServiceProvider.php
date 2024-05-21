<?php

namespace App\Providers;

use App\Repositories\BookRepository;
use App\Repositories\DynamicBookRepository;
use App\Repositories\Interfaces\BookRepositoryInterface;
use App\Repositories\Interfaces\DynamicBookRepositoryInterface;
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
        $this->app->bind(BookRepositoryInterface::class,BookRepository::class);
        $this->app->bind(DynamicBookRepositoryInterface::class,DynamicBookRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
