<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\POS\PosController;
use App\Http\Controllers\POS\ShiftController;
use App\Http\Controllers\Orders\OrderController;
use App\Http\Controllers\Orders\WorkstationController;
use App\Http\Controllers\Orders\RewashController;
use App\Http\Controllers\Storage\RackController;
use App\Http\Controllers\Customers\CustomerController;
use App\Http\Controllers\Services\ServiceController;
use App\Http\Controllers\Inventory\InventoryController;
use App\Http\Controllers\Outlet\OutletController;
use App\Http\Controllers\Expenses\ExpenseController;
use App\Http\Controllers\Reports\ReportController;
use App\Http\Controllers\Users\UserController;
use App\Http\Controllers\Public\TrackingController;

// Public Routes
Route::get('/track/{invoice?}', [TrackingController::class, 'show'])->name('public.track');

// Authentication Routes
Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
Route::post('/login', [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Authenticated App Routes
Route::middleware(['auth'])->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/dashboard', [DashboardController::class, 'index']);

    // POS & Shifts (Cashier & Owner)
    Route::middleware(['role:owner,cashier'])->group(function () {
        Route::get('/pos', [PosController::class, 'index'])->name('pos.index');
        Route::post('/pos', [PosController::class, 'store'])->name('pos.store');
        Route::post('/pos/shift/open', [ShiftController::class, 'openShift'])->name('pos.shift.open');
        Route::post('/pos/shift/close', [ShiftController::class, 'closeShift'])->name('pos.shift.close');

        // Orders Management
        Route::get('/orders', [OrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{id}', [OrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{id}/pay', [OrderController::class, 'pay'])->name('orders.pay');

        // DATA MASTER (Operational)
        Route::get('/services', [ServiceController::class, 'index'])->name('services.index');
        Route::post('/services', [ServiceController::class, 'store'])->name('services.store');
        Route::put('/services/{id}', [ServiceController::class, 'update'])->name('services.update');

        Route::get('/customers', [CustomerController::class, 'index'])->name('customers.index');
        Route::post('/customers', [CustomerController::class, 'store'])->name('customers.store');
        Route::put('/customers/{id}', [CustomerController::class, 'update'])->name('customers.update');
        Route::post('/customers/{id}/deposit', [CustomerController::class, 'deposit'])->name('customers.deposit');
    });

    // Workstation / Antrian Cuci (Accessible to All Roles)
    Route::get('/workstation', [WorkstationController::class, 'index'])->name('workstation.index');
    Route::post('/workstation/{id}/status', [WorkstationController::class, 'updateStatus'])->name('workstation.status');
    
    Route::get('/racks', [RackController::class, 'index'])->name('racks.index');

    // Rewash / Garansi Komplain
    Route::resource('rewash', RewashController::class)->only(['index', 'store', 'update']);

    // Owner ONLY Routes (Financials, Settings, Staff, Inventory)
    Route::middleware(['role:owner'])->group(function () {
        // Inventory & Chemical Recipes
        Route::get('/inventory', [InventoryController::class, 'index'])->name('inventory.index');
        Route::post('/inventory', [InventoryController::class, 'store'])->name('inventory.store');
        Route::put('/inventory/{id}', [InventoryController::class, 'update'])->name('inventory.update');
        Route::post('/inventory/{id}/adjust', [InventoryController::class, 'adjustStock'])->name('inventory.adjust');
        Route::delete('/inventory/{id}', [InventoryController::class, 'destroy'])->name('inventory.destroy');

        // Master Profil Outlet & Header Struk
        Route::get('/outlet', [OutletController::class, 'index'])->name('outlet.index');
        Route::put('/outlet', [OutletController::class, 'update'])->name('outlet.update');
        Route::post('/outlet/reset-transactions', [OutletController::class, 'resetTransactions'])->name('outlet.reset');

        // Payment Gateways
        Route::get('/payment-gateways', [\App\Http\Controllers\PaymentGatewayController::class, 'index'])->name('payment-gateways.index');
        Route::put('/payment-gateways/{id}', [\App\Http\Controllers\PaymentGatewayController::class, 'update'])->name('payment-gateways.update');

        // KEUANGAN & LAPORAN
        Route::get('/expenses', [ExpenseController::class, 'index'])->name('expenses.index');
        Route::post('/expenses', [ExpenseController::class, 'store'])->name('expenses.store');
        Route::delete('/expenses/{id}', [ExpenseController::class, 'destroy'])->name('expenses.destroy');

        Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
        Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');

        // Staff Users Management
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
        Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

        // Destructive / High Privilege Actions
        Route::post('/orders/{id}/cancel', [OrderController::class, 'destroy'])->name('orders.cancel');
        Route::delete('/services/{id}', [ServiceController::class, 'destroy'])->name('services.destroy');
        Route::delete('/customers/{id}', [CustomerController::class, 'destroy'])->name('customers.destroy');
        
        Route::post('/racks', [RackController::class, 'store'])->name('racks.store');
        Route::delete('/racks/{id}', [RackController::class, 'destroy'])->name('racks.destroy');
    });
});
