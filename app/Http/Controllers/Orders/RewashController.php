<?php

namespace App\Http\Controllers\Orders;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\RewashTicket;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RewashController extends Controller
{
    public function index()
    {
        $tickets = RewashTicket::with(['order.customer', 'creator', 'handler'])->latest()->get();
        $recentOrders = Order::with('customer')->latest()->limit(50)->get();

        return Inertia::render('Rewash/Index', [
            'tickets' => $tickets,
            'recentOrders' => $recentOrders
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'order_id' => 'required|exists:orders,id',
            'reason' => 'required|string',
        ]);

        RewashTicket::create([
            'ticket_code' => 'RW-' . strtoupper(uniqid()),
            'order_id' => $validated['order_id'],
            'created_by' => Auth::id(),
            'reason' => $validated['reason'],
            'status' => 'pending'
        ]);

        return back()->with('success', 'Tiket komplain cuci ulang berhasil dibuat.');
    }

    public function update(Request $request, $id)
    {
        $ticket = RewashTicket::findOrFail($id);
        
        $validated = $request->validate([
            'status' => 'required|in:pending,processing,resolved,rejected',
            'resolution_note' => 'nullable|string'
        ]);

        $ticket->status = $validated['status'];
        if ($validated['status'] === 'resolved' || $validated['status'] === 'rejected') {
            $ticket->handled_by = Auth::id();
            $ticket->resolved_at = now();
            $ticket->resolution_note = $validated['resolution_note'] ?? null;
        }

        $ticket->save();

        return back()->with('success', 'Status tiket cuci ulang berhasil diperbarui.');
    }
}

