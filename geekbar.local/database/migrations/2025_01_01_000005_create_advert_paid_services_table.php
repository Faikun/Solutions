<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('advert_paid_services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('advert_id')->constrained()->cascadeOnDelete();

            // Примеры: top, vip
            $table->string('service_type');

            $table->timestamp('connected_at')->nullable();
            $table->timestamp('expires_at')->nullable();

            // true -> подключена сейчас
            // false -> была подключена раньше, но уже отключена
            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('advert_paid_services');
    }
};
