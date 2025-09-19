<?php

namespace App\Providers;

use App\Repositories\EloquentRepository;
use App\Interfaces\RepositoryInterface;
use App\Repositories\VoucherRepository;
use App\Interfaces\VoucherRepositoryInterface;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(RepositoryInterface::class, EloquentRepository::class);
        $this->app->bind(VoucherRepositoryInterface::class, VoucherRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
