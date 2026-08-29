<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RewashTicket extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'resolved_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function handler()
    {
        return $this->belongsTo(User::class, 'handled_by');
    }
}

