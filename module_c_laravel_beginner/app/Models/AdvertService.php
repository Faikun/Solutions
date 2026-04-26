<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertService extends Model
{
    // Поля, которые можно массово заполнять.
    protected $fillable = ['advert_id', 'type', 'activated_at', 'expires_at'];

    // Сразу приводим даты к Carbon-объектам.
    protected $casts = [
        'activated_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    // Услуга принадлежит одному объявлению.
    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }

    // Удобный helper для проверки активности услуги.
    public function isActive(): bool
    {
        return $this->expires_at && $this->expires_at->isFuture();
    }
}
