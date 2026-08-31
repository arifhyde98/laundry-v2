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
            $amount = isset($validated['amount']) ? (float) $validated['amount'] : null;

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
            Log::error('Midtrans Webhook Exception: ' . $e->getMessage());

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage(),
            ], 500);
        }
    }
}
