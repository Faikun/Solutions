<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Регистрация нового пользователя
    |--------------------------------------------------------------------------
    |
    | По заданию пользователь указывает:
    | - имя (необязательно);
    | - телефон;
    | - email;
    | - пароль.
    |
    | После успешной регистрации:
    | - пароль сохраняется в хеше;
    | - роль выставляется в user;
    | - создаётся Bearer token длиной 64 символа.
    |
    */
    public function register(Request $request)
    {
        // validate() автоматически:
        // 1. проверяет входные данные;
        // 2. при ошибке возвращает 422 с JSON-ошибками.
        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'phone' => ['required', 'string', 'regex:/^\+\d{10,15}$/', 'unique:users,phone'],
            'email' => ['required', 'email', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'max:50'],
        ]);

        // Создаём пользователя в таблице users.
        $user = User::create([
            // Если имя не пришло, сохраняем null.
            'name' => $data['name'] ?? null,

            // Телефон и email обязательны.
            'phone' => $data['phone'],
            'email' => $data['email'],

            // Пароль хешируем, а не сохраняем как plain text.
            'password' => Hash::make($data['password']),

            // При регистрации обычный пользователь получает роль user.
            'role' => 'user',

            // Генерируем простой API-токен длиной 64 символа.
            // Этого достаточно для учебного решения Module C.
            'api_token' => Str::random(64),
        ]);

        // Возвращаем токен клиенту.
        return response()->json([
            'access_token' => $user->api_token,
        ], 201);
    }

    /*
    |--------------------------------------------------------------------------
    | Вход по email или телефону
    |--------------------------------------------------------------------------
    |
    | По заданию логин возможен по:
    | - email;
    | - phone.
    |
    | Поэтому поле называется login, а не email.
    |
    */
    public function login(Request $request)
    {
        // Проверяем, что оба поля пришли.
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ]);

        // Ищем пользователя либо по email, либо по телефону.
        $user = User::where('email', $data['login'])
            ->orWhere('phone', $data['login'])
            ->first();

        // Если пользователя нет или пароль не совпал — 401.
        if (!$user || !Hash::check($data['password'], $user->password)) {
            return response()->json(['error' => 'Invalid credentials'], 401);
        }

        // На каждый успешный вход обновляем токен.
        // Так старый токен становится неактуальным.
        $user->update(['api_token' => Str::random(64)]);

        // Возвращаем новый токен.
        return response()->json([
            'access_token' => $user->api_token,
        ]);
    }

    /*
    |--------------------------------------------------------------------------
    | Выход из системы
    |--------------------------------------------------------------------------
    |
    | Здесь логика очень простая:
    | - у текущего пользователя очищаем api_token;
    | - после этого старый Bearer token перестаёт работать.
    |
    */
    public function logout(Request $request)
    {
        // $request->user() уже доступен благодаря middleware ApiTokenAuth.
        $request->user()->update(['api_token' => null]);

        // 204 = успешный ответ без тела.
        return response()->json(null, 204);
    }
}
