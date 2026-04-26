<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('adverts', function (Blueprint $table) {
            // Первичный ключ.
            $table->id();

            // Статусы объявления из задания.
            $table->enum('status', ['draft', 'moderation', 'declined', 'published', 'archived'])->default('draft');

            // Заголовок до 200 символов.
            $table->string('title', 200);

            // Текст объявления до 1000 символов можно держать в text.
            $table->text('text');

            // Цена.
            $table->integer('price');

            // Внешний ключ на categories.id
            $table->foreignId('category_id')->constrained()->cascadeOnDelete();

            // Внешний ключ на users.id
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // JSON-массив ссылок на фото.
            $table->json('photos');

            // created_at и updated_at.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('adverts');
    }
};
