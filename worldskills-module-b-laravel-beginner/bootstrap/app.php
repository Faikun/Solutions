<?php

/*
|--------------------------------------------------------------------------
| Bootstrap Laravel application
|--------------------------------------------------------------------------
|
| Этот файл создаёт Laravel-приложение, подключает маршруты и регистрирует
| короткое имя middleware "moderator". Middleware используется в routes/web.php,
| чтобы закрыть админ-панель от пользователей без роли moderator.
|
*/

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__ . '/../routes/web.php',
        commands: __DIR__ . '/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware) {
        $middleware->alias([
            'moderator' => \App\Http\Middleware\ModeratorOnly::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions) {
        // Для учебного проекта отдельная настройка обработчика ошибок не нужна.
    })
    ->create();
