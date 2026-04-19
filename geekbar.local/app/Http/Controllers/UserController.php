<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class UserController extends Controller
{
    public function index(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Поиск ровно по одному полю
        |--------------------------------------------------------------------------
        |
        | По ТЗ одно поле должно искать точным совпадением по:
        | - ID
        | - телефону
        | - email
        |
        */

        $search = trim((string) $request->query('search', ''));

        $users = User::query()
            ->withCount([
                'adverts as published_adverts_count' => function ($query) {
                    $query->where('status', 'published');
                }
            ])
            ->when($search !== '', function ($query) use ($search) {
                $query->where(function ($nested) use ($search) {
                    if (ctype_digit($search)) {
                        $nested->orWhere('id', (int) $search);
                    }

                    $nested->orWhere('phone', $search)
                           ->orWhere('email', $search);
                });
            })
            ->orderBy('id')
            ->get();

        return view('users.index', compact('users', 'search'));
    }
}
