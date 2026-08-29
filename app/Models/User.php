<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function shifts()
    {
        return $this->hasMany(Shift::class);
    }

    public function commissions()
    {
        return $this->hasMany(EmployeeCommission::class);
    }

    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }
}
