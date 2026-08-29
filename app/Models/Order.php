<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'total_weight_qty' => 'decimal:2',
        'subtotal_amount' => 'decimal:2',
        'discount_amount' => 'decimal:2',
        'delivery_fee' => 'decimal:2',
        'grand_total' => 'decimal:2',
        'paid_amount' => 'decimal:2',
        'order_date' => 'date',
        'estimated_completion' => 'datetime',
        'actual_completion' => 'datetime',
    ];

    public function customer()
    {
        return $this->belongsTo(Customer::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function outlet()
    {
        return $this->belongsTo(Outlet::class);
    }

    public function rack()
    {
        return $this->belongsTo(Rack::class);
    }

    public function shift()
    {
        return $this->belongsTo(Shift::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payments()
    {
        return $this->hasMany(OrderPayment::class);
    }

    public function trackingLogs()
    {
        return $this->hasMany(OrderTrackingLog::class)->orderBy('created_at', 'desc');
    }

    public function commissions()
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function rewashTickets()
    {
        return $this->hasMany(RewashTicket::class);
    }

    public function isPaid(): bool
    {
        return $this->payment_status === 'paid';
    }

    public function getRemainingAmountAttribute(): float
    {
        return max(0, (float)$this->grand_total - (float)$this->paid_amount);
    }
}

