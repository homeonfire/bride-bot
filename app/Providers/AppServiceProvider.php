<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\URL; // <-- Обязательно добавляем эту строку!

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
        // Заставляем Laravel генерировать все ссылки (включая стили Filament) с https://
        if (config('app.env') !== 'local') {
            URL::forceScheme('https');
        }
    }
}