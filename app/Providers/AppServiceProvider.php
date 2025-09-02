<?php

namespace App\Providers;

use App\Models\Module;
use App\Observers\ModuleObserver;
use App\Repositories\AccountRepository;
use Illuminate\Support\ServiceProvider;
use App\Repositories\Contracts\AccountRepositoryInterface;

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
        Module::observe(ModuleObserver::class);
    }
}
