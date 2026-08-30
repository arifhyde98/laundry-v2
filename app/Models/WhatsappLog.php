<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class WhatsappLog extends Model
{
    protected $fillable = [
        'order_id',
        'target_phone',
        'message_type',
        'status',
        'response_payload'
    ];

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
