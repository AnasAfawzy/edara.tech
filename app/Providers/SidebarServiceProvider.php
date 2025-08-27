<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

class SidebarServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        View::composer('layouts.sidebar', function ($view) {
            $user = Auth::user();
            $userRole = null;
            $mainModules = collect();

            if ($user) {
                $user->loadMissing(['roles.modules']);
                $userRole = $user->roles->first();

                if ($userRole) {
                    $cacheKey = "role_main_modules_{$userRole->id}";
                    $mainModules = Cache::remember($cacheKey, 1, function () use ($userRole) {
                        return $userRole->modules()
                            ->whereNull('parent_id')
                            ->with('children')
                            ->get();
                    });
                }
            }

            $view->with(compact('user', 'userRole', 'mainModules'));
        });
    }
}
