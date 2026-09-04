<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Outlet extends Model
{
    protected $guarded = ['id'];

    protected $hidden = [
        'wa_api_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_wa_enabled' => 'boolean',
        'commission_washing' => 'float',
        'commission_ironing' => 'float',
        'commission_packing' => 'float',
    ];

    public function racks()
    {
        return $this->hasMany(Rack::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function services()
    {
        return $this->hasMany(Service::class);
    }
}
