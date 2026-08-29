<?php

namespace App\Services;

use App\Models\Order;
use chillerlan\QRCode\QRCode;

class ReceiptService
{
    public function generateQrCode(string $text): string
    {
        return (new QRCode())->render($text);
    }

    public function getReceiptData(Order $order): array
    {
        $trackingUrl = url('/track/' . $order->invoice_code);
        $qrCode = $this->generateQrCode($trackingUrl);

        return [
            'order' => $order->load(['customer', 'items.service', 'payments', 'rack', 'outlet']),
            'trackingUrl' => $trackingUrl,
            'qrCode' => $qrCode,
            'printedAt' => now()->format('d/m/Y H:i'),
        ];
    }
}

