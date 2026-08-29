<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CustomerDeposit extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'amount' => 'decimal:2',
        'balance_after' => 'decimal:2',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}

