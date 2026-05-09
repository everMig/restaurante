@extends('layouts.app')

@section('content')
<div class="w-full">
    <div class="flex items-center mb-8">
        <a href="{{ route('products.index') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl shadow-sm border border-slate-200 transition-colors mr-4">
            <i class="bi bi-arrow-left"></i>
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Editar Producto</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Gestiona detalles y receta</p>
        </div>
    </div>

    <form action="{{ route('products.update', $product->id) }}" method="POST" enctype="multipart/form-data" class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        @csrf
        @method('PUT')

        <div class="lg:col-span-8">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden h-full">
                <div class="p-8">
                    <h6 class="font-extrabold text-indigo-600 mb-6 flex items-center gap-2 text-lg"><i class="bi bi-info-circle"></i> Información General</h6>

                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre del Producto</label>
                        <input type="text" name="name" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all text-lg font-bold text-slate-800" value="{{ $product->name }}" required>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Categoría</label>
                            <select name="category_id" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-white font-bold text-slate-700" required>
                                @foreach($categories as $cat)
                                    <option value="{{ $cat->id }}" {{ $product->category_id == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Código de Barras</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold"><i class="bi bi-upc-scan"></i></span>
                                <input type="text" name="barcode" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm pl-11 pr-4 py-3 outline-none transition-all font-bold text-slate-700" value="{{ $product->barcode }}" placeholder="Escanear...">
                            </div>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Precio ({{ $currency }})</label>
                            <div class="relative">
                                <span class="absolute left-4 top-1/2 -translate-y-1/2 font-extrabold text-slate-400">{{ $currency }}</span>
                                <input type="number" step="0.01" name="price" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm pl-12 pr-4 py-3 outline-none transition-all font-extrabold text-indigo-600 text-lg" value="{{ $product->price }}" required>
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Stock Actual (Lectura)</label>
                            <input type="text" class="w-full rounded-xl border border-slate-200 bg-slate-50 px-4 py-3 outline-none font-bold text-slate-500 cursor-not-allowed shadow-inner" value="{{ $product->stock }}" readonly>
                            <small class="text-slate-500 font-medium text-xs mt-1 block">Si tiene receta, el stock dependerá de los insumos.</small>
                        </div>
                    </div>

                    <div class="mb-8 bg-slate-50 p-5 rounded-2xl border border-slate-200 shadow-sm">
                        <label class="flex items-center cursor-pointer">
                            <div class="relative">
                                <input type="checkbox" name="is_saleable" id="saleableCheck" {{ $product->is_saleable ? 'checked' : '' }} class="sr-only">
                                <div class="block bg-slate-300 w-14 h-8 rounded-full transition-colors" id="toggleBg"></div>
                                <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform shadow-sm" id="toggleDot"></div>
                            </div>
                            <div class="ml-4">
                                <div class="font-extrabold text-slate-800">Disponible para Venta en POS</div>
                                <div class="text-sm text-slate-500 font-medium mt-0.5">Si desmarcas esto, el producto servirá como <strong>Insumo</strong> para recetas, pero no aparecerá en ventas.</div>
                            </div>
                        </label>
                    </div>

                    <div class="mb-4">
                        <label class="block text-sm font-bold text-slate-700 mb-2">Imagen</label>
                        <div class="flex flex-col sm:flex-row items-center gap-6">
                            @if($product->image)
                                <div class="shrink-0 p-1.5 bg-white border border-slate-200 rounded-2xl shadow-sm">
                                    <img src="{{ asset('storage/'.$product->image) }}" class="rounded-xl w-24 h-24 object-cover">
                                </div>
                            @endif
                            <div class="flex-1 w-full border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:bg-slate-50 transition-colors">
                                <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer outline-none">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="lg:col-span-4">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden h-full flex flex-col">
                <div class="bg-indigo-600 text-white p-6 flex justify-between items-center shadow-sm z-10">
                    <h6 class="mb-0 font-extrabold text-lg flex items-center gap-2"><i class="bi bi-basket"></i> Receta / Insumos</h6>
                    <button type="button" class="bg-white/20 hover:bg-white/30 text-white font-bold py-1.5 px-3 rounded-lg text-sm transition-colors" onclick="addIngredient()">+ Agregar</button>
                </div>
                <div class="p-6 flex-1 bg-slate-50/50 overflow-y-auto max-h-[500px]">
                    <p class="text-sm text-slate-500 font-medium mb-6 bg-white p-4 rounded-xl border border-slate-200 shadow-sm text-center">Selecciona los insumos que componen este plato. Al venderlo, se descontarán automáticamente.</p>
                    
                    <div id="ingredients-list" class="space-y-3">
                        @foreach($product->ingredients as $ingredient)
                            <div class="flex items-center bg-white rounded-xl border border-slate-200 shadow-sm overflow-hidden" id="row-{{ $ingredient->id }}">
                                <span class="bg-slate-50 text-slate-700 font-bold px-4 py-3 text-sm w-3/5 border-r border-slate-200 truncate" title="{{ $ingredient->name }}">{{ $ingredient->name }}</span>
                                <input type="number" step="0.01" name="ingredients[{{ $ingredient->id }}]" value="{{ $ingredient->pivot->quantity }}" class="w-1/4 outline-none px-2 py-3 text-center font-extrabold text-indigo-600 border-r border-slate-200 focus:bg-indigo-50 transition-colors" placeholder="Cant.">
                                <button type="button" class="w-1/6 flex items-center justify-center text-rose-400 hover:bg-rose-50 hover:text-rose-600 transition-colors py-3" onclick="document.getElementById('row-{{ $ingredient->id }}').remove()"><i class="bi bi-x-lg font-extrabold"></i></button>
                            </div>
                        @endforeach
                    </div>

                    <div class="hidden" id="ingredient-select-template">
                        <div class="flex items-center bg-white rounded-xl border border-indigo-200 shadow-sm overflow-hidden ingredient-row">
                            <select class="outline-none px-3 py-3 text-sm w-3/5 border-r border-slate-200 font-bold text-slate-700 bg-transparent focus:bg-indigo-50 transition-colors" onchange="setIngredientName(this)">
                                <option value="">- Insumo -</option>
                                @foreach($ingredients as $ing)
                                    <option value="{{ $ing->id }}">{{ $ing->name }}</option>
                                @endforeach
                            </select>
                            <input type="number" step="0.01" class="w-1/4 outline-none px-2 py-3 text-center font-extrabold text-indigo-600 border-r border-slate-200 focus:bg-indigo-50 transition-colors" placeholder="Cant.">
                            <button type="button" class="w-1/6 flex items-center justify-center bg-rose-50 text-rose-500 hover:bg-rose-500 hover:text-white transition-colors py-3" onclick="this.parentElement.remove()"><i class="bi bi-trash font-bold"></i></button>
                        </div>
                    </div>

                </div>
                <div class="p-6 bg-white border-t border-slate-100 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.05)] z-10">
                    <button type="submit" class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-3.5 rounded-xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex justify-center items-center gap-2">
                        <i class="bi bi-check-lg text-xl font-extrabold"></i> Guardar Cambios
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<style>
    input:checked ~ #toggleBg { background-color: #4f46e5; }
    input:checked ~ #toggleDot { transform: translateX(100%); }
</style>

<script>
    function addIngredient() {
        let container = document.getElementById('ingredients-list');
        let template = document.getElementById('ingredient-select-template').innerHTML;
        let div = document.createElement('div');
        div.className = "mt-3";
        div.innerHTML = template;
        container.appendChild(div);
    }

    function setIngredientName(select) {
        let row = select.parentElement;
        let inputQty = row.querySelector('input[type="number"]');
        if (select.value) {
            inputQty.name = "ingredients[" + select.value + "]";
            inputQty.required = true;
        }
    }
</script>
@endsection