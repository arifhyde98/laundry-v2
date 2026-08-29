<?php

namespace App\Http\Controllers\Expenses;

use App\Http\Controllers\Controller;
use App\Models\Expense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class ExpenseController extends Controller
{
    public function index(Request $request)
    {
        $expenses = Expense::with('user')
            ->latest('expense_date')
            ->paginate(15);

        $totalThisMonth = Expense::whereYear('expense_date', now()->year)
            ->whereMonth('expense_date', now()->month)
            ->sum('amount');

        return Inertia::render('Expenses/Index', [
            'expenses' => $expenses,
            'totalThisMonth' => (float)$totalThisMonth,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'category' => ['required', 'string'],
            'title' => ['required', 'string', 'max:255'],
            'amount' => ['required', 'numeric', 'min:1'],
            'expense_date' => ['required', 'date'],
            'description' => ['nullable', 'string'],
        ]);

        $validated['user_id'] = Auth::id();

        Expense::create($validated);

        return back()->with('success', 'Pengeluaran kas operasional berhasil dicatat.');
    }

    public function destroy($id)
    {
        $expense = Expense::findOrFail($id);
        $expense->delete();

        return back()->with('success', 'Data pengeluaran berhasil dihapus.');
    }
}

