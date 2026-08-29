<?php

namespace App\Services;

use App\Models\Order;
use App\Models\ChemicalRecipe;
use Illuminate\Support\Facades\Log;

class ChemicalService
{
    /**
     * Auto deduct chemical inventory based on order weight
     */
    public function deductStockForOrder(Order $order): void
    {
        $weight = (float)$order->total_weight_qty;
        if ($weight <= 0) return;

        $recipes = ChemicalRecipe::with('item')->get();

        foreach ($recipes as $recipe) {
            $item = $recipe->item;
            if (!$item) continue;

            $deduction = (float)$recipe->dosage_per_kg * $weight;
            $newStock = max(0, (float)$item->stock - $deduction);
            $item->update(['stock' => $newStock]);

            Log::info("Chemical Stock Auto-Deducted for Order {$order->invoice_code}: Item {$item->name} deducted {$deduction} {$item->unit}, new stock: {$newStock} {$item->unit}");
        }
    }
}

