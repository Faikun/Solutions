@extends('layouts.app', ['title' => 'Главная'])

@section('content')
<section class="page-head">
    <h1>Главная страница админ-панели</h1>
    <p class="muted">Здравствуйте, {{ $currentUser['name'] }}.</p>
</section>

<section class="stats-grid">
    <article class="stat-card">
        <h2>На модерации</h2>
        <p class="stat-value">{{ $stats['moderation'] }}</p>
    </article>

    <article class="stat-card">
        <h2>Опубликовано</h2>
        <p class="stat-value">{{ $stats['published'] }}</p>
    </article>

    <article class="stat-card">
        <h2>Отклонено</h2>
        <p class="stat-value">{{ $stats['declined'] }}</p>
    </article>

    <article class="stat-card">
        <h2>Пользователей</h2>
        <p class="stat-value">{{ $stats['users'] }}</p>
    </article>
</section>

<section class="card">
    <h2>Топ 10 объявлений по просмотрам</h2>

    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Категория</th>
                    <th>Автор</th>
                    <th>Просмотры</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                @forelse($topAdverts as $advert)
                    <tr>
                        <td>{{ $advert->id }}</td>
                        <td>{{ $advert->title }}</td>
                        <td>{{ $advert->category->name ?? '—' }}</td>
                        <td>{{ $advert->author->name ?? '—' }}</td>
                        <td>{{ $advert->views_count }}</td>
                        <td>
                            <a href="{{ route('adverts.show', $advert) }}">Открыть</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6">Нет опубликованных объявлений.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
