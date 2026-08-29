<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChemicalRecipe extends Model
{
    protected $guarded = ['id'];

    protected $casts = [
        'dosage_per_kg' => 'decimal:2',
    ];

    public function item()
    {
        return $this->belongsTo(InventoryItem::class, 'inventory_item_id');
    }
}

