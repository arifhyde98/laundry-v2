<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmployeeCommission extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'quantity' => 'decimal:2',
        'rate_per_unit' => 'decimal:2',
        'commission_amount' => 'decimal:2',
        'is_paid' => 'boolean',
        'paid_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}

