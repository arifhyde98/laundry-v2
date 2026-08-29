<?php

namespace App\Http\Controllers\Reports;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Shift;
use App\Models\EmployeeCommission;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());

        // Orders within range
        $orders = Order::with(['customer', 'payments'])
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate)
            ->where('order_status', '!=', 'cancelled')
            ->orderBy('order_date', 'desc')
            ->get();

        $totalOmset = $orders->sum('grand_total');
        $totalPaidCash = $orders->sum('paid_amount');
        $totalWeight = $orders->sum('total_weight_qty');
        $totalUnpaid = $orders->whereIn('payment_status', ['unpaid', 'partial'])->sum(function ($o) {
            return $o->remaining_amount;
        });

        // Expenses within range
        $expenses = Expense::whereDate('expense_date', '>=', $startDate)
            ->whereDate('expense_date', '<=', $endDate)
            ->get();
        $totalExpense = $expenses->sum('amount');

        // Commissions within range
        $totalCommissions = EmployeeCommission::whereDate('created_at', '>=', $startDate)
            ->whereDate('created_at', '<=', $endDate)
            ->sum('commission_amount');

        $netProfit = $totalPaidCash - $totalExpense - $totalCommissions;

        // Shifts within range
        $shifts = Shift::with('user')
            ->whereDate('opened_at', '>=', $startDate)
            ->whereDate('opened_at', '<=', $endDate)
            ->latest('opened_at')
            ->get();

        return Inertia::render('Reports/Index', [
            'filters' => [
                'start_date' => $startDate,
                'end_date' => $endDate,
            ],
            'summary' => [
                'totalOrders' => $orders->count(),
                'totalWeight' => (float)$totalWeight,
                'totalOmset' => (float)$totalOmset,
                'totalPaidCash' => (float)$totalPaidCash,
                'totalUnpaid' => (float)$totalUnpaid,
                'totalExpense' => (float)$totalExpense,
                'totalCommissions' => (float)$totalCommissions,
                'netProfit' => (float)$netProfit,
            ],
            'orders' => $orders,
            'expenses' => $expenses,
            'shifts' => $shifts,
        ]);
    }

    public function export(Request $request)
    {
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->endOfMonth()->toDateString());
        $format = $request->input('format', 'csv');

        $orders = Order::with('customer')
            ->whereDate('order_date', '>=', $startDate)
            ->whereDate('order_date', '<=', $endDate)
            ->where('order_status', '!=', 'cancelled')
            ->orderBy('order_date', 'asc')
            ->get();

        if ($format === 'csv') {
            $filename = "laporan_penjualan_{$startDate}_to_{$endDate}.csv";
            $headers = [
                "Content-type"        => "text/csv",
                "Content-Disposition" => "attachment; filename=$filename",
                "Pragma"              => "no-cache",
                "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
                "Expires"             => "0"
            ];
            
            $callback = function() use ($orders) {
                $file = fopen('php://output', 'w');
                fputcsv($file, ['Tanggal', 'Invoice', 'Pelanggan', 'Status Bayar', 'Total Qty', 'Grand Total', 'Metode Bayar']);

                foreach ($orders as $order) {
                    fputcsv($file, [
                        $order->order_date,
                        $order->invoice_code,
                        $order->customer?->name,
                        $order->payment_status,
                        $order->total_weight_qty,
                        $order->grand_total,
                        $order->payment_method
                    ]);
                }
                fclose($file);
            };
            return response()->stream($callback, 200, $headers);
        }
        
        return back()->with('error', 'Format tidak didukung.');
    }
}
