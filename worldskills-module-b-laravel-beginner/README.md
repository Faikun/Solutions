# Module B — Laravel beginner version

Это **максимально упрощённая учебная версия** под стек **PHP + Laravel 12.x**.  
Она специально написана так, чтобы начинающий разработчик понял структуру проекта:

- больше **Blade + HTML-форм**
- меньше JavaScript
- простые контроллеры
- понятные маршруты
- подробные комментарии в коде

Технологии и допустимый стек опираются на список инфраструктуры, где разрешён **Laravel v12.x**. citeturn326290view0

## Что реализовано по Module B

По заданию должны быть:
- вход только для пользователей с ролью `moderator`
- header со ссылками: Home, Categories, Adverts, Users
- Home: имя текущего пользователя, количество объявлений по статусам, количество пользователей, топ-10 published по просмотрам
- Categories: список категорий и интерфейс добавления / редактирования / удаления
- нельзя удалить категорию, если к ней привязаны объявления в любом статусе
- Users: список пользователей и одно поле поиска по ID, телефону и email
- Adverts: список с фильтрами по статусу, категории и тексту
- поиск по тексту идёт по заголовку, тексту, названию категории и имени автора
- в списке объявлений есть пометки о платных услугах
- Advert details: полные данные, фото, автор, смена статуса, управление платными услугами
- экспорт списка в `export_adverts.csv`
- в проекте есть `database/DB_DUMP.sql` и `database/ERD.png`

Все эти пункты присутствуют в данной версии. Основание — `Module B.pdf`. fileciteturn5file0

## Как устроен проект

### Основные папки
- `routes/web.php` — все веб-маршруты
- `app/Http/Controllers` — контроллеры
- `app/Models` — модели
- `app/Http/Middleware/ModeratorOnly.php` — проверка роли модератора
- `resources/views` — Blade-шаблоны
- `public/css/style.css` — стили
- `database/migrations` — структура таблиц
- `database/seeders/CsvImportSeeder.php` — импорт CSV в БД
- `database/source-data` — исходные CSV
- `public/media-files/images` — картинки объявлений
- `database/DB_DUMP.sql` — пример SQL-дампа
- `database/ERD.png` — простая ER-диаграмма

## Быстрый запуск

### 1. Создать новый Laravel 12 проект
Можно взять этот код как основу и перенести его в чистый проект Laravel:

```bash
composer create-project laravel/laravel geek-bazaar-admin
```

### 2. Скопировать файлы
Скопируйте в новый проект:
- `routes/web.php`
- `app/Http/Controllers/*`
- `app/Http/Middleware/ModeratorOnly.php`
- `app/Models/*`
- `resources/views/*`
- `public/css/style.css`
- `public/media-files/images/*`
- `database/migrations/*`
- `database/seeders/CsvImportSeeder.php`
- `database/source-data/*`

### 3. Настроить `.env`
Укажите параметры своей MySQL базы.
Выполни composer update в корне проекта для скачивания всех зависимостей

### 4. Выполнить миграции и импорт
```bash
php artisan migrate
php artisan db:seed --class=CsvImportSeeder
```

### 5. Зарегистрировать middleware
В `bootstrap/app.php` добавьте middleware-алиас:

```php
->withMiddleware(function ($middleware) {
    $middleware->alias([
        'moderator' => \App\Http\Middleware\ModeratorOnly::class,
    ]);
})
```

### 6. Запустить сервер
```bash
php artisan serve
```

## Тестовые модераторы

CSV пользователей содержит роль `moderator`.  
Seeder автоматически создаёт пароль из шаблона `<hash(name_123)>`, то есть реальный пароль будет таким текстом:

- `olivia@example.com` / `olivia_123`
- `ava@example.com` / `ava_123`
- `sophia@example.com` / `sophia_123`
- `emma@example.com` / `emma_123`
- `isabella@example.com` / `isabella_123`

Также можно входить по телефону из CSV. Данные взяты из `users.csv` внутри переданного media-архива. fileciteturn5file0

## Почему версия “для новичка”
- почти нет клиентского JavaScript
- все действия идут через формы `GET` и `POST`
- логика находится в контроллерах, а не размазана по фронтенду
- Blade-шаблоны максимально прямые
- в файлах много поясняющих комментариев

## Примечание по данным
Исходные CSV и изображения лежат в:
- `database/source-data/*.csv`
- `public/media-files/images/*`

Они взяты из присланного архива `media-files.zip`.

