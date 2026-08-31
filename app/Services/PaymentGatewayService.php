<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderTrackingLog;
use App\Models\PaymentGateway;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;
use Midtrans\Config;
use Midtrans\Snap;
use Exception;

class PaymentGatewayService
{
    protected ?PaymentGateway $activeGateway = null;

    public function __construct()
    {
        $this->activeGateway = PaymentGateway::where('is_active', true)->first();
    }

    public function getActiveGateway(): ?PaymentGateway
    {
        return $this->activeGateway;
    }

    /**
     * Konfigurasi SDK Midtrans
     */
    protected function configureMidtrans(): void
    {
        if (!$this->activeGateway || $this->activeGateway->name !== 'midtrans') {
            throw new Exception('Payment Gateway Midtrans belum diaktifkan.');
        }

        if (empty($this->activeGateway->server_key)) {
            throw new Exception('Server Key Midtrans belum diisi di menu Pengaturan Payment Gateway.');
        }

        Config::$serverKey = $this->activeGateway->server_key;
        Config::$isProduction = $this->activeGateway->mode === 'production';
        Config::$isSanitized = true;
        Config::$is3ds = true;
    }

    /**
     * Membuat Snap Token Midtrans untuk Transaksi Order
     */
    public function createSnapToken(Order $order, ?float $amount = null): array
    {
        $this->configureMidtrans();

        $payAmount = $amount ?? ($order->grand_total - $order->paid_amount);
        if ($payAmount <= 0) {
            throw new Exception('Tagihan order ini sudah lunas.');
        }

        // Midtrans butuh ID transaksi yang unik setiap request.
        // Format: INV-20260831-0001-T1693456789
        $midtransTxId = $order->invoice_code . '-T' . time();

        $params = [
            'transaction_details' => [
                'order_id' => $midtransTxId,
                'gross_amount' => (int) round($payAmount),
            ],
            'customer_details' => [
                'first_name' => $order->customer?->name ?? 'Pelanggan',
                'phone' => $order->customer?->phone ?? '',
            ],
            'item_details' => [
                [
                    'id' => $order->invoice_code,
                    'price' => (int) round($payAmount),
                    'quantity' => 1,
                    'name' => "Pembayaran Laundry {$order->invoice_code}",
                ]
            ],
            'custom_field1' => (string) $order->id, // Menyimpan ID order database
        ];

        $snapToken = Snap::getSnapToken($params);

        return [
            'snap_token' => $snapToken,
            'client_key' => $this->activeGateway->client_key,
            'mode' => $this->activeGateway->mode,
            'amount' => $payAmount,
            'transaction_id' => $midtransTxId,
        ];
    }

    /**
     * Memproses callback/webhook dari Midtrans
     */
    public function processWebhook(array $payload, WhatsAppService $whatsAppService): bool
    {
        if (!$this->activeGateway || empty($this->activeGateway->server_key)) {
            Log::error('Webhook Payment Failed: Gateway not active or server key missing.');
            return false;
        }

        $orderIdStr = $payload['order_id'] ?? null;
        $statusCode = $payload['status_code'] ?? null;
        $grossAmount = $payload['gross_amount'] ?? null;
        $signatureKey = $payload['signature_key'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;
        $fraudStatus = $payload['fraud_status'] ?? null;
        $paymentType = $payload['payment_type'] ?? 'qris';

        if (!$orderIdStr || !$statusCode || !$grossAmount || !$signatureKey) {
            Log::error('Webhook Payment Invalid Payload: ' . json_encode($payload));
            return false;
        }

        // Verifikasi Signature HASH
        $serverKey = $this->activeGateway->server_key;
        $expectedSignature = hash("sha512", $orderIdStr . $statusCode . $grossAmount . $serverKey);

        if ($signatureKey !== $expectedSignature) {
            Log::error("Webhook Payment Signature Mismatch for Order: {$orderIdStr}");
            return false;
        }

        // Cari Order berdasarkan custom_field1 atau prefix invoice code
        $dbOrderId = $payload['custom_field1'] ?? null;
        $order = null;

        if ($dbOrderId) {
            $order = Order::find($dbOrderId);
        }

        if (!$order) {
            // Extrak invoice code misal INV-20260831-0001 dari INV-20260831-0001-T123456
            $parts = explode('-T', $orderIdStr);
            $invoiceCode = $parts[0];
            $order = Order::where('invoice_code', $invoiceCode)->first();
        }

        if (!$order) {
            Log::error("Webhook Payment Order Not Found: {$orderIdStr}");
            return false;
        }

        // Cek status pembayaran dari Midtrans
        $isPaidSuccess = false;

        if ($transactionStatus === 'capture') {
            if ($fraudStatus === 'accept') {
                $isPaidSuccess = true;
            }
        } elseif ($transactionStatus === 'settlement') {
            $isPaidSuccess = true;
        }

        if ($isPaidSuccess) {
            $paidFloat = (float) $grossAmount;
            $newTotalPaid = $order->paid_amount + $paidFloat;
            $newPaymentStatus = $newTotalPaid >= (float) $order->grand_total ? 'paid' : 'partial';

            // Hindari duplikasi jika callback dikirim ulang oleh Midtrans
            $existingPayment = OrderPayment::where('gateway_transaction_id', $orderIdStr)->first();

            if (!$existingPayment) {
                $paymentRecord = OrderPayment::create([
                    'order_id' => $order->id,
                    'shift_id' => null,
                    'received_by' => null, // Diproses otomatis oleh Midtrans Callback
                    'amount_paid' => $paidFloat,
                    'payment_method' => $paymentType === 'qris' ? 'qris' : 'transfer',
                    'payment_gateway_id' => $this->activeGateway->id,
                    'gateway_transaction_id' => $orderIdStr,
                    'payment_url' => null,
                    'payment_status' => 'success',
                    'gateway_response' => json_encode($payload),
                    'reference_no' => $payload['transaction_id'] ?? $orderIdStr,
                    'notes' => "Pembayaran Otomatis Midtrans ({$paymentType})",
                    'paid_at' => now(),
                ]);

                $order->update([
                    'paid_amount' => $newTotalPaid,
                    'payment_status' => $newPaymentStatus,
                ]);

                // Log progres
                OrderTrackingLog::create([
                    'order_id' => $order->id,
                    'changed_by' => null,
                    'status_to' => $order->order_status,
                    'notes' => "Pembayaran Online Midtrans berhasil diselesaikan (Rp " . number_format($paidFloat, 0, ',', '.') . ").",
                ]);

                // Kirim Notifikasi WA jika diaktifkan
                try {
                    $whatsAppService->sendPaymentReceivedNotification($paymentRecord);
                } catch (\Exception $waEx) {
                    Log::warning("WA Notification Callback Error: " . $waEx->getMessage());
                }
            }

            return true;
        }

        return true;
    }
}
