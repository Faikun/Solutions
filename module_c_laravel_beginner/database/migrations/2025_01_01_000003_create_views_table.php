<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('views', function (Blueprint $table) {
            // Первичный ключ.
            $table->id();

            // Какое объявление посмотрели.
            $table->foreignId('advert_id')->constrained()->cascadeOnDelete();

            // Какой пользователь посмотрел.
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();

            // created_at и updated_at.
            $table->timestamps();

            // Одна уникальная запись на пару advert + user.
            $table->unique(['advert_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('views');
    }
};
