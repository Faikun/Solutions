<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>{{ $title ?? 'Админ-панель модератора' }}</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="Админ-панель модератора для сервиса объявлений.">
    <link rel="stylesheet" href="{{ asset('css/style.css') }}">
</head>
<body>
    <header class="site-header">
        <div class="container header-row">
            <div>
                <a class="logo" href="{{ route('dashboard') }}">Geek Bazaar Admin</a>
                <p class="header-note">Простая Laravel-версия для начинающих</p>
            </div>

            @php
                $authUser = session('auth_user');
            @endphp

            @if($authUser)
                <nav class="main-nav" aria-label="Основная навигация">
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'is-active' : '' }}">Home</a>
                    <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'is-active' : '' }}">Categories</a>
                    <a href="{{ route('adverts.index') }}" class="{{ request()->routeIs('adverts.*') ? 'is-active' : '' }}">Adverts</a>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'is-active' : '' }}">Users</a>
                </nav>

                <form action="{{ route('logout') }}" method="post">
                    @csrf
                    <button type="submit" class="button button-light">Выйти</button>
                </form>
            @endif
        </div>
    </header>

    <main class="container page-content">
        @include('partials.flash')
        {{ $slot ?? '' }}
        @yield('content')
    </main>
</body>
</html>
