<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    // Поля, которые можно массово записывать.
    protected $fillable = ['name'];

    // У категории много объявлений.
    public function adverts()
    {
        return $this->hasMany(Advert::class);
    }
}
