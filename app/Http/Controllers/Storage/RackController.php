<?php

namespace App\Http\Controllers\Storage;

use App\Http\Controllers\Controller;
use App\Models\Rack;
use Illuminate\Http\Request;
use Inertia\Inertia;

class RackController extends Controller
{
    public function index()
    {
        $racks = Rack::with(['orders.customer'])
            ->orderBy('rack_code')
            ->get();

        return Inertia::render('Storage/Index', [
            'racks' => $racks,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'rack_code' => ['required', 'string', 'unique:racks,rack_code'],
            'category' => ['required', 'string'],
            'capacity' => ['required', 'integer', 'min:1'],
        ]);

        Rack::create($validated);

        return back()->with('success', "Slot rak {$validated['rack_code']} berhasil ditambahkan.");
    }

    public function destroy($id)
    {
        $rack = Rack::findOrFail($id);
        $rack->delete();

        return back()->with('success', 'Rak berhasil dihapus.');
    }
}

