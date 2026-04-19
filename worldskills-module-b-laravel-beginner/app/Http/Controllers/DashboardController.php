<?php

namespace App\Http\Controllers;

use App\Models\Advert;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $currentUser = $request->session()->get('auth_user');

        /*
        |--------------------------------------------------------------------------
        | Статистика для Home Page
        |--------------------------------------------------------------------------
        |
        | По ТЗ здесь нужны:
        | - имя текущего пользователя
        | - количество объявлений по статусам
        | - количество пользователей
        | - топ-10 published по просмотрам
        |
        */

        $stats = [
            'moderation' => Advert::where('status', 'moderation')->count(),
            'published' => Advert::where('status', 'published')->count(),
            'declined' => Advert::where('status', 'declined')->count(),
            'users' => User::count(),
        ];

        $topAdverts = Advert::with(['category', 'author'])
            ->where('status', 'published')
            ->orderByDesc('views_count')
            ->limit(10)
            ->get();

        return view('dashboard.index', compact('currentUser', 'stats', 'topAdverts'));
    }
}
