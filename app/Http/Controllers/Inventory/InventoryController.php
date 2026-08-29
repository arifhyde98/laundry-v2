<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\ChemicalRecipe;
use Illuminate\Http\Request;
use Inertia\Inertia;

class InventoryController extends Controller
{
    public function index(Request $request)
    {
        $items = InventoryItem::with('recipe')->orderBy('name')->get();

        return Inertia::render('Inventory/Index', [
            'items' => $items,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:chemical,packaging,equipment,other'],
            'stock' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'dosage_per_kg' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item = InventoryItem::create([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'],
            'cost_price' => $validated['cost_price'] ?? 0,
        ]);

        if (!empty($validated['dosage_per_kg']) && $validated['dosage_per_kg'] > 0) {
            ChemicalRecipe::create([
                'inventory_item_id' => $item->id,
                'dosage_per_kg' => $validated['dosage_per_kg'],
            ]);
        }

        return back()->with('success', "Item inventaris {$item->name} berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'category' => ['required', 'in:chemical,packaging,equipment,other'],
            'stock' => ['required', 'numeric', 'min:0'],
            'unit' => ['required', 'string', 'max:20'],
            'minimum_stock' => ['required', 'numeric', 'min:0'],
            'cost_price' => ['nullable', 'numeric', 'min:0'],
            'dosage_per_kg' => ['nullable', 'numeric', 'min:0'],
        ]);

        $item->update([
            'name' => $validated['name'],
            'category' => $validated['category'],
            'stock' => $validated['stock'],
            'unit' => $validated['unit'],
            'minimum_stock' => $validated['minimum_stock'],
            'cost_price' => $validated['cost_price'] ?? 0,
        ]);

        if (isset($validated['dosage_per_kg'])) {
            if ($validated['dosage_per_kg'] > 0) {
                ChemicalRecipe::updateOrCreate(
                    ['inventory_item_id' => $item->id],
                    ['dosage_per_kg' => $validated['dosage_per_kg']]
                );
            } else {
                ChemicalRecipe::where('inventory_item_id', $item->id)->delete();
            }
        }

        return back()->with('success', "Item inventaris {$item->name} berhasil diperbarui.");
    }

    public function adjustStock(Request $request, $id)
    {
        $item = InventoryItem::findOrFail($id);

        $validated = $request->validate([
            'quantity' => ['required', 'numeric'], // can be positive (tambah) or negative (buang/kurang)
            'notes' => ['nullable', 'string'],
        ]);

        $newStock = max(0, (float)$item->stock + (float)$validated['quantity']);
        $item->update(['stock' => $newStock]);

        return back()->with('success', "Stok {$item->name} disesuaikan menjadi {$newStock} {$item->unit}.");
    }

    public function destroy($id)
    {
        $item = InventoryItem::findOrFail($id);
        $item->delete();

        return back()->with('success', 'Item inventaris berhasil dihapus.');
    }
}

