<?php

namespace App\Http\Controllers;

use App\Models\PaymentGateway;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        // Seed initial gateways if empty
        if (PaymentGateway::count() === 0) {
            PaymentGateway::insert([
                [
                    'name' => 'midtrans',
                    'display_name' => 'Midtrans Payment Gateway',
                    'is_active' => false,
                    'mode' => 'sandbox',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'name' => 'xendit',
                    'display_name' => 'Xendit Payment Gateway',
                    'is_active' => false,
                    'mode' => 'sandbox',
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }

        $gateways = PaymentGateway::all()->makeVisible(['server_key']);

        return Inertia::render('PaymentGateways/Index', [
            'gateways' => $gateways,
        ]);
    }

    public function update(Request $request, $id)
    {
        $gateway = PaymentGateway::findOrFail($id);

        $validated = $request->validate([
            'is_active' => 'boolean',
            'mode' => 'in:sandbox,production',
            'server_key' => 'nullable|string',
            'client_key' => 'nullable|string',
            'merchant_id' => 'nullable|string',
        ]);

        // If activating this one, we might want to deactivate others
        if ($validated['is_active'] ?? false) {
            PaymentGateway::where('id', '!=', $id)->update(['is_active' => false]);
        }

        $gateway->update($validated);

        return back()->with('success', 'Konfigurasi Payment Gateway berhasil diperbarui.');
    }
}
