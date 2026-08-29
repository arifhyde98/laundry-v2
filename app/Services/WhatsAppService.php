<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class WhatsAppService
{
    /**
     * Send Order Received Confirmation Message
     */
    public function sendOrderReceivedNotification(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) return false;

        $trackingUrl = url('/track/' . $order->invoice_code);
        $totalFormatted = 'Rp ' . number_format($order->grand_total, 0, ',', '.');
        $paymentStatusText = $order->payment_status === 'paid' ? 'LUNAS' : 'BELUM LUNAS';
        $estDate = $order->estimated_completion ? $order->estimated_completion->format('d M Y H:i') : '-';

        $message = "Halo Kak *{$customer->name}*,\n\n"
            . "Terima kasih telah mempercayakan cucian Anda di *Laundry Express*.\n"
            . "Pesanan Anda telah kami terima:\n\n"
            . "📄 *No. Invoice:* {$order->invoice_code}\n"
            . "⚖️ *Total Berat/Qty:* {$order->total_weight_qty} Kg/Pcs\n"
            . "💰 *Total Biaya:* {$totalFormatted} ({$paymentStatusText})\n"
            . "⏰ *Estimasi Selesai:* {$estDate}\n\n"
            . "🔎 Lacak progres cucian Anda secara *real-time* di sini:\n"
            . "{$trackingUrl}\n\n"
            . "_Pesan otomatis oleh Laundry Express POS._";

        return $this->sendMessage($customer->phone, $message);
    }

    /**
     * Send Order Ready for Pickup Notification
     */
    public function sendOrderReadyNotification(Order $order): bool
    {
        $customer = $order->customer;
        if (!$customer || !$customer->phone) return false;

        $totalFormatted = 'Rp ' . number_format($order->grand_total, 0, ',', '.');
        $paidFormatted = 'Rp ' . number_format($order->paid_amount, 0, ',', '.');
        $remainingFormatted = 'Rp ' . number_format($order->remaining_amount, 0, ',', '.');
        $rackCode = $order->rack ? $order->rack->rack_code : 'Meja Kasir';

        $message = "Halo Kak *{$customer->name}*,\n\n"
            . "🎉 Kabar gembira! Cucian Anda dengan nomor invoice *{$order->invoice_code}* sudah *SELESAI, BERSIH, & WANGI*.\n\n"
            . "📦 *Lokasi Pengambilan:* {$rackCode}\n"
            . "💰 *Total Tagihan:* {$totalFormatted}\n"
            . "💵 *Sisa Pembayaran:* {$remainingFormatted}\n\n"
            . "Silakan tunjukkan pesan ini atau struk nota saat pengambilan di kasir. Terima kasih!";

        return $this->sendMessage($customer->phone, $message);
    }

    /**
     * Dispatch WhatsApp message (Gateway wrapper)
     */
    public function sendMessage(string $phone, string $message): bool
    {
        // Normalize phone number to 62xxx
        $cleanPhone = preg_replace('/[^0-9]/', '', $phone);
        if (str_starts_with($cleanPhone, '0')) {
            $cleanPhone = '62' . substr($cleanPhone, 1);
        }

        // Log message for audit / development simulation
        Log::info("WhatsApp Gateway Dispatched to [{$cleanPhone}]:\n" . $message);

        // If webhook API key is configured in .env (e.g. Fonnte / Wablas), trigger HTTP POST
        $apiKey = config('services.whatsapp.api_key');
        if ($apiKey) {
            try {
                Http::withHeaders(['Authorization' => $apiKey])
                    ->post('https://api.fonnte.com/send', [
                        'target' => $cleanPhone,
                        'message' => $message,
                    ]);
            } catch (\Exception $e) {
                Log::error('WhatsApp API Error: ' . $e->getMessage());
            }
        }

        return true;
    }
}

