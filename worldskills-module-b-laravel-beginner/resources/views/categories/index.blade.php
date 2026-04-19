@extends('layouts.app', ['title' => 'Категории'])

@section('content')
<section class="page-head">
    <h1>Категории</h1>
    <p class="muted">Здесь можно добавить, изменить и удалить категории.</p>
</section>

<section class="two-columns">
    <article class="card">
        <h2>Добавить категорию</h2>

        <form action="{{ route('categories.store') }}" method="post" class="stack">
            @csrf

            <div>
                <label for="external_id">Внешний ID</label>
                <input id="external_id" name="external_id" type="text" placeholder="Например: C11" required>
            </div>

            <div>
                <label for="name">Название</label>
                <input id="name" name="name" type="text" placeholder="Например: Books" required>
            </div>

            <button type="submit" class="button">Добавить</button>
        </form>
    </article>

    <article class="card">
        <h2>Список категорий</h2>

        <div class="table-wrap">
            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Внешний ID</th>
                        <th>Название</th>
                        <th>Published adverts</th>
                        <th>Действия</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                        <tr>
                            <td>{{ $category->id }}</td>
                            <td>{{ $category->external_id }}</td>
                            <td>{{ $category->name }}</td>
                            <td>{{ $category->published_adverts_count }}</td>
                            <td>
                                <details>
                                    <summary>Изменить</summary>

                                    <form action="{{ route('categories.update', $category) }}" method="post" class="stack inner-form">
                                        @csrf
                                        <div>
                                            <label>Внешний ID</label>
                                            <input type="text" name="external_id" value="{{ $category->external_id }}" required>
                                        </div>
                                        <div>
                                            <label>Название</label>
                                            <input type="text" name="name" value="{{ $category->name }}" required>
                                        </div>
                                        <button type="submit" class="button">Сохранить</button>
                                    </form>
                                </details>

                                <form action="{{ route('categories.destroy', $category) }}" method="post" class="inline-form">
                                    @csrf
                                    <button type="submit" class="button button-danger">Удалить</button>
                                </form>

                                @if($category->all_adverts_count > 0)
                                    <p class="small-note">Удаление будет запрещено: есть связанные объявления.</p>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5">Категорий пока нет.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </article>
</section>
@endsection
