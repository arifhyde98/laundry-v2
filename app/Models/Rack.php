<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Rack extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'is_available' => 'boolean',
        'capacity' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class)->where('order_status', 'ready');
    }
}

