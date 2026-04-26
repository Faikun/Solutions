<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advert_services', function (Blueprint $table) {
            // Первичный ключ.
            $table->id();

            // Какому объявлению принадлежит услуга.
            $table->foreignId('advert_id')->constrained()->cascadeOnDelete();

            // Тип услуги: VIP или TOP.
            $table->enum('type', ['vip', 'top']);

            // Когда подключили услугу.
            $table->timestamp('activated_at');

            // Когда услуга закончится.
            $table->timestamp('expires_at');

            // created_at и updated_at.
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advert_services');
    }
};
