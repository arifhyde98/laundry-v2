<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Shift extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'starting_cash' => 'decimal:2',
        'cash_income' => 'decimal:2',
        'non_cash_income' => 'decimal:2',
        'expected_cash' => 'decimal:2',
        'closing_cash' => 'decimal:2',
        'cash_difference' => 'decimal:2',
        'opened_at' => 'datetime',
        'closed_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }
}

