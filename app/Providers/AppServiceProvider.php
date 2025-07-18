<?php

namespace App\Providers;

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
        // Share the count of SOs in packings on hold with all views
        \View::composer('*', function ($view) {
            $shortSoCount = \App\Models\Picking::where('status', 'hold')->get()->groupBy('so_no')->count();
            $view->with('shortSoCount', $shortSoCount);
        });
    }
}
