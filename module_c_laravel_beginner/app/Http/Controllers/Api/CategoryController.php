<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;

class CategoryController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Публичный список категорий
    |--------------------------------------------------------------------------
    |
    | По Module C категории доступны без авторизации.
    | Поэтому этот метод очень простой:
    | - берём категории из базы;
    | - сортируем по имени;
    | - возвращаем только id и name.
    |
    */
    public function index()
    {
        return response()->json(
            Category::orderBy('name')->get(['id', 'name'])
        );
    }
}
