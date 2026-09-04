<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\Order;
use App\Models\OrderPayment;
use App\Models\OrderTrackingLog;
use App\Models\Rack;
use App\Models\Shift;
use App\Services\ReceiptService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $status = $request->input('status');
        $paymentStatus = $request->input('payment_status');

        $query = Order::with(['customer', 'rack', 'items.service'])
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('invoice_code', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($cq) use ($search) {
                        $cq->where('name', 'like', "%{$search}%")
                            ->orWhere('phone', 'like', "%{$search}%");
                    });
            });
        }

        if ($status) {
            $query->where('order_status', $status);
        }

        if ($paymentStatus) {
            $query->where('payment_status', $paymentStatus);
        }

        $orders = $query->paginate(15)->withQueryString();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
            'filters' => [
                'search' => $search,
                'status' => $status,
                'payment_status' => $paymentStatus,
            ],
        ]);
    }

    public function show($id, Request $request, ReceiptService $receiptService)
    {
        $order = Order::with(['customer', 'user', 'rack', 'items.service', 'payments.receiver', 'trackingLogs.changer', 'rewashTickets'])
            ->findOrFail($id);

        $receiptData = $receiptService->getReceiptData($order);
        $availableRacks = Rack::where('is_available', true)->orWhere('id', $order->rack_id)->get();

        return Inertia::render('Orders/Detail', [
            'order' => $order,
            'receipt' => $receiptData,
            'availableRacks' => $availableRacks,
            'autoPrint' => (bool) $request->input('autoprint'),
        ]);
    }

    public function pay(Request $request, $id, WhatsAppService $whatsAppService)
    {
        $order = Order::findOrFail($id);

        $remaining_balance = max(0, (float) $order->grand_total - (float) $order->paid_amount);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:1', 'max:'.$remaining_balance],
            'payment_method' => ['required', 'in:cash,deposit,qris,transfer'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();

        if (Auth::user()->role === 'cashier' && ! $activeShift) {
            return back()->with('error', 'Akses Ditolak: Anda wajib "Buka Shift" terlebih dahulu sebelum bisa menerima pembayaran.');
        }

        DB::beginTransaction();

        try {
            $amount = (float) $validated['amount'];

            // Jika pembayaran menggunakan deposit, pastikan saldo cukup dan kurangi saldo pelanggan
            if ($validated['payment_method'] === 'deposit') {
                $customer = Customer::lockForUpdate()->findOrFail($order->customer_id);

                if ((float) $customer->deposit_balance < $amount) {
                    DB::rollBack();

                    return back()->with('error', 'Saldo deposit pelanggan tidak mencukupi (Tersedia: Rp '.number_format($customer->deposit_balance, 0, ',', '.').').');
                }

                $customer->decrement('deposit_balance', $amount);

                CustomerDeposit::create([
                    'customer_id' => $customer->id,
                    'user_id' => Auth::id(),
                    'amount' => $amount,
                    'type' => 'order_deduction',
                    'balance_after' => $customer->deposit_balance,
                    'notes' => "Pelunasan tagihan pesanan {$order->invoice_code}",
                ]);
            }

            $newPaidTotal = (float) $order->paid_amount + $amount;
            $newPaymentStatus = $newPaidTotal >= (float) $order->grand_total ? 'paid' : 'partial';

            $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();

            OrderPayment::create([
                'order_id' => $order->id,
                'shift_id' => $activeShift ? $activeShift->id : null,
                'received_by' => Auth::id(),
                'amount_paid' => $amount,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
                'paid_at' => now(),
            ]);

            $order->update([
                'paid_amount' => $newPaidTotal,
                'payment_status' => $newPaymentStatus,
            ]);

            OrderTrackingLog::create([
                'order_id' => $order->id,
                'changed_by' => Auth::id(),
                'status_to' => $order->order_status,
                'notes' => 'Pembayaran diterima sebesar Rp '.number_format($amount, 0, ',', '.')." via {$validated['payment_method']}.",
            ]);

            if ($activeShift) {
                if ($validated['payment_method'] === 'cash') {
                    $activeShift->increment('cash_income', $amount);
                    $activeShift->increment('expected_cash', $amount);
                } else {
                    $activeShift->increment('non_cash_income', $amount);
                }
            }

            DB::commit();

            return back()->with('success', 'Pembayaran sebesar Rp '.number_format($amount, 0, ',', '.').' berhasil dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();

            return back()->with('error', 'Gagal mencatat pembayaran: '.$e->getMessage());
        }
    }

    public function destroy($id)
    {
        $order = Order::findOrFail($id);
        $order->update(['order_status' => 'cancelled']);

        return redirect()->route('orders.index')->with('success', "Order {$order->invoice_code} telah dibatalkan.");
    }
}
