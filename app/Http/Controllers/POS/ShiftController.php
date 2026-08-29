<?php

namespace App\Http\Controllers\POS;

use App\Http\Controllers\Controller;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ShiftController extends Controller
{
    public function openShift(Request $request)
    {
        $validated = $request->validate([
            'starting_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if ($activeShift) {
            return back()->with('error', 'Anda sudah memiliki shift kasir yang aktif.');
        }

        Shift::create([
            'user_id' => Auth::id(),
            'starting_cash' => $validated['starting_cash'],
            'expected_cash' => $validated['starting_cash'],
            'notes' => $validated['notes'],
            'status' => 'open',
            'opened_at' => now(),
        ]);

        return back()->with('success', 'Shift kasir berhasil dibuka. Selamat bertugas!');
    }

    public function closeShift(Request $request)
    {
        $validated = $request->validate([
            'closing_cash' => ['required', 'numeric', 'min:0'],
            'notes' => ['nullable', 'string'],
        ]);

        $activeShift = Shift::where('user_id', Auth::id())
            ->where('status', 'open')
            ->first();

        if (!$activeShift) {
            return back()->with('error', 'Tidak ada shift kasir aktif yang ditemukan.');
        }

        $difference = (float)$validated['closing_cash'] - (float)$activeShift->expected_cash;

        $activeShift->update([
            'closing_cash' => $validated['closing_cash'],
            'cash_difference' => $difference,
            'notes' => $validated['notes'],
            'status' => 'closed',
            'closed_at' => now(),
        ]);

        return back()->with('success', 'Shift kasir berhasil ditutup (Z-Report dicatat).');
    }
}

