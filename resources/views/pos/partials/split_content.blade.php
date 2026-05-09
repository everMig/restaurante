@extends('layouts.app')

@section('content')
{{-- PosSplit — React Island (Fase 4) --}}
<div
    data-react-component="PosSplit"
    data-react-props="{{ json_encode([
        'orderId'    => $order->id,
        'orderLabel' => 'Mesa: ' . ($order->table->name ?? 'Mesa') . ' — Orden #' . str_pad($order->id, 5, '0', STR_PAD_LEFT),
        'details'    => $order->details->map(fn($d) => [
            'id'           => $d->id,
            'product_name' => $d->product->name,
            'quantity'     => $d->quantity,
            'price'        => (float) $d->price,
            'note'         => $d->note,
        ])->values(),
        'currency'   => $currency ?? 'S/',
        'splitUrl'   => route('pos.split', $order->id),
        'backUrl'    => route('pos.order', $order->table_id),
        'csrfToken'  => csrf_token(),
    ]) }}"
    class="w-full"
>
    <div class="flex items-center justify-center py-24 text-slate-400">
        <div class="w-5 h-5 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin mr-3"></div>
        <span class="font-bold">Cargando división de cuenta…</span>
    </div>
</div>
@endsection