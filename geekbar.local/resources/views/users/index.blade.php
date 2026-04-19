@extends('layouts.app', ['title' => 'Пользователи'])

@section('content')
<section class="page-head">
    <h1>Пользователи</h1>
    <p class="muted">Одно поле поиска: точное совпадение по ID, телефону или email.</p>
</section>

<section class="card">
    <form action="{{ route('users.index') }}" method="get" class="filter-grid">
        <div>
            <label for="search">Поиск</label>
            <input id="search" name="search" type="text" value="{{ $search }}" placeholder="Например: 5 или emma@example.com">
        </div>

        <div class="filter-actions">
            <button type="submit" class="button">Искать</button>
            <a href="{{ route('users.index') }}" class="button button-light">Сбросить</a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Имя</th>
                    <th>Телефон</th>
                    <th>Email</th>
                    <th>Published adverts</th>
                </tr>
            </thead>
            <tbody>
                @forelse($users as $user)
                    <tr>
                        <td>{{ $user->id }}</td>
                        <td>{{ $user->name }}</td>
                        <td>{{ $user->phone }}</td>
                        <td>{{ $user->email }}</td>
                        <td>{{ $user->published_adverts_count }}</td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5">Пользователи не найдены.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
