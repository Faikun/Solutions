<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AdvertPaidService extends Model
{
    protected $fillable = [
        'advert_id',
        'service_type',
        'connected_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'connected_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    public function advert()
    {
        return $this->belongsTo(Advert::class);
    }
}
