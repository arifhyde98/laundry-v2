<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TrackingController extends Controller
{
    public function show(Request $request, $invoice = null)
    {
        $searchCode = $invoice ?? $request->input('search');
        $order = null;

        if ($searchCode) {
            $order = Order::with(['customer:id,name', 'rack', 'items.service', 'trackingLogs'])
                ->where('invoice_code', $searchCode)
                ->first();
        }

        return Inertia::render('Public/Tracking', [
            'searchCode' => $searchCode,
            'order' => $order,
        ]);
    }
}

