<?php

namespace App\Http\Controllers;

use App\Http\Requests\CheckoutOrderRequest;
use App\Models\Area;
use App\Models\Table;
use App\Models\Category;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\InventoryLog;
use App\Models\Client;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class PosController extends Controller
{
    public function index()
    {
        $areas = Area::with(['tables' => function($q) {
            $q->with(['orders' => function($q) {
                $q->where('status', 'pending');
            }, 'reservations' => function($q) {
                $q->where('status', 'confirmed')
                  ->whereDate('reservation_time', Carbon::today())
                  ->where('reservation_time', '>=', Carbon::now()->subHours(2)) 
                  ->orderBy('reservation_time', 'asc');
            }]);
        }])->get();
        
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        return view('pos.index', compact('areas', 'currency'));
    }

    public function order(Table $table)
    {
        // Filtro: Solo productos activos y vendibles
        $categories = Category::with(['products' => function($q) {
            $q->where('is_active', true)
              ->where('is_saleable', true);
        }])->where('is_active', true)->get();

        $order = Order::where('table_id', $table->id)->where('status', 'pending')->with('details.product')->first();
        $occupiedTableIds = Order::where('status', 'pending')->pluck('table_id');
        $freeTables = Table::whereNotIn('id', $occupiedTableIds)->where('id', '!=', $table->id)->with('area')->get();
        $clients = Client::select('id', 'name', 'document_number')->orderBy('name')->get();
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';

        return view('pos.order', compact('table', 'categories', 'order', 'freeTables', 'clients', 'currency'));
    }

    // --- AGREGAR POR CLIC (Normal) ---
    public function addToOrder(Request $request, Table $table)
    {
        $this->authorize('addItem', Order::class);

        $validated = $request->validate([
            'product_id' => 'required|integer|exists:products,id',
        ]);

        $product = Product::findOrFail($validated['product_id']);
        $this->addItemToTable($table, $product);

        if ($request->expectsJson()) {
            return $this->getOrderJson($table);
        }
        return $this->getCartHtml($table);
    }

    // --- AGREGAR POR CÓDIGO DE BARRAS ---
    public function addByBarcode(Request $request, Table $table)
    {
        $this->authorize('addItem', Order::class);

        $validated = $request->validate([
            'barcode' => 'required|string|max:50',
        ]);

        $product = Product::where('barcode', $validated['barcode'])
                          ->where('is_active', true)
                          ->where('is_saleable', true)
                          ->first();

        if (!$product) {
            return response()->json(['message' => 'Producto no encontrado'], 404);
        }

        $this->addItemToTable($table, $product);

        if ($request->expectsJson()) {
            return $this->getOrderJson($table);
        }
        return $this->getCartHtml($table);
    }

    // Lógica auxiliar para no repetir código al agregar
    private function addItemToTable(Table $table, Product $product)
    {
        DB::transaction(function() use ($table, $product) {
            $order = Order::firstOrCreate(
                ['table_id' => $table->id, 'status' => 'pending'], 
                ['user_id' => Auth::id() ?? 1, 'total' => 0]
            );

            $detail = $order->details()->where('product_id', $product->id)->first();

            if ($detail) {
                $detail->increment('quantity');
            } else {
                $order->details()->create([
                    'product_id' => $product->id, 
                    'quantity' => 1, 
                    'price' => $product->price, 
                    'status' => 'pending'
                ]);
            }
            $this->recalculateTotal($order);
        });
    }

    // --- ACTUALIZAR CANTIDAD ---
    public function updateQuantity(Request $request, OrderDetail $detail)
    {
        $this->authorize('updateQuantity', $detail);

        $validated = $request->validate([
            'quantity' => 'required|integer|min:0|max:999',
        ]);

        $newQty = $validated['quantity'];
        $order  = $detail->order;
        $table  = $order->table()->firstOrFail();

        if ($newQty < 1) {
            $detail->delete();
        } else {
            $detail->update(['quantity' => $newQty]);
        }

        $this->recalculateTotal($order);

        if ($request->expectsJson()) {
            return $this->getOrderJson($table);
        }
        return $this->getCartHtml($table);
    }

    // --- ACTUALIZAR NOTA ---
    public function updateNote(Request $request, OrderDetail $detail)
    {
        $this->authorize('updateNote', $detail);

        $validated = $request->validate([
            'note' => 'nullable|string|max:500',
        ]);

        $detail->update(['note' => $validated['note'] ?? null]);
        $table = $detail->order->table()->firstOrFail();

        if ($request->expectsJson()) {
            return $this->getOrderJson($table);
        }
        return $this->getCartHtml($table);
    }

    // --- ELIMINAR ITEM ---
    public function removeItem(Request $request, OrderDetail $detail)
    {
        $this->authorize('delete', $detail);

        $order = $detail->order;
        $table = $order->table()->firstOrFail();
        $detail->delete();
        $this->recalculateTotal($order);

        if ($request->expectsJson()) {
            return $this->getOrderJson($table);
        }
        return $this->getCartHtml($table);
    }

    // --- APLICAR DESCUENTO / PROPINA ---
    public function applyDiscount(Request $request, Order $order)
    {
        $this->authorize('applyDiscount', $order);

        $validated = $request->validate([
            'discount' => 'nullable|numeric|min:0',
            'tip'      => 'nullable|numeric|min:0',
        ]);

        $order->discount = $validated['discount'] ?? 0;
        $order->tip      = $validated['tip'] ?? 0;
        $order->save();
        $this->recalculateTotal($order);
        $table = $order->table()->firstOrFail();

        if ($request->expectsJson()) {
            return $this->getOrderJson($table);
        }
        return $this->getCartHtml($table);
    }
    
    public function moveTable(Request $request, Order $order) {
        $this->authorize('moveTable', $order);

        $request->validate(['target_table_id' => 'required|exists:tables,id']);
        if (Order::where('table_id', $request->target_table_id)->where('status', 'pending')->exists()) return redirect()->back()->with('error', 'Ocupada.');
        $order->table_id = $request->target_table_id; $order->save();
        return redirect()->route('pos.order', $request->target_table_id);
    }

    public function getSplitContent(Order $order)
    {
        $order->loadMissing(['details.product', 'table']);
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        return view('pos.partials.split_content', compact('order', 'currency'));
    }

    /**
     * Procesar pago parcial (división de cuenta).
     * Crea una nueva orden completada con los ítems seleccionados
     * y elimina esos ítems de la orden original.
     */
    public function processSplit(Request $request, Order $order)
    {
        $request->validate([
            'selected_items'   => 'required|array|min:1',
            'selected_items.*' => 'exists:order_details,id',
            'payment_method'   => 'nullable|string|in:cash,card',
        ]);

        $selectedIds    = $request->input('selected_items');
        $paymentMethod  = $request->input('payment_method', 'cash');

        DB::transaction(function () use ($order, $selectedIds, $paymentMethod) {
            // Obtener los detalles seleccionados
            $selectedDetails = $order->details()->whereIn('id', $selectedIds)->with('product')->get();

            if ($selectedDetails->isEmpty()) {
                return;
            }

            // Calcular el subtotal de los ítems seleccionados
            $splitTotal = $selectedDetails->sum(fn($d) => $d->price * $d->quantity);

            // Crear orden de cobro parcial (completed inmediatamente)
            $splitOrder = Order::create([
                'table_id'       => $order->table_id,
                'user_id'        => Auth::id(),
                'total'          => $splitTotal,
                'status'         => 'completed',
                'payment_method' => $paymentMethod,
                'client_name'    => 'División de cuenta',
                'document_type'  => 'Ticket',
            ]);

            // Mover los detalles seleccionados a la nueva orden
            foreach ($selectedDetails as $detail) {
                $splitOrder->details()->create([
                    'product_id' => $detail->product_id,
                    'quantity'   => $detail->quantity,
                    'price'      => $detail->price,
                    'note'       => $detail->note,
                    'status'     => 'served',
                ]);
                $detail->delete();
            }

            // Recalcular el total de la orden original
            $this->recalculateTotal($order->fresh());
        });

        return redirect()
            ->route('pos.order', $order->table_id)
            ->with('success', 'Pago parcial registrado correctamente.');
    }

    public function precheck(Order $order) { $settings = Setting::pluck('value', 'key')->toArray(); return view('sales.ticket', compact('order', 'settings')); }
    public function kitchenTicket(Order $order) { return view('sales.kitchen_ticket', compact('order')); }

    public function checkout(CheckoutOrderRequest $request, Order $order)
    {
        $this->authorize('checkout', $order);

        if($order->status !== 'pending') return redirect()->route('pos.index')->with('error', 'Orden cerrada.');

        $order->loadMissing('details.product.ingredients');
        $this->ensureStockIsAvailable($order);

        $method = $request->input('payment_method', 'cash');
        $received = $method === 'cash'
            ? (float) $request->input('received_amount', $order->total)
            : (float) $order->total;

        if ($method === 'cash' && $received < (float) $order->total) {
            throw ValidationException::withMessages([
                'received_amount' => 'El monto recibido no puede ser menor al total de la orden.',
            ]);
        }

        $change = max(0, $received - $order->total);
        $clientId = $request->input('client_id');
        $clientName = $clientId
            ? Client::whereKey($clientId)->value('name')
            : ($request->input('client_name') ?? 'Público');

        DB::transaction(function() use ($order, $method, $received, $change, $request, $clientId, $clientName) {
            $order->update([
                'status' => 'completed',
                'payment_method' => $method,
                'received_amount' => $received,
                'change_amount' => $change,
                'document_type' => $request->input('document_type', 'Ticket'),
                'client_id' => $clientId, 
                'client_name' => $clientName,
                'client_document' => $request->input('client_document')
            ]);

            foreach($order->details as $detail) {
                $product = $detail->product;
                $ingredients = $product->ingredients;

                if ($ingredients->count() > 0) {
                    foreach ($ingredients as $ingredient) {
                        $qtyToDeduct = $ingredient->pivot->quantity * $detail->quantity;
                        $oldStock = $ingredient->stock;
                        $ingredient->decrement('stock', $qtyToDeduct);
                        InventoryLog::create([
                            'product_id' => $ingredient->id,
                            'user_id' => Auth::id(),
                            'type' => 'sale',
                            'quantity' => -$qtyToDeduct,
                            'old_stock' => $oldStock,
                            'new_stock' => $oldStock - $qtyToDeduct,
                            'note' => 'Venta: ' . $product->name . ' (Orden #' . $order->id . ')'
                        ]);
                    }
                } else {
                    if (!is_null($product->stock)) {
                        $oldStock = $product->stock;
                        $product->decrement('stock', $detail->quantity);
                        InventoryLog::create([
                            'product_id' => $product->id,
                            'user_id' => Auth::id(),
                            'type' => 'sale',
                            'quantity' => -($detail->quantity),
                            'old_stock' => $oldStock,
                            'new_stock' => $oldStock - $detail->quantity,
                            'note' => 'Venta POS #' . $order->id
                        ]);
                    }
                }
            }
        });

        return redirect()->route('pos.index')->with('success', 'Venta registrada.');
    }

    private function ensureStockIsAvailable(Order $order): void
    {
        foreach ($order->details as $detail) {
            $product = $detail->product;
            $ingredients = $product->ingredients;

            if ($ingredients->isNotEmpty()) {
                foreach ($ingredients as $ingredient) {
                    if (is_null($ingredient->stock)) {
                        continue;
                    }

                    $qtyToDeduct = (int) ($ingredient->pivot->quantity * $detail->quantity);

                    if ($ingredient->stock < $qtyToDeduct) {
                        throw ValidationException::withMessages([
                            'stock' => 'Stock insuficiente para el insumo: '.$ingredient->name,
                        ]);
                    }
                }

                continue;
            }

            if (! is_null($product->stock) && $product->stock < $detail->quantity) {
                throw ValidationException::withMessages([
                    'stock' => 'Stock insuficiente para el producto: '.$product->name,
                ]);
            }
        }
    }

    private function recalculateTotal(Order $order)
    {
        $subtotal = $order->details->sum(fn($d) => $d->price * $d->quantity);
        $total = ($subtotal - ($order->discount ?? 0)) + ($order->tip ?? 0);
        $order->update(['total' => max(0, $total)]);
    }

    private function getCartHtml(Table $table)
    {
        $order    = Order::where('table_id', $table->id)->where('status', 'pending')->with('details.product')->first();
        $clients  = Client::select('id', 'name', 'document_number')->orderBy('name')->get();
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        return view('pos.partials.cart', compact('order', 'clients', 'currency'))->render();
    }

    /**
     * Devuelve la orden activa de una mesa formateada como JSON.
     * Usado por los componentes React del POS.
     */
    private function getOrderJson(Table $table)
    {
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        $order    = Order::where('table_id', $table->id)
            ->where('status', 'pending')
            ->with(['details' => fn($q) => $q->with('product:id,name,price,image')])
            ->first();

        if (!$order) {
            return response()->json(null);
        }

        return response()->json([
            'id'       => $order->id,
            'total'    => $order->total,
            'discount' => $order->discount ?? 0,
            'tip'      => $order->tip ?? 0,
            'currency' => $currency,
            'details'  => $order->details->map(fn($d) => [
                'id'       => $d->id,
                'quantity' => $d->quantity,
                'price'    => $d->price,
                'note'     => $d->note,
                'product'  => [
                    'id'    => $d->product->id,
                    'name'  => $d->product->name,
                    'image' => $d->product->image,
                ],
            ])->values(),
        ]);
    }
}