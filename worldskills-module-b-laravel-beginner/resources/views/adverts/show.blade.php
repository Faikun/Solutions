@extends('layouts.app', ['title' => 'Детали объявления'])

@section('content')
<section class="page-head">
    <h1>Объявление #{{ $advert->id }}</h1>
    <p class="muted">Страница деталей объявления для модератора.</p>
</section>

<section class="three-columns">
    <article class="card">
        <h2>Основная информация</h2>

        <dl class="info-list">
            <dt>ID</dt>
            <dd>{{ $advert->id }}</dd>

            <dt>Заголовок</dt>
            <dd>{{ $advert->title }}</dd>

            <dt>Текст</dt>
            <dd>{{ $advert->text }}</dd>

            <dt>Цена</dt>
            <dd>${{ number_format($advert->price, 2) }}</dd>

            <dt>Просмотры</dt>
            <dd>{{ $advert->views_count }}</dd>

            <dt>Категория</dt>
            <dd>{{ $advert->category->name ?? '—' }}</dd>

            <dt>Статус</dt>
            <dd><span class="status-badge status-{{ $advert->status }}">{{ $advert->status }}</span></dd>
        </dl>
    </article>

    <article class="card">
        <h2>Автор объявления</h2>

        <dl class="info-list">
            <dt>ID</dt>
            <dd>{{ $advert->author->id ?? '—' }}</dd>

            <dt>Имя</dt>
            <dd>{{ $advert->author->name ?? '—' }}</dd>

            <dt>Телефон</dt>
            <dd>{{ $advert->author->phone ?? '—' }}</dd>

            <dt>Email</dt>
            <dd>{{ $advert->author->email ?? '—' }}</dd>
        </dl>
    </article>

    <article class="card">
        <h2>Смена статуса</h2>

        @if(count($allowedTransitions))
            <form action="{{ route('adverts.status', $advert) }}" method="post" class="stack">
                @csrf

                <div>
                    <label for="status">Новый статус</label>
                    <select id="status" name="status" required>
                        @foreach($allowedTransitions as $transition)
                            <option value="{{ $transition }}">{{ $transition }}</option>
                        @endforeach
                    </select>
                </div>

                <button type="submit" class="button">Обновить статус</button>
            </form>
        @else
            <p class="muted">Для текущего статуса переходы не предусмотрены.</p>
        @endif
    </article>
</section>

<section class="card">
    <h2>Фотографии</h2>

    <div class="photo-grid">
        @forelse($advert->photos as $photo)
            <figure class="photo-card">
                <img
                    src="{{ asset('media-files/images/' . $photo->file_name) }}"
                    alt="Фото объявления {{ $advert->id }}"
                >
                <figcaption>{{ $photo->file_name }}</figcaption>
            </figure>
        @empty
            <p>Фотографий нет.</p>
        @endforelse
    </div>
</section>

<section class="two-columns">
    <article class="card">
        <h2>Подключённые и прошлые платные услуги</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>Тип</th>
                        <th>Дата подключения</th>
                        <th>Срок действия</th>
                        <th>Активна сейчас</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($advert->paidServices as $service)
                        <tr>
                            <td>{{ $service->service_type }}</td>
                            <td>{{ optional($service->connected_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ optional($service->expires_at)->format('Y-m-d H:i') }}</td>
                            <td>{{ $service->is_active ? 'Да' : 'Нет' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4">Платных услуг пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>

    <article class="card">
        <h2>Подключить или отключить услугу</h2>

        <p class="muted">
            Для учебной версии используем 3 типа услуг: <strong>top</strong>, <strong>vip</strong>, <strong>premium</strong>.
            Нажатие по кнопке работает как переключатель.
        </p>

        <div class="button-list">
            @foreach(['top', 'vip', 'premium'] as $serviceType)
                <form action="{{ route('adverts.services.toggle', $advert) }}" method="post">
                    @csrf
                    <input type="hidden" name="service_type" value="{{ $serviceType }}">
                    <button type="submit" class="button button-light">
                        Переключить {{ $serviceType }}
                    </button>
                </form>
            @endforeach
        </div>
    </article>
</section>
@endsection
