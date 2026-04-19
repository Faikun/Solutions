# Module B на Laravel + OSP 6 — инструкция от А до Я для новичка

Эта инструкция объясняет, как развернуть проект локально и как потом повторить такой проект с нуля на соревновании.

Проект сделан как учебная версия: Laravel + Blade + обычные HTML-формы + один CSS-файл. Клиентский JavaScript почти не нужен.

---

## 1. Что ты делаешь вообще

Module B — это не обычный лендинг. Это админ-панель модератора.

Нужно сделать:

1. вход в админку только для пользователей с ролью `moderator`;
2. главную страницу со статистикой;
3. страницу категорий;
4. страницу пользователей;
5. страницу объявлений с фильтрами;
6. страницу деталей объявления;
7. смену статуса объявления;
8. подключение и отключение платных услуг;
9. экспорт объявлений в CSV;
10. импорт исходных CSV в базу данных;
11. файл `database/DB_DUMP.sql`;
12. файл `database/ERD.png`.

Главная мысль: CSV — это только исходные данные. Работать сайт должен с MySQL-базой.

---

## 2. Где должен лежать проект в OSP 6

В OSP 6 сайты обычно лежат в папке:

```text
C:\OSPanel\home\
```

Для этого проекта сделай папку:

```text
C:\OSPanel\home\geekbar.local\
```

Именно в этой папке будет Laravel-проект.

Пример правильной структуры:

```text
C:\OSPanel\home\geekbar.local\
├── app\
├── bootstrap\
├── config\
├── database\
├── public\
├── resources\
├── routes\
├── storage\
├── vendor\
├── .env
├── artisan
└── composer.json
```

Важно: у Laravel входной файл находится в папке `public`:

```text
C:\OSPanel\home\geekbar.local\public\index.php
```

Если OSP открывает не `public`, а корень проекта, Laravel может работать неправильно.

---

## 3. Подготовка OSP 6

Открой OSP и включи модули:

1. PHP подходящей версии;
2. MySQL 8.4 или MariaDB;
3. веб-сервер.

В твоём логе уже было видно:

```text
Host: MySQL-8.4
Port: 3306
```

Значит для `.env` в OSP 6 часто удобно указывать именно:

```env
DB_HOST=MySQL-8.4
DB_PORT=3306
```

Если у другого участника модуль MySQL называется иначе, нужно указать то имя, которое показывает OSP.

---

## 4. Создание базы данных

Открой phpMyAdmin из OSP.

Обычно путь такой:

```text
OSP → Дополнительно → phpMyAdmin
```

Данные входа чаще всего:

```text
Пользователь: root
Пароль: пустой
```

Создай базу:

```text
geek_bazaar
```

Лучше выбрать кодировку:

```text
utf8mb4_unicode_ci
```

На этом этапе таблицы создавать руками не нужно. Таблицы создадут миграции Laravel.

---

## 5. Создание чистого Laravel-проекта

Открой терминал в папке:

```text
C:\OSPanel\home\
```

Создай проект:

```bash
composer create-project laravel/laravel geekbar.local
```

После этого перейди в папку проекта:

```bash
cd C:\OSPanel\home\geekbar.local
```

---

## 6. Куда копировать файлы из этого архива

Этот архив — учебная заготовка поверх чистого Laravel.

Скопируй папки и файлы из архива в Laravel-проект с заменой:

```text
app\Http\Controllers\
app\Http\Middleware\ModeratorOnly.php
app\Models\
bootstrap\app.php
routes\web.php
resources\views\
public\css\style.css
public\media-files\images\
database\migrations\
database\seeders\CsvImportSeeder.php
database\source-data\
database\DB_DUMP.sql
database\ERD.png
.env.osp.example
```

Если Windows спросит “заменить файлы?” — соглашайся для файлов проекта.

---

## 7. Настройка `.env`

В корне Laravel-проекта есть файл `.env`.

Открой его и выставь такие значения:

```env
APP_NAME="Geek Bazaar Admin"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://geekbar.local

DB_CONNECTION=mysql
DB_HOST=MySQL-8.4
DB_PORT=3306
DB_DATABASE=geek_bazaar
DB_USERNAME=root
DB_PASSWORD=

SESSION_DRIVER=file
CACHE_STORE=file
QUEUE_CONNECTION=sync
```

Почему `SESSION_DRIVER=file`?

Так новичку проще. Laravel хранит сессии в файлах и не требует таблицу `sessions`. Это уменьшает количество ошибок при первом запуске.

Если `DB_HOST=MySQL-8.4` не работает, попробуй по очереди:

```env
DB_HOST=127.0.0.1
```

потом:

```env
DB_HOST=localhost
```

Но если в ошибке Laravel уже пишет `Host: MySQL-8.4`, значит у тебя этот вариант подходит.

---

## 8. Очистка кэша Laravel после изменения `.env`

Каждый раз после изменения `.env` полезно выполнить:

```bash
php artisan optimize:clear
```

Это очищает кэш конфигурации, маршрутов и шаблонов.

---

## 9. Важное исправление по миграциям users

В чистом Laravel уже есть стандартная миграция:

```text
database\migrations\0001_01_01_000000_create_users_table.php
```

Она создаёт таблицу `users`.

Поэтому в этой версии проекта больше нет второй миграции `create_users_table`.

Вместо неё есть файл:

```text
database\migrations\2025_01_01_000001_add_phone_and_role_to_users_table.php
```

Он не создаёт `users` заново. Он только добавляет поля:

```text
phone
role
```

Это исправляет ошибку:

```text
Table 'users' already exists
```

---

## 10. Запуск миграций
Выполни composer update в корне проекта для скачивания всех зависимостей

Если база новая и пустая, выполни:

```bash
php artisan migrate
```

Если ты уже запускал миграции и получил ошибку, проще пересоздать таблицы:

```bash
php artisan migrate:fresh
```

Что делает `migrate:fresh`:

1. удаляет все таблицы из базы;
2. создаёт их заново по миграциям.

Используй `migrate:fresh` только если в базе нет важных данных.

---

## 11. Импорт CSV в базу

Исходные CSV лежат здесь:

```text
database\source-data\categories.csv
database\source-data\users.csv
database\source-data\adverts.csv
```

Импорт делает файл:

```text
database\seeders\CsvImportSeeder.php
```

Запусти импорт:

```bash
php artisan db:seed --class=CsvImportSeeder
```

Что делает seeder:

1. читает `categories.csv`;
2. создаёт категории;
3. читает `users.csv`;
4. создаёт пользователей;
5. достаёт пароль из записи вида `<hash(olivia_123)>`;
6. хеширует пароль через Laravel;
7. читает `adverts.csv`;
8. создаёт объявления;
9. добавляет фото объявлений;
10. добавляет платные услуги.

---

## 12. Полная команда для пересоздания базы и импорта

Когда тренируешься, удобно использовать одну команду:

```bash
php artisan migrate:fresh --seed --seeder=CsvImportSeeder
```

Она:

1. удалит старые таблицы;
2. создаст новые;
3. импортирует CSV.

---

## 13. Запуск сайта

Если используешь OSP-домен, открой в браузере:

```text
http://geekbar.local
```

Если запускаешь через artisan:

```bash
php artisan serve
```

И открой:

```text
http://127.0.0.1:8000
```

Для OSP обычно удобнее использовать домен `geekbar.local`.

---

## 14. Данные для входа

После импорта можно войти модератором.

Примеры:

```text
olivia@example.com
olivia_123
```

```text
ava@example.com
ava_123
```

```text
sophia@example.com
sophia_123
```

```text
emma@example.com
emma_123
```

Можно входить не только по email, но и по телефону из `users.csv`.

Обычный пользователь с ролью `user` войти не должен.

---

## 15. Что проверить после запуска

Проверь по порядку:

1. открывается `/login`;
2. неверные данные дают ошибку;
3. обычный `user` не входит;
4. `moderator` входит;
5. после входа открывается `/dashboard`;
6. в header есть ссылки Home, Categories, Adverts, Users;
7. Home показывает статистику;
8. Categories показывает список категорий;
9. категорию с объявлениями нельзя удалить;
10. Users показывает пользователей;
11. поиск Users работает по точному ID, телефону или email;
12. Adverts показывает объявления;
13. фильтр по статусу работает;
14. фильтр по категории работает;
15. текстовый поиск ищет по заголовку, тексту, категории и автору;
16. экспорт CSV скачивает `export_adverts.csv`;
17. страница объявления показывает фото плиткой;
18. статус меняется только по разрешённым переходам;
19. платные услуги можно включать и отключать.

---

## 16. Разбор основных папок

### `routes/web.php`

Это карта сайта.

Там указано:

```text
/login
/dashboard
/categories
/users
/adverts
/adverts/export
/adverts/{advert}
```

Новичку лучше начинать изучение именно с маршрутов.

---

### `app/Http/Controllers`

Здесь логика страниц.

```text
AuthController.php       вход и выход
DashboardController.php  главная статистика
CategoryController.php   категории
UserController.php       пользователи
AdvertController.php     объявления, детали, экспорт, услуги
```

---

### `app/Models`

Здесь описаны таблицы и связи.

```text
User.php
Category.php
Advert.php
AdvertPhoto.php
AdvertPaidService.php
```

---

### `resources/views`

Здесь HTML-шаблоны Blade.

```text
layouts/app.blade.php       общий шаблон
partials/flash.blade.php    сообщения об ошибках и успехе
auth/login.blade.php        форма входа
dashboard/index.blade.php   главная
categories/index.blade.php  категории
users/index.blade.php       пользователи
adverts/index.blade.php     список объявлений
adverts/show.blade.php      детали объявления
```

---

### `database/source-data`

Здесь исходные CSV. Их читает seeder и переносит в MySQL.

---

## 17. Как писать такой проект с нуля на соревновании

Порядок работы:

1. создать Laravel-проект;
2. создать БД;
3. настроить `.env`;
4. открыть CSV и понять колонки;
5. сделать миграции;
6. сделать модели и связи;
7. сделать seeder для CSV;
8. импортировать данные;
9. сделать login;
10. сделать middleware moderator;
11. сделать layout;
12. сделать Home;
13. сделать Categories;
14. сделать Users;
15. сделать Adverts;
16. сделать Advert Details;
17. сделать CSV export;
18. сохранить `DB_DUMP.sql`;
19. сохранить `ERD.png`;
20. проверить весь сайт.

Не начинай с дизайна. Сначала база и данные, потом страницы.

---

## 18. Примерный тайминг на 3 часа

```text
0:00–0:15   изучить PDF и CSV
0:15–0:40   БД, миграции, модели
0:40–0:55   импорт CSV
0:55–1:15   авторизация и middleware
1:15–1:30   layout и header
1:30–1:45   Home
1:45–2:05   Categories
2:05–2:20   Users
2:20–2:45   Adverts list и фильтры
2:45–3:05   Advert details
3:05–3:20   export CSV, DB_DUMP, ERD
последние минуты — проверка важнее красоты
```

На практике старайся закончить функциональность примерно за 2 часа 40 минут.

---

## 19. Как сделать DB_DUMP.sql

Через phpMyAdmin:

1. выбери базу `geek_bazaar`;
2. нажми “Экспорт”;
3. выбери SQL;
4. скачай файл;
5. положи его сюда:

```text
database\DB_DUMP.sql
```

Это нужно по заданию.

---

## 20. Как сделать ERD.png

Самый простой вариант:

1. открыть draw.io, diagrams.net или MySQL Workbench;
2. нарисовать таблицы:
   - users;
   - categories;
   - adverts;
   - advert_photos;
   - advert_paid_services;
3. показать связи:
   - users 1 → many adverts;
   - categories 1 → many adverts;
   - adverts 1 → many advert_photos;
   - adverts 1 → many advert_paid_services;
4. экспортировать как PNG;
5. сохранить сюда:

```text
database\ERD.png
```

---

## 21. Частые ошибки и решения

### Ошибка: `No connection could be made because the target machine actively refused it`

Причина: Laravel не может подключиться к MySQL.

Проверь:

1. запущен ли MySQL в OSP;
2. правильно ли указан `DB_HOST`;
3. правильно ли указан порт `3306`;
4. существует ли база `geek_bazaar`.

---

### Ошибка: `Table 'users' already exists`

Причина: две миграции пытаются создать таблицу `users`.

В этой обновлённой версии это исправлено.

Должно быть так:

1. стандартная миграция Laravel создаёт `users`;
2. наша миграция только добавляет `phone` и `role`.

---

### Ошибка: страница открывается без CSS

Проверь, что файл есть:

```text
public\css\style.css
```

И что в layout подключено:

```blade
<link rel="stylesheet" href="{{ asset('css/style.css') }}">
```

---

### Ошибка: фото не показываются

Проверь, что картинки лежат здесь:

```text
public\media-files\images\
```

А в Blade путь такой:

```blade
{{ asset('media-files/images/' . $photo->file_name) }}
```

---

### Ошибка: после изменения `.env` ничего не изменилось

Выполни:

```bash
php artisan optimize:clear
```

---

## 22. Самые важные команды

```bash
composer create-project laravel/laravel geekbar.local
```

```bash
php artisan optimize:clear
```

```bash
php artisan migrate
```

```bash
php artisan migrate:fresh
```

```bash
php artisan db:seed --class=CsvImportSeeder
```

```bash
php artisan migrate:fresh --seed --seeder=CsvImportSeeder
```

```bash
php artisan serve
```

---

## 23. Главное правило для соревнования

Сначала сделай работающую логику:

1. база;
2. импорт;
3. вход;
4. страницы;
5. фильтры;
6. экспорт.

Только потом улучшай внешний вид.
