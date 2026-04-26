<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Advert extends Model
{
    // Список полей для create() и update().
    protected $fillable = [
        'status',
        'title',
        'text',
        'price',
        'category_id',
        'user_id',
        'photos',
    ];

    // Автоматические преобразования типов.
    protected $casts = [
        'photos' => 'array',
        'price' => 'integer',
    ];

    // Объявление принадлежит одной категории.
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Объявление принадлежит одному пользователю-автору.
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // У объявления может быть много просмотров.
    public function views()
    {
        return $this->hasMany(View::class);
    }

    // У объявления может быть много подключённых услуг.
    public function services()
    {
        return $this->hasMany(AdvertService::class);
    }

    // Активная VIP-услуга.
    public function activeVipService()
    {
        return $this->services()
            ->where('type', 'vip')
            ->where('expires_at', '>', now());
    }

    // Активная TOP-услуга.
    public function activeTopService()
    {
        return $this->services()
            ->where('type', 'top')
            ->where('expires_at', '>', now());
    }
}
