<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /*
    |--------------------------------------------------------------------------
    | Создание таблицы users
    |--------------------------------------------------------------------------
    |
    | Здесь хранятся пользователи API.
    | В Module C нужны роли user и moderator.
    |
    */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            // Главный первичный ключ.
            $table->id();

            // Имя по заданию необязательное.
            $table->string('name', 100)->nullable();

            // Телефон должен быть уникальным.
            $table->string('phone')->unique();

            // Email тоже должен быть уникальным.
            $table->string('email')->unique();

            // Сохраняем только хеш пароля.
            $table->string('password');

            // Роль пользователя: обычный user или moderator.
            $table->enum('role', ['user', 'moderator'])->default('user');

            // Простой токен для Bearer auth.
            $table->string('api_token', 80)->nullable()->unique();

            // created_at и updated_at.
            $table->timestamps();
        });
    }

    // Откат миграции: удалить таблицу users.
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
