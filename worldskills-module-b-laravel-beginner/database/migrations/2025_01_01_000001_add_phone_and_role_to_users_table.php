<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        /*
        |--------------------------------------------------------------------------
        | Почему мы НЕ создаём таблицу users здесь
        |--------------------------------------------------------------------------
        |
        | В чистом Laravel таблица users уже создаётся стандартной миграцией:
        | 0001_01_01_000000_create_users_table.php
        |
        | Поэтому для Module B мы только добавляем два нужных поля:
        | - phone: чтобы входить по телефону и показывать телефон автора
        | - role: чтобы пускать в админку только moderator
        |
        */
        Schema::table('users', function (Blueprint $table) {
            $table->string('phone')->unique()->after('name');
            $table->string('role')->default('user')->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['phone', 'role']);
        });
    }
};
