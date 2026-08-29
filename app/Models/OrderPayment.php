<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderPayment extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount_paid' => 'decimal:2',
        'paid_at' => 'datetime',
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }

    public function receiver()
    {
        return $this->belongsTo(User::class, 'received_by');
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }
}

