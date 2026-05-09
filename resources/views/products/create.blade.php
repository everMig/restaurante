@extends('layouts.app')

@section('content')
<div class="w-full flex justify-center">
    <div class="w-full max-w-4xl">
        <div class="flex items-center mb-8">
            <a href="{{ route('products.index') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-4 rounded-xl shadow-sm border border-slate-200 transition-colors mr-4">
                <i class="bi bi-arrow-left"></i>
            </a>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Nuevo Producto</h2>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <form action="{{ route('products.store') }}" method="POST" enctype="multipart/form-data" class="p-8">
                @csrf
                
                <h6 class="font-extrabold text-indigo-600 mb-6 flex items-center gap-2 text-lg"><i class="bi bi-box-seam"></i> Información General</h6>

                <div class="mb-6">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nombre del Producto <span class="text-rose-500">*</span></label>
                    <input type="text" name="name" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all text-lg font-bold text-slate-800" placeholder="Ej: Lomo Saltado" required autofocus>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Categoría <span class="text-rose-500">*</span></label>
                        <select name="category_id" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-white font-bold text-slate-700" required>
                            <option value="" selected disabled>-- Seleccionar --</option>
                            @foreach($categories as $cat)
                                <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Código de Barras (Opcional)</label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400 font-bold"><i class="bi bi-upc-scan"></i></span>
                            <input type="text" name="barcode" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm pl-11 pr-4 py-3 outline-none transition-all font-bold text-slate-700" placeholder="Escanear o escribir...">
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Precio de Venta <span class="text-rose-500">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 font-extrabold text-slate-400">{{ $currency }}</span>
                            <input type="number" step="0.01" name="price" value="{{ old('price') }}" required class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm pl-12 pr-4 py-3 outline-none transition-all bg-white font-extrabold text-indigo-700 text-lg">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Stock Inicial</label>
                        <input type="number" name="stock" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all font-bold text-slate-700" placeholder="0">
                        <small class="text-slate-500 font-medium text-xs mt-1 block">Se creará un registro de entrada en el Kardex.</small>
                    </div>
                </div>

                <div class="mb-8 bg-slate-50 p-5 rounded-2xl border border-slate-200 shadow-sm">
                    <label class="flex items-center cursor-pointer">
                        <div class="relative">
                            <input type="checkbox" name="is_saleable" id="saleableCheck" checked class="sr-only">
                            <div class="block bg-slate-300 w-14 h-8 rounded-full transition-colors" id="toggleBg"></div>
                            <div class="dot absolute left-1 top-1 bg-white w-6 h-6 rounded-full transition-transform shadow-sm" id="toggleDot"></div>
                        </div>
                        <div class="ml-4">
                            <div class="font-extrabold text-slate-800">Disponible para Venta en POS</div>
                            <div class="text-sm text-slate-500 font-medium mt-0.5">Si desmarcas esto, el producto servirá como <strong>Insumo</strong> para recetas, pero no aparecerá en ventas.</div>
                        </div>
                    </label>
                </div>

                <div class="mb-8">
                    <label class="block text-sm font-bold text-slate-700 mb-2">Imagen (Opcional)</label>
                    <div class="border-2 border-dashed border-slate-300 rounded-2xl p-6 text-center hover:bg-slate-50 transition-colors">
                        <input type="file" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer outline-none">
                    </div>
                </div>

                <div class="flex justify-end pt-6 border-t border-slate-100">
                    <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3.5 px-8 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
                        <i class="bi bi-save"></i> Guardar Producto
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<style>
    input:checked ~ #toggleBg { background-color: #4f46e5; }
    input:checked ~ #toggleDot { transform: translateX(100%); }
</style>
@endsection