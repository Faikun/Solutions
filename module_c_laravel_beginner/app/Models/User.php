<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    // Поля, которые можно массово заполнять через create() / update().
    protected $fillable = [
        'name',
        'phone',
        'email',
        'password',
        'role',
        'api_token',
    ];

    // Эти поля не должны уходить наружу при сериализации модели в JSON.
    protected $hidden = [
        'password',
        'api_token',
    ];

    // У пользователя может быть много объявлений.
    public function adverts()
    {
        return $this->hasMany(Advert::class);
    }

    // У пользователя может быть много просмотров.
    public function views()
    {
        return $this->hasMany(View::class);
    }

    // Удобный helper: проверить, модератор ли это.
    public function isModerator(): bool
    {
        return $this->role === 'moderator';
    }
}
