<?php

namespace App\Providers;

use App\Models\Setting;
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
        View::composer('*', function ($view) {
            $businessName = 'JewelERP';
            $tagline = 'Jewellery Suite';

            try {
                if (Schema::hasTable('settings')) {
                    $businessName = Setting::get('business_name', 'JewelERP') ?: 'JewelERP';
                    $tagline = Setting::get('tagline', 'Jewellery Suite') ?: 'Jewellery Suite';
                }
            } catch (\Throwable) {
                // fallback if database is not reachable during build or before migration
            }

            $view->with('businessName', $businessName);
            $view->with('businessTagline', $tagline);
        });
    }
}
