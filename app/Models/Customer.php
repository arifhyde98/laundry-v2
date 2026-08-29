<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Customer extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'deposit_balance' => 'decimal:2',
        'point_balance' => 'integer',
    ];

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deposits()
    {
        return $this->hasMany(CustomerDeposit::class);
    }
}

