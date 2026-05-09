<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Area;
use App\Models\Order;
use App\Models\Product;
use App\Models\Client;
use App\Models\Setting;
use App\Models\Table;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;

/**
 * PosApiController — Endpoints JSON para los componentes React del POS.
 * Las rutas web existentes NO se modifican (compatibilidad total).
 */
class PosApiController extends Controller
{
    /**
     * GET /api/pos/areas
     * Devuelve salones con sus mesas, orden activa y reservas del día.
     */
    public function areas(): JsonResponse
    {
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';

        $areas = Area::with([
            'tables' => function ($q) {
                $q->with([
                    'orders' => fn($q) => $q->where('status', 'pending')->select('id', 'table_id', 'total', 'discount', 'tip', 'status'),
                    'reservations' => fn($q) => $q
                        ->where('status', 'confirmed')
                        ->whereDate('reservation_time', Carbon::today())
                        ->where('reservation_time', '>=', Carbon::now()->subHours(2))
                        ->orderBy('reservation_time'),
                ]);
            },
        ])->get();

        $mapped = $areas->map(function ($area) use ($currency) {
            return [
                'id'     => $area->id,
                'name'   => $area->name,
                'tables' => $area->tables->map(function ($table) use ($currency) {
                    $activeOrder = $table->orders->first();
                    return [
                        'id'                   => $table->id,
                        'name'                 => $table->name,
                        'x_pos'                => $table->x_pos,
                        'y_pos'                => $table->y_pos,
                        'active_order'         => $activeOrder ? [
                            'id'    => $activeOrder->id,
                            'total' => number_format(
                                $activeOrder->total + ($activeOrder->tip ?? 0) - ($activeOrder->discount ?? 0),
                                2, '.', ''
                            ),
                        ] : null,
                        'reservations_count'   => $table->reservations->count(),
                        'upcoming_reservations' => $table->reservations->take(2)->map(fn($r) => [
                            'id'               => $r->id,
                            'time'             => $r->reservation_time->format('H:i'),
                            'client_first_name' => explode(' ', $r->client_name)[0],
                        ])->values(),
                    ];
                })->values(),
            ];
        });

        return response()->json($mapped);
    }

    /**
     * GET /api/pos/order/{tableId}
     * Devuelve la orden activa de una mesa con sus detalles.
     */
    public function order(int $tableId): JsonResponse
    {
        $currency = Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';

        $order = Order::where('table_id', $tableId)
            ->where('status', 'pending')
            ->with(['details' => fn($q) => $q->with('product:id,name,price,image')])
            ->first();

        if (!$order) {
            return response()->json(null);
        }

        return response()->json($this->formatOrder($order, $currency));
    }

    /**
     * GET /api/pos/products
     * Devuelve todos los productos activos y vendibles con su categoría.
     */
    public function products(): JsonResponse
    {
        $products = Product::with('category:id,name,image')
            ->where('is_active', true)
            ->where('is_saleable', true)
            ->orderBy('name')
            ->get()
            ->map(fn($p) => [
                'id'          => $p->id,
                'name'        => $p->name,
                'price'       => $p->price,
                'image'       => $p->image,
                'barcode'     => $p->barcode,
                'stock'       => $p->stock,
                'category_id' => $p->category_id,
                'category'    => $p->category ? ['id' => $p->category->id, 'name' => $p->category->name, 'image' => $p->category->image] : null,
            ]);

        return response()->json($products);
    }

    /**
     * GET /api/pos/clients
     * Lista de clientes para el autocomplete del checkout.
     */
    public function clients(): JsonResponse
    {
        $clients = Client::select('id', 'name', 'document_number')
            ->orderBy('name')
            ->get();

        return response()->json($clients);
    }

    // ── Helper privado ────────────────────────────────────────────────

    private function formatOrder(Order $order, string $currency): array
    {
        return [
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
        ];
    }
}
