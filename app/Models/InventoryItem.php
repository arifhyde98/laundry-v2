<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InventoryItem extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'stock' => 'decimal:2',
        'minimum_stock' => 'decimal:2',
        'cost_price' => 'decimal:2',
    ];

    public function recipe()
    {
        return $this->hasOne(ChemicalRecipe::class);
    }
}

