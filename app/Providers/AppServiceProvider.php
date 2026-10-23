<?php

namespace App\Providers;

use App\Models\Perumahan;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
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
        Paginator::useBootstrapFive();

        // Bagikan data profil perumahan aktif ke seluruh view template
        View::composer('*', function ($view) {
            static $sharedPerumahan = null;
            if ($sharedPerumahan === null) {
                try {
                    if (Schema::hasTable('perumahan')) {
                        $sharedPerumahan = Perumahan::first();
                    }
                } catch (\Throwable $e) {
                    $sharedPerumahan = null;
                }
            }
            $view->with('sharedPerumahan', $sharedPerumahan);
        });
    }
}
