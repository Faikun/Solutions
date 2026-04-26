<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    // Здесь можно регистрировать сервисы приложения.
    public function register(): void
    {
        // Для учебного проекта здесь ничего не нужно.
    }

    // Здесь можно настраивать поведение приложения при загрузке.
    public function boot(): void
    {
        // Для учебного проекта здесь ничего не нужно.
    }
}
