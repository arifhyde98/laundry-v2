<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\Service;
use App\Models\Rack;
use App\Models\Shift;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\OrderPayment;
use App\Models\OrderTrackingLog;
use App\Services\WhatsAppService;
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $services = Service::where('is_active', true)->orderBy('name')->get();
        $customers = Customer::orderBy('name')->get(['id', 'name', 'phone', 'address', 'deposit_balance']);
        $racks = Rack::where('is_available', true)->get();

        // Get Active Shift for current user
        $activeShift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        return Inertia::render('POS/Index', [
            'services' => $services,
            'customers' => $customers,
            'racks' => $racks,
            'activeShift' => $activeShift,
        ]);
    }

    public function store(Request $request, WhatsAppService $whatsAppService, ReceiptService $receiptService)
    {
        $validated = $request->validate([
            'customer_id' => ['required', 'exists:customers,id'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.service_id' => ['nullable', 'exists:services,id'],
            'items.*.item_name' => ['required', 'string'],
            'items.*.quantity' => ['required', 'numeric', 'min:0.1'],
            'items.*.unit_price' => ['required', 'numeric', 'min:0'],
            'discount_amount' => ['nullable', 'numeric', 'min:0'],
            'delivery_fee' => ['nullable', 'numeric', 'min:0'],
            'payment_status' => ['required', 'in:unpaid,partial,paid'],
            'payment_method' => ['required', 'in:cash,deposit,qris,transfer'],
            'paid_amount' => ['required', 'numeric', 'min:0'],
            'estimated_hours' => ['nullable', 'integer'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();

        // Security Policy: Cashier MUST open a shift to process transactions. Owner is flexible.
        if (Auth::user()->role === 'cashier' && !$activeShift) {
            return back()->with('error', 'Akses Ditolak: Anda wajib "Buka Shift" terlebih dahulu sebelum bisa memproses pesanan.');
        }

        DB::beginTransaction();

        try {
            $customer = Customer::findOrFail($validated['customer_id']);

            // Calculate Totals
            $subtotal = 0;
            $totalWeightQty = 0;
            $maxEstimatedHours = 72; // default 3 days

            foreach ($validated['items'] as $item) {
                $subtotal += (float)$item['quantity'] * (float)$item['unit_price'];
                $totalWeightQty += (float)$item['quantity'];

                if (!empty($item['service_id'])) {
                    $svc = Service::find($item['service_id']);
                    if ($svc && $svc->estimated_hours < $maxEstimatedHours) {
                        $maxEstimatedHours = $svc->estimated_hours;
                    }
                }
            }

            $discount = (float)($validated['discount_amount'] ?? 0);
            $delivery = (float)($validated['delivery_fee'] ?? 0);
            $grandTotal = max(0, $subtotal - $discount + $delivery);
            $paidAmount = min($grandTotal, (float)$validated['paid_amount']);

            // Verify Deposit Payment
            if ($validated['payment_method'] === 'deposit') {
                if ($customer->deposit_balance < $paidAmount) {
                    return back()->with('error', 'Saldo deposit pelanggan tidak mencukupi (Tersedia: Rp ' . number_format($customer->deposit_balance, 0, ',', '.') . ').');
                }
                $customer->decrement('deposit_balance', $paidAmount);

                CustomerDeposit::create([
                    'customer_id' => $customer->id,
                    'user_id' => Auth::id(),
                    'amount' => $paidAmount,
                    'type' => 'order_deduction',
                    'balance_after' => $customer->deposit_balance,
                    'notes' => 'Pembayaran pesanan POS',
                ]);
            }

            // Generate Unique Invoice Code
            $todayPrefix = Carbon::now()->format('Ymd');
            $todayCount = Order::whereDate('created_at', Carbon::today())->count() + 1;
            $invoiceCode = 'INV-' . $todayPrefix . '-' . str_pad($todayCount, 4, '0', STR_PAD_LEFT);

            // Active Shift
            $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();

            // Create Order
            $order = Order::create([
                'invoice_code' => $invoiceCode,
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'shift_id' => $activeShift ? $activeShift->id : null,
                'total_weight_qty' => $totalWeightQty,
                'subtotal_amount' => $subtotal,
                'discount_amount' => $discount,
                'delivery_fee' => $delivery,
                'grand_total' => $grandTotal,
                'paid_amount' => $paidAmount,
                'payment_status' => $paidAmount >= $grandTotal ? 'paid' : ($paidAmount > 0 ? 'partial' : 'unpaid'),
                'payment_method' => $validated['payment_method'],
                'order_status' => 'received',
                'order_date' => Carbon::today(),
                'estimated_completion' => Carbon::now()->addHours($maxEstimatedHours),
                'notes' => $validated['notes'],
            ]);

            // Create Order Items
            foreach ($validated['items'] as $item) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'service_id' => $item['service_id'] ?? null,
                    'item_name' => $item['item_name'],
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'subtotal' => (float)$item['quantity'] * (float)$item['unit_price'],
                ]);
            }

            // Record Payment if paid > 0
            if ($paidAmount > 0) {
                OrderPayment::create([
                    'order_id' => $order->id,
                    'shift_id' => $activeShift ? $activeShift->id : null,
                    'received_by' => Auth::id(),
                    'amount_paid' => $paidAmount,
                    'payment_method' => $validated['payment_method'],
                    'paid_at' => now(),
                ]);

                // Update Shift Cash / Non-Cash totals
                if ($activeShift) {
                    if ($validated['payment_method'] === 'cash') {
                        $activeShift->increment('cash_income', $paidAmount);
                        $activeShift->increment('expected_cash', $paidAmount);
                    } else {
                        $activeShift->increment('non_cash_income', $paidAmount);
                    }
                }
            }

            // Record Initial Tracking Log
            OrderTrackingLog::create([
                'order_id' => $order->id,
                'changed_by' => Auth::id(),
                'status_to' => 'received',
                'notes' => 'Pesanan diterima di kasir POS.',
            ]);

            DB::commit();

            // Dispatch WhatsApp Notification in Background
            $whatsAppService->sendOrderReceivedNotification($order);

            return redirect()->route('orders.show', $order->id)->with('success', "Order {$order->invoice_code} berhasil dibuat!");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses transaksi: ' . $e->getMessage());
        }
    }
}

