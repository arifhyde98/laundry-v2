<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'price' => 'decimal:2',
        'is_active' => 'boolean',
        'estimated_hours' => 'integer',
    ];

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }
}

