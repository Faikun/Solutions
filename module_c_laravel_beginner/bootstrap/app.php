<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        // Файл обычных web-маршрутов.
        web: __DIR__.'/../routes/web.php',

        // Файл API-маршрутов.
        api: __DIR__.'/../routes/api.php',

        // Файл консольных команд.
        commands: __DIR__.'/../routes/console.php',

        // Стандартный health-check endpoint Laravel.
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Регистрируем короткое имя middleware для Bearer token auth.
        $middleware->alias([
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Здесь можно настраивать обработку ошибок.
        // Для учебной версии ничего не добавляем.
    })
    ->create();
