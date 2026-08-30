<?php

namespace App\Http\Controllers\Customers;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\CustomerDeposit;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $query = Customer::withCount('orders')
            ->latest();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%")
                  ->orWhere('address', 'like', "%{$search}%");
            });
        }

        $customers = $query->paginate(15)->withQueryString();

        return Inertia::render('Customers/Index', [
            'customers' => $customers,
            'filters' => ['search' => $search],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer = Customer::create($validated);

        if ($request->wantsJson()) {
            return response()->json($customer);
        }

        return back()->with('success', "Pelanggan {$customer->name} berhasil ditambahkan.");
    }

    public function update(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:25'],
            'address' => ['nullable', 'string'],
            'notes' => ['nullable', 'string'],
        ]);

        $customer->update($validated);

        return back()->with('success', "Data pelanggan {$customer->name} berhasil diperbarui.");
    }

    public function deposit(Request $request, $id)
    {
        $customer = Customer::findOrFail($id);

        $validated = $request->validate([
            'amount' => ['required', 'numeric', 'min:10000'],
            'payment_method' => ['required', 'in:cash,qris,transfer'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();
        
        if (Auth::user()->role === 'cashier' && !$activeShift) {
            return back()->with('error', 'Akses Ditolak: Anda wajib "Buka Shift" terlebih dahulu sebelum bisa menerima uang deposit.');
        }

        DB::beginTransaction();

        try {
            $customer = Customer::lockForUpdate()->findOrFail($id);
            $amount = (float)$validated['amount'];
            $customer->increment('deposit_balance', $amount);

            $activeShift = Shift::where('user_id', Auth::id())->where('status', 'open')->first();

            CustomerDeposit::create([
                'customer_id' => $customer->id,
                'user_id' => Auth::id(),
                'amount' => $amount,
                'type' => 'topup',
                'balance_after' => $customer->deposit_balance,
                'payment_method' => $validated['payment_method'],
                'notes' => $validated['notes'] ?? null,
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

            return back()->with('success', "Top up saldo sebesar Rp " . number_format($amount, 0, ',', '.') . " berhasil masuk ke dompet {$customer->name}.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memproses top-up deposit: ' . $e->getMessage());
        }
    }

    public function destroy($id)
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return back()->with('success', 'Pelanggan berhasil dihapus.');
    }
}

