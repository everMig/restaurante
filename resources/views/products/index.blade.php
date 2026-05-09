@extends('layouts.app')

@section('content')
<div class="w-full">
    @php
        // Cargar relaciones si no están cargadas
        if(method_exists($products, 'loadMissing')) {
            $products->loadMissing('category');
        }
        
        // Mapear los items agregando las rutas procesadas por Laravel
        $items = collect($products->items())->map(function($p) {
            $pArray = $p->toArray();
            $pArray['category'] = $p->category;
            $pArray['edit_url'] = route('products.edit', $p->id);
            $pArray['toggle_url'] = route('products.toggle', $p->id);
            $pArray['delete_url'] = route('products.destroy', $p->id);
            $pArray['adjust_url'] = route('products.adjust', $p->id);
            return $pArray;
        });

        $reactProps = [
            'productsData' => $items,
            'csrfToken' => csrf_token(),
            'routes' => [
                'kardex' => route('inventory.logs'),
                'create' => route('products.create'),
            ],
            'currency' => $currency
        ];
    @endphp

    <div
        data-react-component="ProductList"
        data-react-props='@json($reactProps)'
    ></div>

    <!-- Paginación nativa de Laravel -->
    <div class="mt-8 flex justify-center">
        {{ $products->links() }}
    </div>
</div>
@endsection