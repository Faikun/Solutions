<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;

class ApiTokenAuth
{
    /*
    |--------------------------------------------------------------------------
    | Проверка Bearer token
    |--------------------------------------------------------------------------
    |
    | Этот middleware защищает приватные маршруты API.
    |
    | Что он делает шаг за шагом:
    | 1. Берёт токен из заголовка Authorization.
    | 2. Проверяет, передан ли токен вообще.
    | 3. Ищет пользователя с таким api_token.
    | 4. Если пользователь найден — "прикрепляет" его к запросу.
    | 5. Если нет — возвращает 401 Unauthorized.
    |
    */
    public function handle(Request $request, Closure $next)
    {
        // Получаем Bearer token из заголовка Authorization.
        // Например: Authorization: Bearer abcdef123...
        $token = $request->bearerToken();

        // Если токен не передали вообще — это неавторизованный запрос.
        if (!$token) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        // Ищем пользователя по сохранённому токену.
        $user = User::where('api_token', $token)->first();

        // Если токен есть, но пользователя с таким токеном нет,
        // значит токен неверный или уже сброшен.
        if (!$user) {
            return response()->json(['error' => 'Invalid token'], 401);
        }

        // Говорим Laravel, что у текущего запроса есть авторизованный пользователь.
        // После этого в контроллере можно писать: $request->user()
        $request->setUserResolver(fn () => $user);

        // Передаём запрос дальше, в контроллер.
        return $next($request);
    }
}
