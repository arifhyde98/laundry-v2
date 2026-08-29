<?php

namespace App\Http\Controllers\Services;

use App\Http\Controllers\Controller;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ServiceController extends Controller
{
    public function index()
    {
        $services = Service::orderBy('is_active', 'desc')->orderBy('name')->get();

        return Inertia::render('Services/Index', [
            'services' => $services,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:kg,pcs,meter,pasang'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_hours' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
        ]);

        Service::create($validated);

        return back()->with('success', "Layanan {$validated['name']} berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $service = Service::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'unit' => ['required', 'in:kg,pcs,meter,pasang'],
            'price' => ['required', 'numeric', 'min:0'],
            'estimated_hours' => ['required', 'integer', 'min:1'],
            'description' => ['nullable', 'string'],
            'is_active' => ['required', 'boolean'],
        ]);

        $service->update($validated);

        return back()->with('success', "Layanan {$service->name} berhasil diperbarui.");
    }

    public function destroy($id)
    {
        $service = Service::findOrFail($id);
        $service->delete();

        return back()->with('success', 'Layanan berhasil dihapus.');
    }
}

