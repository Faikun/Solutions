<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertPhoto extends Model
{
    protected $fillable = [
        'advert_id',
        'file_name',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }
}
