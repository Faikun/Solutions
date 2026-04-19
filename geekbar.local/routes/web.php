<?php

use App\Http\Controllers\AdvertController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Публичные маршруты
|--------------------------------------------------------------------------
|
| Здесь у нас только вход и выход. Все остальные страницы доступны
| только после авторизации модератора.
|
*/

Route::get('/', function () {
    return redirect()->route('login.form');
});

Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login.form');
Route::post('/login', [AuthController::class, 'login'])->name('login.submit');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

/*
|--------------------------------------------------------------------------
| Защищённые маршруты
|--------------------------------------------------------------------------
|
| auth  -> пользователь должен быть в сессии
| moderator -> пользователь обязан иметь роль moderator
|
*/

Route::middleware(['web', 'moderator'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::post('/categories/{category}/update', [CategoryController::class, 'update'])->name('categories.update');
    Route::post('/categories/{category}/delete', [CategoryController::class, 'destroy'])->name('categories.destroy');

    Route::get('/users', [UserController::class, 'index'])->name('users.index');

    Route::get('/adverts', [AdvertController::class, 'index'])->name('adverts.index');
    Route::get('/adverts/export', [AdvertController::class, 'export'])->name('adverts.export');
    Route::get('/adverts/{advert}', [AdvertController::class, 'show'])->name('adverts.show');
    Route::post('/adverts/{advert}/status', [AdvertController::class, 'updateStatus'])->name('adverts.status');
    Route::post('/adverts/{advert}/services/toggle', [AdvertController::class, 'toggleService'])->name('adverts.services.toggle');
});
