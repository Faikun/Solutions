<?php

use Illuminate\Support\Facades\Route;

// Корень проекта нужен только как подсказка.
Route::get('/', function () {
    return response()->json([
        'message' => 'Geek Bazaar REST API. Open /api/categories or read README.md.',
    ]);
});
