<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Профиль текущего пользователя
    |--------------------------------------------------------------------------
    |
    | Этот метод возвращает данные уже авторизованного пользователя.
    | Пользователь берётся из middleware через $request->user().
    |
    */
    public function show(Request $request)
    {
        return response()->json($this->formatUser($request->user()));
    }

    /*
    |--------------------------------------------------------------------------
    | Обновление профиля
    |--------------------------------------------------------------------------
    |
    | Пользователь может менять:
    | - name;
    | - phone;
    | - email.
    |
    | Проверяем уникальность phone/email, но игнорируем текущего пользователя,
    | чтобы он мог сохранить профиль без ложной ошибки.
    |
    */
    public function update(Request $request)
    {
        $user = $request->user();

        $data = $request->validate([
            'name' => ['nullable', 'string', 'max:100'],
            'phone' => ['sometimes', 'string', 'regex:/^\+\d{10,15}$/', Rule::unique('users', 'phone')->ignore($user->id)],
            'email' => ['sometimes', 'email', Rule::unique('users', 'email')->ignore($user->id)],
        ]);

        $user->update($data);

        return response()->json($this->formatUser($user->fresh()));
    }

    /*
    |--------------------------------------------------------------------------
    | Список объявлений текущего пользователя
    |--------------------------------------------------------------------------
    |
    | По заданию пользователь может получить список своих объявлений
    | и отфильтровать его по статусу.
    |
    */
    public function adverts(Request $request)
    {
        // Если статус явно не передали, по умолчанию показываем published.
        $status = $request->query('status', 'published');

        $adverts = $request->user()
            ->adverts()
            ->with(['category', 'user'])
            ->when($status, fn ($query) => $query->where('status', $status))
            ->latest()
            ->get()
            // Чтобы не дублировать формат ответа, переиспользуем formatAdvert.
            ->map(fn ($advert) => app(AdvertController::class)->formatAdvert($advert));

        return response()->json($adverts);
    }

    /*
    |--------------------------------------------------------------------------
    | Приводим пользователя к понятному JSON-формату
    |--------------------------------------------------------------------------
    */
    private function formatUser($user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'phone' => $user->phone,
            'email' => $user->email,
            'role' => $user->role,
            'created_at' => $user->created_at?->toISOString(),
            'updated_at' => $user->updated_at?->toISOString(),
        ];
    }
}
