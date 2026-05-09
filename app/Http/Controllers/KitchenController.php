<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\OrderDetail;
use Illuminate\Http\Request;

class KitchenController extends Controller
{
    /**
     * Pantalla principal del KDS (Blade shell — React monta aquí).
     */
    public function index()
    {
        return view('kitchen.index');
    }

    /**
     * GET /api/kitchen/orders — JSON para el componente React KitchenDisplay.
     * Devuelve órdenes con platos pendientes/cocinando, ordenadas FIFO.
     */
    public function ordersJson()
    {
        $orders = Order::whereHas('details', fn($q) => $q->whereIn('status', ['pending', 'cooking']))
            ->with([
                'table:id,name',
                'details' => fn($q) => $q->whereIn('status', ['pending', 'cooking'])
                    ->with('product:id,name'),
            ])
            ->orderBy('created_at', 'asc')
            ->get()
            ->map(fn($order) => [
                'id'         => $order->id,
                'table_name' => $order->table->name ?? 'Barra',
                'created_at' => $order->created_at->toIso8601String(),
                'details'    => $order->details->map(fn($d) => [
                    'id'           => $d->id,
                    'quantity'     => $d->quantity,
                    'product_name' => $d->product->name,
                    'note'         => $d->note,
                    'status'       => $d->status,
                ])->values(),
            ]);

        return response()->json($orders);
    }

    /**
     * POST /kitchen/{detail}/status
     * Avanza el estado: pending → cooking → served.
     * Responde JSON si lo pide React, redirect si viene del Blade legacy.
     */
    public function updateStatus(Request $request, OrderDetail $detail)
    {
        if ($detail->status === 'pending') {
            $detail->update(['status' => 'cooking']);
        } elseif ($detail->status === 'cooking') {
            $detail->update(['status' => 'served']);
        }

        if ($request->expectsJson()) {
            return response()->json(['status' => $detail->status]);
        }

        return redirect()->route('kitchen.index');
    }
}