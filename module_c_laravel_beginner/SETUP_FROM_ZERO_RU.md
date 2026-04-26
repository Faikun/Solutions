# Module C — полная инструкция от А до Я для новичка

## 0. Что ты вообще делаешь

Module C — это **REST API** для сервиса объявлений.

В этом модуле ты не делаешь HTML-страницы. Ты делаешь **API**, которое принимает HTTP-запросы и возвращает JSON.

Пример:

- клиент отправил `POST /api/auth/login`
- сервер проверил логин и пароль
- сервер вернул JSON с токеном

По заданию у тебя есть роли:
- guest — гость без токена;
- user — обычный пользователь;
- moderator — модератор.

В PDF сказано, что:
- база не даётся готовой;
- нужно самому создать структуру;
- нужно самому наполнить тестовыми данными;
- нужно сохранить `DB_DUMP.sql` и `ERD.png`;
- правила API описаны в `media-files/swagger/openapi.yaml`;
- нужно добавить две обязательные учётные записи Ethan Brooks и Olivia Carter. fileciteturn6file0

---

## 1. Что нужно установить

Минимум нужен такой набор:

- OSP 6;
- PHP 8.2+;
- MySQL;
- Composer;
- редактор кода (VS Code или PhpStorm).

Если проект лежит в OSP, то обычно папка выглядит так:

```text
C:\OSPanel\home\admin.local
```

или

```text
C:\OSPanel\home\geekbar-api.local
```

Важно: работать нужно **из корня Laravel-проекта**, там где лежит файл `artisan`.

---

## 2. Как выглядит правильный Laravel-проект

В корне должны быть:

```text
artisan
composer.json
app
database
routes
bootstrap
storage
vendor
.env
```

Если файла `artisan` нет — ты не в той папке.

Проверка:

```bash
dir artisan
```

---

## 3. Как создать базу данных

Открой phpMyAdmin через OSP.

Создай базу, например:

```text
geek_bazaar_api
```

Кодировка:

```text
utf8mb4_unicode_ci
```

Таблицы руками не создавай.
Таблицы должен создать Laravel через миграции.

---

## 4. Как настроить `.env`

Если у тебя OSP, удобно начать с файла:

```text
.env.osp.example
```

Скопируй его:

```bash
copy .env.osp.example .env
```

Потом открой `.env` и проверь:

```env
APP_NAME="Geek Bazaar API"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://admin.local

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=geek_bazaar_api
DB_USERNAME=root
DB_PASSWORD=
```

Если в OSP база доступна не по `127.0.0.1`, а по имени модуля, поставь хост, который реально работает у тебя.

После изменения `.env` всегда выполняй:

```bash
php artisan optimize:clear
```

---

## 5. Что такое миграции

### Простыми словами

Миграция — это PHP-файл, который говорит Laravel:

> Создай таблицу.
> Добавь поля.
> Создай связи.

Миграции лежат в:

```text
database/migrations
```

### Команда для создания миграции

```bash
php artisan make:migration create_categories_table
```

### Команда для запуска миграций

```bash
php artisan migrate
```

### Команда для полного пересоздания базы

```bash
php artisan migrate:fresh
```

Если ты тренируешься, чаще всего удобнее:

```bash
php artisan migrate:fresh --seed
```

---

## 6. Какие таблицы нужны в Module C

Минимально:

### users
Нужны поля:
- id
- name
- phone
- email
- password
- role
- api_token
- created_at
- updated_at

### categories
Нужны поля:
- id
- name
- created_at
- updated_at

### adverts
Нужны поля:
- id
- status
- title
- text
- price
- category_id
- user_id
- photos
- created_at
- updated_at

### views
Нужны поля:
- id
- advert_id
- user_id
- created_at
- updated_at

### advert_services
Нужны поля:
- id
- advert_id
- type
- activated_at
- expires_at
- created_at
- updated_at

---

## 7. Порядок создания таблиц

Очень важно создавать таблицы в правильном порядке.

Сначала:
1. `users`
2. `categories`
3. `adverts`
4. `views`
5. `advert_services`

Почему:
- `adverts` зависит от `users` и `categories`;
- `views` зависит от `adverts` и `users`;
- `advert_services` зависит от `adverts`.

---

## 8. Как выполнять миграции

### Шаг 1
Проверь статус:

```bash
php artisan migrate:status
```

### Шаг 2
Если всё с нуля:

```bash
php artisan migrate:fresh
```

### Шаг 3
Если нужно сразу и данные:

```bash
php artisan migrate:fresh --seed
```

---

## 9. Что такое сидер

Seeder — это PHP-файл, который **заполняет таблицы тестовыми данными**.

В этом проекте за это отвечает:

```text
database/seeders/ModuleCSeeder.php
```

Он создаёт:
- обязательного пользователя Ethan;
- обязательного модератора Olivia;
- категории;
- объявления;
- VIP/TOP услуги.

Запуск вручную:

```bash
php artisan db:seed --class=ModuleCSeeder
```

Но обычно удобнее:

```bash
php artisan migrate:fresh --seed
```

Потому что `DatabaseSeeder` уже вызывает `ModuleCSeeder`.

---

## 10. Что такое модели

Модель — это PHP-класс, который работает с таблицей.

Например:

- `App\Models\User` работает с таблицей `users`;
- `App\Models\Advert` работает с таблицей `adverts`.

Модели лежат в:

```text
app/Models
```

В моделях описываются:
- какие поля можно заполнять;
- какие есть связи между таблицами.

Пример связи:

- одно объявление принадлежит одной категории;
- одна категория имеет много объявлений.

---

## 11. Что такое маршруты

Маршрут — это правило:

> Если пришёл такой URL и такой HTTP-метод, вызвать такой-то метод контроллера.

Маршруты API лежат в:

```text
routes/api.php
```

Например:

```php
Route::get('/categories', [CategoryController::class, 'index']);
```

Это значит:

- запрос `GET /api/categories`
- пойдёт в `CategoryController@index`

---

## 12. Что такое контроллер

Контроллер — это место, где находится логика обработки запроса.

Пример:
- пришёл запрос на логин;
- контроллер проверяет данные;
- находит пользователя;
- создаёт токен;
- возвращает JSON.

Контроллеры лежат в:

```text
app/Http/Controllers/Api
```

---

## 13. Как работает токен в этом проекте

В учебной версии мы не используем Sanctum.

Схема простая:
1. пользователь логинится;
2. мы создаём случайную строку длиной 64 символа;
3. сохраняем её в `users.api_token`;
4. клиент отправляет заголовок:

```http
Authorization: Bearer TOKEN
```

5. middleware ищет пользователя по `api_token`;
6. если пользователь найден — запрос считается авторизованным.

Это сделано специально, чтобы новичку было легче понять логику.

---

## 14. Что делает middleware

Middleware — это фильтр перед запросом.

В проекте есть:

```text
app/Http/Middleware/ApiTokenAuth.php
```

Он проверяет:
- есть ли заголовок Bearer token;
- существует ли пользователь с таким токеном.

Если нет — возвращает 401.

---

## 15. Что делать после распаковки проекта

Пошагово:

### 1. Перейти в корень проекта

```bash
cd C:\OSPanel\home\admin.local
```

### 2. Установить зависимости

```bash
composer install
```

### 3. Создать `.env`

```bash
copy .env.osp.example .env
```

### 4. Сгенерировать ключ

```bash
php artisan key:generate
```

### 5. Очистить кэш

```bash
php artisan optimize:clear
```

### 6. Пересоздать таблицы и данные

```bash
php artisan migrate:fresh --seed
```

### 7. Запустить проект

```bash
php artisan serve
```

Или открыть домен OSP.

---

## 16. Как проверить, что всё работает

### Проверить Laravel

```bash
php artisan --version
```

### Проверить миграции

```bash
php artisan migrate:status
```

### Проверить категории

Открой:

```text
http://127.0.0.1:8000/api/categories
```

Должен прийти JSON-массив категорий.

### Проверить логин

Отправь POST:

```json
{
  "login": "ethan@ws-s17.kz",
  "password": "ethan_123"
}
```

---

## 17. Что делать, если `artisan` не запускается

Проверь:

### 1. Ты точно в корне проекта?

Должен быть файл:

```text
artisan
```

### 2. Есть ли папка `vendor`?

Проверь:

```bash
dir vendor
```

Если нет — выполни:

```bash
composer install
```

### 3. Не сломан ли файл `artisan`?

В нём должен быть стандартный код Laravel.

### 4. Очисти кэш bootstrap

Если есть проблемы с автозагрузкой сервисов, иногда нужно удалить старые файлы из:

```text
bootstrap/cache
```

И потом снова выполнить:

```bash
php artisan optimize:clear
```

---

## 18. Как писать такой модуль с нуля на соревновании

Вот правильный порядок.

### Этап 1. Сначала читаешь задание

Смотри:
- роли;
- таблицы;
- allowed transitions статусов;
- фильтры;
- Advantage Services;
- требования к токену;
- обязательные аккаунты;
- README, DB dump, ERD, swagger.

### Этап 2. Сразу рисуешь ERD на бумаге

Нарисуй таблицы и связи.

### Этап 3. Создаёшь Laravel-проект

### Этап 4. Настраиваешь БД и `.env`

### Этап 5. Пишешь миграции

### Этап 6. Пишешь модели и связи

### Этап 7. Пишешь сидер с тестовыми данными

### Этап 8. Пишешь auth + middleware

### Этап 9. Пишешь categories

### Этап 10. Пишешь user profile + user adverts

### Этап 11. Пишешь adverts list + advert details

### Этап 12. Пишешь create/update/delete/status

### Этап 13. Пишешь VIP/TOP логику

### Этап 14. Обновляешь swagger

### Этап 15. Сохраняешь DB_DUMP.sql и ERD.png

---

## 19. Как не запутаться в коде

Всегда двигайся так:

1. маршрут;
2. контроллер;
3. модель;
4. миграция;
5. сидер.

То есть не прыгай по всему проекту случайно.

---

## 20. Что обязательно сдать

По Module C нужно положить в проект:

- `README.md`;
- `database/DB_DUMP.sql`;
- `database/ERD.png`;
- обновлённый `swagger/openapi.yaml`.

Это прямо указано в задании. fileciteturn6file0

---

## 21. Короткий набор команд, который нужно запомнить

```bash
composer install
copy .env.osp.example .env
php artisan key:generate
php artisan optimize:clear
php artisan migrate:fresh --seed
php artisan serve
```

И вспомогательные:

```bash
php artisan migrate:status
php artisan db:seed
php artisan make:migration create_categories_table
php artisan make:model Advert
```

---

## 22. Главное правило

Не начинай с «красоты».

Сначала:
- база;
- миграции;
- сидер;
- маршруты;
- контроллеры;
- только потом мелкая шлифовка.

Так ты не развалишь модуль по времени.
