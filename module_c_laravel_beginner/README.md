# WorldSkills Kazakhstan 2025 — Module C — Laravel REST API

Это учебная версия проекта для Module C.

Главная цель проекта — не показать «самую умную» архитектуру, а дать понятный шаблон, который можно:
- быстро развернуть в OSP 6;
- понять новичку;
- использовать как тренировочную основу перед соревнованием.

## Что реализовано

- регистрация пользователя;
- вход по email или телефону;
- Bearer token авторизация;
- просмотр и обновление профиля;
- список своих объявлений с фильтром по статусу;
- публичный список категорий;
- публичный список объявлений;
- публичные детали объявления;
- создание, редактирование и удаление объявлений;
- смена статусов по правилам задания;
- просмотр объявлений с записью в таблицу `views`;
- VIP и TOP услуги;
- swagger/openapi файл с описанием API;
- ERD и DB dump в папке `database`.

## Где читать подробную инструкцию

Открой файл:

```text
SETUP_FROM_ZERO_RU.md
```

В нём пошагово описано:
- как создать базу;
- как настроить `.env`;
- как выполнять миграции;
- как запускать сидеры;
- как проверять API;
- как писать такой модуль с нуля на соревновании.

## Быстрый старт

### 1. Установи зависимости

```bash
composer install
```

### 2. Создай `.env`

Для OSP 6 удобнее всего:

```bash
copy .env.osp.example .env
```

Потом открой `.env` и проверь параметры базы данных.

### 3. Сгенерируй ключ приложения

```bash
php artisan key:generate
```

### 4. Очисти кэш

```bash
php artisan optimize:clear
```

### 5. Создай таблицы и заполни тестовыми данными

```bash
php artisan migrate:fresh --seed
```

### 6. Запусти проект

Через OSP открывай домен сайта.

Если хочешь временно запустить встроенный сервер Laravel:

```bash
php artisan serve
```

## Тестовые учётные записи

### User
- email: `ethan@ws-s17.kz`
- phone: `+12025550143`
- password: `ethan_123`

### Moderator
- email: `olivia@ws-s17.kz`
- phone: `+447700900321`
- password: `olivia_123`

## Как тестировать API

### Получить категории

```http
GET /api/categories
```

### Зарегистрироваться

```http
POST /api/auth/register
Content-Type: application/json

{
  "name": "Test User",
  "phone": "+77001234567",
  "email": "test@example.com",
  "password": "password123"
}
```

### Войти

```http
POST /api/auth/login
Content-Type: application/json

{
  "login": "ethan@ws-s17.kz",
  "password": "ethan_123"
}
```

Ответ вернёт `access_token`.

### Использовать токен

Передавай заголовок:

```http
Authorization: Bearer TOKEN
```

## Структура проекта

```text
app/
  Http/
    Controllers/Api/
    Middleware/
  Models/
database/
  migrations/
  seeders/
  swagger/
routes/
  api.php
```

## Важная идея учебной версии

В этом проекте не используется Sanctum или Passport.

Почему:
- для соревнования и тренировки новичку проще понять авторизацию через обычный токен в поле `api_token`;
- меньше файлов;
- проще вручную отлаживать Bearer token.

## Что смотреть первым делом

1. `routes/api.php`
2. `app/Http/Middleware/ApiTokenAuth.php`
3. `app/Http/Controllers/Api/AuthController.php`
4. `app/Http/Controllers/Api/UserController.php`
5. `app/Http/Controllers/Api/AdvertController.php`
6. `database/migrations/*`
7. `database/seeders/ModuleCSeeder.php`

## Если что-то не запускается

Сначала проверь:

```bash
php artisan --version
php artisan optimize:clear
php artisan migrate:status
```

Если `artisan` не запускается, открой `SETUP_FROM_ZERO_RU.md` и пройди шаги восстановления.
