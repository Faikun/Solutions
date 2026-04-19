<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = [
        'external_id',
        'name',
    ];

    public function adverts()
    {
        return $this->hasMany(Advert::class);
    }
}
