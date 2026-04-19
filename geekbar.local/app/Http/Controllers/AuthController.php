<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Страницы входа
    |--------------------------------------------------------------------------
    */

    public function showLoginForm(Request $request)
    {
        // Если модератор уже вошёл, сразу отправляем его на главную панель.
        if ($request->session()->has('auth_user')) {
            return redirect()->route('dashboard');
        }

        return view('auth.login');
    }

    public function login(Request $request)
    {
        /*
        |--------------------------------------------------------------------------
        | Валидация формы
        |--------------------------------------------------------------------------
        |
        | По ТЗ логином может быть телефон ИЛИ email.
        |
        */

        $validated = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
        ], [
            'login.required' => 'Введите email или телефон.',
            'password.required' => 'Введите пароль.',
        ]);

        $login = trim($validated['login']);

        // Ищем пользователя по email ИЛИ по номеру телефона.
        $user = User::query()
            ->where('email', $login)
            ->orWhere('phone', $login)
            ->first();

        if (!$user) {
            return back()->withInput()->with('error', 'Пользователь не найден.');
        }

        /*
        |--------------------------------------------------------------------------
        | Проверка пароля
        |--------------------------------------------------------------------------
        |
        | Seeder сохранит пароли в bcrypt, поэтому здесь используем Hash::check.
        |
        */

        if (!Hash::check($validated['password'], $user->password)) {
            return back()->withInput()->with('error', 'Неверный пароль.');
        }

        if ($user->role !== 'moderator') {
            return back()->withInput()->with('error', 'Вход разрешён только модераторам.');
        }

        // Сохраняем в сессию только простые данные, чтобы было легко понять логику.
        $request->session()->put('auth_user', [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $user->role,
        ]);

        return redirect()->route('dashboard')->with('success', 'Вы успешно вошли в систему.');
    }

    public function logout(Request $request)
    {
        $request->session()->forget('auth_user');

        return redirect()->route('login.form')->with('success', 'Вы вышли из системы.');
    }
}
