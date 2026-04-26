<?php

use App\Http\Controllers\Api\AdvertController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\UserController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API-маршруты Module C
|--------------------------------------------------------------------------
|
| Все маршруты из этого файла автоматически получают префикс /api.
| То есть Route::get('/categories') будет доступен как /api/categories.
|
*/

// Регистрация и вход доступны гостю.
Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

// Категории публичные.
Route::get('/categories', [CategoryController::class, 'index']);

// Объявления и детали объявления публичные.
Route::get('/adverts', [AdvertController::class, 'index']);
Route::get('/adverts/{advert}', [AdvertController::class, 'show']);

// Всё ниже требует Bearer token.
Route::middleware('api.token')->group(function () {
    // Выход.
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // Профиль пользователя.
    Route::get('/user', [UserController::class, 'show']);
    Route::patch('/user', [UserController::class, 'update']);
    Route::get('/user/adverts', [UserController::class, 'adverts']);

    // CRUD и статусные действия по объявлениям.
    Route::post('/adverts', [AdvertController::class, 'store']);
    Route::patch('/adverts/{advert}', [AdvertController::class, 'update']);
    Route::delete('/adverts/{advert}', [AdvertController::class, 'destroy']);
    Route::post('/adverts/{advert}/update-status', [AdvertController::class, 'updateStatus']);

    // Endpoints для Advantage Services.
    Route::get('/adverts/{advert}/services', [AdvertController::class, 'services']);
    Route::post('/adverts/{advert}/services/{type}/enable', [AdvertController::class, 'enableService']);
    Route::post('/adverts/{advert}/services/{type}/extend', [AdvertController::class, 'extendService']);
});
