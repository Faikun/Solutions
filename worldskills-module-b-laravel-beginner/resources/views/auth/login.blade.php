@extends('layouts.app', ['title' => 'Вход'])

@section('content')
<section class="auth-box">
    <h1>Вход в админ-панель</h1>
    <p class="muted">
        Входить могут только пользователи с ролью <strong>moderator</strong>.
        В поле логина можно ввести email или номер телефона.
    </p>

    <form action="{{ route('login.submit') }}" method="post" class="stack">
        @csrf

        <div>
            <label for="login">Email или телефон</label>
            <input
                id="login"
                name="login"
                type="text"
                value="{{ old('login') }}"
                placeholder="Например: olivia@example.com"
                required
            >
        </div>

        <div>
            <label for="password">Пароль</label>
            <input
                id="password"
                name="password"
                type="password"
                placeholder="Например: olivia_123"
                required
            >
        </div>

        <button type="submit" class="button">Войти</button>
    </form>

    <div class="note-box">
        <h2>Тестовые данные</h2>
        <p><strong>Логин:</strong> olivia@example.com</p>
        <p><strong>Пароль:</strong> olivia_123</p>
    </div>
</section>
@endsection
