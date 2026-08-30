<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use App\Models\Expense;
use App\Models\Rack;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $year = (int)$request->input('year', date('Y'));

        // Key Metric Summary Cards
        $totalCustomers = Customer::count();
        $ordersProcessing = Order::whereIn('order_status', ['received', 'washing', 'drying', 'ironing', 'packing'])->count();
        $ordersReady = Order::where('order_status', 'ready')->count();
        $ordersCompleted = Order::where('order_status', 'completed')->count();

        // Financial Metrics
        $todaySales = Order::whereDate('created_at', Carbon::today())->sum('grand_total');
        $thisMonthSales = Order::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('grand_total');
        
        $thisMonthExpenses = Expense::whereYear('expense_date', Carbon::now()->year)
            ->whereMonth('expense_date', Carbon::now()->month)
            ->sum('amount');

        $thisMonthCommissions = \App\Models\EmployeeCommission::whereYear('created_at', Carbon::now()->year)
            ->whereMonth('created_at', Carbon::now()->month)
            ->sum('commission_amount');
        
        $totalUnpaidDebt = Order::whereIn('payment_status', ['unpaid', 'partial'])
            ->selectRaw('SUM(grand_total - paid_amount) as debt')
            ->value('debt') ?? 0;

        // Monthly Sales & Expenses Chart for selected year
        $monthlyLabels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'];
        $monthlySalesData = array_fill(0, 12, 0);
        $monthlyExpensesData = array_fill(0, 12, 0);

        $salesByMonth = Order::selectRaw('MONTH(order_date) as month, SUM(grand_total) as total')
            ->whereYear('order_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        $expensesByMonth = Expense::selectRaw('MONTH(expense_date) as month, SUM(amount) as total')
            ->whereYear('expense_date', $year)
            ->groupBy('month')
            ->pluck('total', 'month');

        foreach ($salesByMonth as $m => $total) {
            $monthlySalesData[$m - 1] = (float)$total;
        }

        foreach ($expensesByMonth as $m => $total) {
            $monthlyExpensesData[$m - 1] = (float)$total;
        }

        // Recent Orders
        $recentOrders = Order::with(['customer', 'rack', 'items'])
            ->latest()
            ->take(6)
            ->get();

        // Available Rack Capacity Status
        $totalRacks = Rack::count();
        $availableRacks = Rack::whereDoesntHave('orders')->count();

        return Inertia::render('Dashboard/Index', [
            'metrics' => [
                'totalCustomers' => $totalCustomers,
                'ordersProcessing' => $ordersProcessing,
                'ordersReady' => $ordersReady,
                'ordersCompleted' => $ordersCompleted,
                'todaySales' => (float)$todaySales,
                'thisMonthSales' => (float)$thisMonthSales,
                'thisMonthExpenses' => (float)$thisMonthExpenses,
                'netProfitThisMonth' => (float)$thisMonthSales - (float)$thisMonthExpenses - (float)$thisMonthCommissions,
                'totalUnpaidDebt' => (float)$totalUnpaidDebt,
                'totalRacks' => $totalRacks,
                'availableRacks' => $availableRacks,
            ],
            'chart' => [
                'year' => $year,
                'labels' => $monthlyLabels,
                'sales' => $monthlySalesData,
                'expenses' => $monthlyExpensesData,
            ],
            'recentOrders' => $recentOrders,
        ]);
    }
}

