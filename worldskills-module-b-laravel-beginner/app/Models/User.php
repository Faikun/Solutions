<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    /*
    |--------------------------------------------------------------------------
    | Модель пользователя
    |--------------------------------------------------------------------------
    |
    | Эта модель хранит авторов объявлений и модераторов.
    | Мы используем обычный Eloquent Model, без усложнений.
    |
    */

    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
    ];

    public function adverts()
    {
        return $this->hasMany(Advert::class);
    }
}
