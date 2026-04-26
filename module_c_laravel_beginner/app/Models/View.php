<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class View extends Model
{
    // Поля для массового заполнения.
    protected $fillable = ['advert_id', 'user_id'];

    // Просмотр относится к одному объявлению.
    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }

    // Просмотр относится к одному пользователю.
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
