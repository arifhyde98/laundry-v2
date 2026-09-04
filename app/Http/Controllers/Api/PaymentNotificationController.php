<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PaymentGatewayService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PaymentNotificationController extends Controller
{
    /**
     * Generate Snap Token untuk transaksi order
     */
    public function generateSnapToken(Request $request, PaymentGatewayService $service)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'amount' => 'nullable|numeric|min:1',
        ]);

        try {
            $order = Order::findOrFail($validated['order_id']);
            $remaining = max(0, (float) $order->grand_total - (float) $order->paid_amount);

            if ($remaining <= 0) {
                return response()->json([
                    'status' => 'error',
                    'message' => 'Tagihan order ini sudah lunas.',
                ], 422);
            }

            // Jika publik/tidak login, kunci pembayaran sebesar sisa tagihan penuh agar tidak bisa dimanipulasi
            // Jika kasir login, nominal dibatasi maksimal sebesar sisa tagihan yang ada
            if (! auth()->check()) {
                $amount = $remaining;
            } else {
                $amount = isset($validated['amount']) ? min($remaining, (float) $validated['amount']) : $remaining;
            }

            $snapData = $service->createSnapToken($order, $amount);

            return response()->json([
                'status' => 'success',
                'data' => $snapData,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * Handle Webhook Notifikasi Callback dari Midtrans
     */
    public function handleMidtransWebhook(Request $request, PaymentGatewayService $service, WhatsAppService $whatsAppService)
    {
        $payload = $request->all();
        Log::info('Midtrans Webhook Received: ', $payload);

        try {
            $processed = $service->processWebhook($payload, $whatsAppService);

            if ($processed) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'Notification processed successfully',
                ], 200);
            }

            return response()->json([
                'status' => 'error',
                'message' => 'Notification invalid or ignored',
            ], 400);
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Exception: '.$e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
