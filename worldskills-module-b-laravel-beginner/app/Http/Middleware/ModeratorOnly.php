<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ModeratorOnly
{
    /*
    |--------------------------------------------------------------------------
    | Простая защита админки
    |--------------------------------------------------------------------------
    |
    | Для задания доступ в панель должен быть только у moderator.
    | Мы проверяем:
    | 1. Есть ли пользователь в сессии
    | 2. Равна ли роль "moderator"
    |
    */

    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->session()->get('auth_user');

        if (!$user) {
            return redirect()->route('login.form')->with('error', 'Сначала войдите в систему.');
        }

        if (($user['role'] ?? null) !== 'moderator') {
            $request->session()->forget('auth_user');

            return redirect()->route('login.form')->with(
                'error',
                'Доступ разрешён только пользователям с ролью moderator.'
            );
        }

        return $next($request);
    }
}
