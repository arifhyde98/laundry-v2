<?php

namespace App\Http\Controllers\Outlet;

use App\Http\Controllers\Controller;
use App\Models\Outlet;
use Illuminate\Http\Request;
use Inertia\Inertia;

class OutletController extends Controller
{
    public function index()
    {
        $outlet = Outlet::firstOrCreate(
            ['id' => 1],
            [
                'name' => 'Laundry Express',
                'phone' => '0812-3456-7890',
                'address' => 'Jl. Utama Laundry No. 1, Kota',
                'receipt_header' => 'Cucian Bersih, Wangi, & Higienis',
                'receipt_footer' => 'Perhatian: 1. Komplain maks 1x24 jam setelah barang diambil. 2. Cucian tidak diambil > 30 hari di luar tanggung jawab kami.',
                'receipt_paper_size' => '58mm',
            ]
        );

        $waLogs = \App\Models\WhatsappLog::with('order:id,invoice_code')
            ->orderBy('id', 'desc')
            ->take(20)
            ->get();

        return Inertia::render('Outlet/Index', [
            'outlet' => $outlet,
            'waLogs' => $waLogs,
        ]);
    }

    public function update(Request $request)
    {
        $outlet = Outlet::firstOrCreate(['id' => 1]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:50'],
            'address' => ['nullable', 'string'],
            'receipt_header' => ['nullable', 'string', 'max:255'],
            'receipt_footer' => ['nullable', 'string'],
            'receipt_paper_size' => ['required', 'in:58mm,80mm'],
            'is_wa_enabled' => ['boolean'],
            'wa_api_token' => ['nullable', 'string', 'max:255'],
            'commission_washing' => ['required', 'numeric', 'min:0'],
            'commission_ironing' => ['required', 'numeric', 'min:0'],
            'commission_packing' => ['required', 'numeric', 'min:0'],
        ]);

        $outlet->update($validated);

        return back()->with('success', 'Profil Outlet & Pengaturan Struk Kasir berhasil diperbarui!');
    }

    public function resetTransactions()
    {
        // Fitur Developer: Reset Transaksi Saja
        try {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            \App\Models\Order::truncate();
            \App\Models\OrderItem::truncate();
            \App\Models\OrderPayment::truncate();
            \App\Models\OrderTrackingLog::truncate();
            \App\Models\CustomerDeposit::truncate();
            \App\Models\Shift::truncate();
            \App\Models\Expense::truncate();
            \App\Models\EmployeeCommission::truncate();
            \App\Models\RewashTicket::truncate();
            \App\Models\WhatsappLog::truncate();
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            // Reset Customer balances & Inventory stock back to 0
            \App\Models\Customer::query()->update(['deposit_balance' => 0, 'point_balance' => 0]);
            \App\Models\InventoryItem::query()->update(['stock' => 0]);
            
            return redirect()->route('dashboard')->with('success', 'Berhasil! Seluruh riwayat transaksi & uang kasir telah dihapus bersih. Saldo pelanggan & stok barang dikembalikan ke 0. Sistem sudah kembali seperti toko baru buka (Fresh).');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            return back()->with('error', 'Gagal mereset data: ' . $e->getMessage());
        }
    }
}

