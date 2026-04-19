@extends('layouts.app', ['title' => 'Объявления'])

@section('content')
<section class="page-head">
    <h1>Объявления</h1>
    <p class="muted">Фильтры работают через обычную HTML-форму — так новичкам проще понять логику.</p>
</section>

<section class="card">
    <form action="{{ route('adverts.index') }}" method="get" class="filter-grid filter-grid-3">
        <div>
            <label for="status">Статус</label>
            <select id="status" name="status">
                <option value="">Все</option>
                <option value="moderation" @selected($status === 'moderation')>moderation</option>
                <option value="published" @selected($status === 'published')>published</option>
                <option value="declined" @selected($status === 'declined')>declined</option>
            </select>
        </div>

        <div>
            <label for="category_id">Категория</label>
            <select id="category_id" name="category_id">
                <option value="">Все</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected((string) $categoryId === (string) $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div>
            <label for="text">Текст</label>
            <input id="text" name="text" type="text" value="{{ $text }}" placeholder="Поиск по title, text, category, author">
        </div>

        <div class="filter-actions">
            <button type="submit" class="button">Применить фильтры</button>
            <a href="{{ route('adverts.index') }}" class="button button-light">Сбросить</a>

            <a
                href="{{ route('adverts.export', ['status' => $status, 'category_id' => $categoryId, 'text' => $text]) }}"
                class="button button-success"
            >
                Экспорт CSV
            </a>
        </div>
    </form>
</section>

<section class="card">
    <div class="table-wrap">
        <table>
            <thead>
                <tr>
                    <th>ID</th>
                    <th>Заголовок</th>
                    <th>Категория</th>
                    <th>Автор</th>
                    <th>Статус</th>
                    <th>Цена</th>
                    <th>Платные услуги</th>
                    <th>Действие</th>
                </tr>
            </thead>
            <tbody>
                @forelse($adverts as $advert)
                    <tr>
                        <td>{{ $advert->id }}</td>
                        <td>{{ $advert->title }}</td>
                        <td>{{ $advert->category->name ?? '—' }}</td>
                        <td>{{ $advert->author->name ?? '—' }}</td>
                        <td>
                            <span class="status-badge status-{{ $advert->status }}">{{ $advert->status }}</span>
                        </td>
                        <td>${{ number_format($advert->price, 2) }}</td>
                        <td>
                            @php
                                $activeServices = $advert->paidServices->where('is_active', true);
                            @endphp

                            @if($activeServices->count())
                                @foreach($activeServices as $service)
                                    <span class="service-badge">{{ $service->service_type }}</span>
                                @endforeach
                            @else
                                <span class="muted">Нет</span>
                            @endif
                        </td>
                        <td>
                            <a href="{{ route('adverts.show', $advert) }}">Открыть</a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8">Объявления не найдены.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
