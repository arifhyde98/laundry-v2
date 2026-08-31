<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    /**
     * Send a WhatsApp message using Fonnte API and log it.
     */
    public static function send(string $target, string $message, string $token, ?int $orderId = null, string $messageType = 'general'): bool
    {
        if (empty($token) || empty($target)) {
            return false;
        }

        try {
            $response = Http::timeout(10)->withHeaders([
                'Authorization' => $token,
            ])->post('https://api.fonnte.com/send', [
                'target' => $target,
                'message' => $message,
                'countryCode' => '62',
            ]);

            $result = $response->json();
            $isSuccess = isset($result['status']) && $result['status'] === true;

            \App\Models\WhatsappLog::create([
                'order_id' => $orderId,
                'target_phone' => $target,
                'message_type' => $messageType,
                'status' => $isSuccess ? 'success' : 'failed',
                'response_payload' => json_encode($result),
            ]);

            if ($isSuccess) {
                Log::info("WhatsApp Notification ({$messageType}) sent successfully to {$target}.");
                return true;
            }

            Log::error("Failed to send WhatsApp ({$messageType}) to {$target}: " . json_encode($result));
            return false;

        } catch (\Exception $e) {
            \App\Models\WhatsappLog::create([
                'order_id' => $orderId,
                'target_phone' => $target,
                'message_type' => $messageType,
                'status' => 'failed',
                'response_payload' => json_encode(['error' => $e->getMessage()]),
            ]);
            
            Log::error("WhatsAppService Exception: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Helper to send Order Ready notification
     */
    public function sendOrderReadyNotification(\App\Models\Order $order): bool
    {
        // Get the single outlet config
        $outlet = \App\Models\Outlet::first();
        
        if (!$outlet || !$outlet->is_wa_enabled || empty($outlet->wa_api_token)) {
            Log::info("WA is disabled or token missing.");
            return false;
        }

        $customer = $order->customer;
        if (!$customer || empty($customer->phone)) {
            Log::info("Customer phone is missing for order {$order->id}");
            return false;
        }

        // Compose message
        $amount = number_format($order->grand_total, 0, ',', '.');
        $statusText = $order->payment_status === 'paid' ? 'SUDAH LUNAS' : "BELUM LUNAS (Tagihan: Rp {$amount})";

        $message = "Halo Kak *{$customer->name}*,\n\n"
                 . "Cucian Anda dengan nomor resi *{$order->invoice_code}* sudah *SIAP DIAMBIL* di *{$outlet->name}*.\n\n"
                 . "Status Pembayaran: *{$statusText}*\n\n"
                 . "Terima kasih telah mempercayakan cucian Anda kepada kami!\n"
                 . "_Pesan otomatis dari {$outlet->name}_";

        return self::send($customer->phone, $message, $outlet->wa_api_token, $order->id, 'ready');
    }

    /**
     * Helper to send Order Received (Baru Masuk) notification
     */
    public function sendOrderReceivedNotification(\App\Models\Order $order): bool
    {
        // Get the single outlet config
        $outlet = \App\Models\Outlet::first();
        
        if (!$outlet || !$outlet->is_wa_enabled || empty($outlet->wa_api_token)) {
            Log::info("WA is disabled or token missing.");
            return false;
        }

        $customer = $order->customer;
        if (!$customer || empty($customer->phone)) {
            Log::info("Customer phone is missing for order {$order->id}");
            return false;
        }

        // Compose message
        $amount = number_format($order->grand_total, 0, ',', '.');
        $statusText = $order->payment_status === 'paid' ? 'SUDAH LUNAS' : "BELUM LUNAS (Sisa Tagihan: Rp " . number_format($order->grand_total - $order->paid_amount, 0, ',', '.') . ")";
        $qtyText = $order->total_weight_qty . " Kg/Pcs";

        $message = "Halo Kak *{$customer->name}*,\n\n"
                 . "Pesanan laundry Anda telah kami terima di *{$outlet->name}* dengan rincian berikut:\n\n"
                 . "🔹 No. Resi: *{$order->invoice_code}*\n"
                 . "🔹 Berat/Jml: *{$qtyText}*\n"
                 . "🔹 Total Biaya: *Rp {$amount}*\n"
                 . "🔹 Status: *{$statusText}*\n\n"
                 . "Kami akan segera memproses cucian Anda. Notifikasi selanjutnya akan dikirim saat cucian sudah siap diambil.\n\n"
                 . "Terima kasih!\n"
                 . "_Pesan otomatis dari {$outlet->name}_";

        return self::send($customer->phone, $message, $outlet->wa_api_token, $order->id, 'received');
    }

    /**
     * Helper to send Payment Received notification
     */
    public function sendPaymentReceivedNotification(\App\Models\OrderPayment $payment): bool
    {
        $outlet = \App\Models\Outlet::first();
        
        if (!$outlet || !$outlet->is_wa_enabled || empty($outlet->wa_api_token)) {
            return false;
        }

        $order = $payment->order;
        if (!$order) return false;

        $customer = $order->customer;
        if (!$customer || empty($customer->phone)) {
            return false;
        }

        $amountFormatted = number_format($payment->amount_paid, 0, ',', '.');
        $remainingFormatted = number_format(max(0, $order->grand_total - $order->paid_amount), 0, ',', '.');
        $statusText = $order->payment_status === 'paid' ? 'SUDAH LUNAS ✅' : "Sisa Tagihan: Rp {$remainingFormatted}";

        $message = "Halo Kak *{$customer->name}*,\n\n"
                 . "Pembayaran sebesar *Rp {$amountFormatted}* untuk resi *{$order->invoice_code}* telah *BERHASIL DITERIMA* via {$payment->payment_method}.\n\n"
                 . "🔹 Status Pembayaran: *{$statusText}*\n\n"
                 . "Terima kasih telah melakukan pembayaran!\n"
                 . "_Pesan otomatis dari {$outlet->name}_";

        return self::send($customer->phone, $message, $outlet->wa_api_token, $order->id, 'payment_received');
    }
}

