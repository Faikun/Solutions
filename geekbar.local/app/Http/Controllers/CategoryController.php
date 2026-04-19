<?php

namespace App\Http\Controllers;

use App\Models\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index()
    {
        /*
        |--------------------------------------------------------------------------
        | Список категорий
        |--------------------------------------------------------------------------
        |
        | Показываем:
        | - ID
        | - название
        | - количество объявлений со статусом published
        |
        */

        $categories = Category::withCount([
            'adverts as published_adverts_count' => function ($query) {
                $query->where('status', 'published');
            },
            'adverts as all_adverts_count',
        ])->orderBy('id')->get();

        return view('categories.index', compact('categories'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'external_id' => ['required', 'string', 'max:50', 'unique:categories,external_id'],
            'name' => ['required', 'string', 'max:255'],
        ]);

        Category::create($validated);

        return back()->with('success', 'Категория добавлена.');
    }

    public function update(Request $request, Category $category)
    {
        $validated = $request->validate([
            'external_id' => ['required', 'string', 'max:50', 'unique:categories,external_id,' . $category->id],
            'name' => ['required', 'string', 'max:255'],
        ]);

        $category->update($validated);

        return back()->with('success', 'Категория обновлена.');
    }

    public function destroy(Category $category)
    {
        /*
        |--------------------------------------------------------------------------
        | Важное правило по ТЗ
        |--------------------------------------------------------------------------
        |
        | Категорию нельзя удалять, если к ней привязаны объявления
        | в любом статусе.
        |
        */

        if ($category->adverts()->exists()) {
            return back()->with('error', 'Нельзя удалить категорию, к которой привязаны объявления.');
        }

        $category->delete();

        return back()->with('success', 'Категория удалена.');
    }
}
