<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderTrackingLog;
use App\Models\Rack;
use App\Models\EmployeeCommission;
use App\Services\ChemicalService;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class WorkstationController extends Controller
{
    public function index()
    {
        $orders = Order::with(['customer', 'items.service', 'rack'])
            ->whereNotIn('order_status', ['completed', 'cancelled'])
            ->orderBy('estimated_completion')
            ->get();

        $racks = Rack::where('is_available', true)->get();

        return Inertia::render('Workstation/Index', [
            'orders' => $orders,
            'racks' => $racks,
        ]);
    }

    public function updateStatus(Request $request, $id, ChemicalService $chemicalService, WhatsAppService $whatsAppService)
    {
        $order = Order::findOrFail($id);

        $validated = $request->validate([
            'status' => ['required', 'in:received,washing,drying,ironing,packing,ready,completed'],
            'rack_id' => ['nullable', 'exists:racks,id'],
            'notes' => ['nullable', 'string'],
        ]);

        DB::beginTransaction();

        try {
            $prevStatus = $order->order_status;
            $newStatus = $validated['status'];

            $updateData = [
                'order_status' => $newStatus,
            ];

            if ($newStatus === 'ready' && !empty($validated['rack_id'])) {
                $updateData['rack_id'] = $validated['rack_id'];
            }

            if ($newStatus === 'completed') {
                $updateData['actual_completion'] = now();
            }

            $order->update($updateData);

            // Record Tracking Log
            OrderTrackingLog::create([
                'order_id' => $order->id,
                'changed_by' => Auth::id(),
                'status_from' => $prevStatus,
                'status_to' => $newStatus,
                'notes' => $validated['notes'] ?? "Status diubah ke {$newStatus}.",
            ]);

            // Auto Deduct Chemical if moved into washing stage
            if ($newStatus === 'washing') {
                $chemicalService->deductStockForOrder($order);
            }

            // Record Commission for Operator
            $commissionRates = [
                'washing' => 500, // Rp 500 / kg
                'ironing' => 1000, // Rp 1000 / kg
                'packing' => 200, // Rp 200 / kg
            ];

            if (isset($commissionRates[$newStatus])) {
                $rate = $commissionRates[$newStatus];
                $qty = (float)$order->total_weight_qty;
                $commAmount = $qty * $rate;

                EmployeeCommission::create([
                    'user_id' => Auth::id(),
                    'order_id' => $order->id,
                    'activity' => $newStatus,
                    'quantity' => $qty,
                    'rate_per_unit' => $rate,
                    'commission_amount' => $commAmount,
                    'is_paid' => false,
                ]);
            }

            DB::commit();

            // Auto Send WA Notification when status is Ready (Siap Diambil)
            if ($newStatus === 'ready') {
                $whatsAppService->sendOrderReadyNotification($order);
            }

            return back()->with('success', "Status order {$order->invoice_code} berhasil diperbarui ke " . strtoupper($newStatus));
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memperbarui status: ' . $e->getMessage());
        }
    }
}

