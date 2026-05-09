@extends('layouts.app')

@section('content')
<div class="w-full" x-data="{ createModalOpen: false }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Gestión de Categorías</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Administra las categorías de tus productos para el punto de venta</p>
        </div>
        <button type="button" @click="createModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Nueva Categoría
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="px-6 py-5 font-bold">Imagen</th>
                        <th class="px-6 py-5 font-bold">Nombre</th>
                        <th class="px-6 py-5 font-bold">Estado</th>
                        <th class="px-6 py-5 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($categories as $category)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->image)
                                    <img src="{{ asset('storage/' . $category->image) }}" class="w-14 h-14 rounded-2xl object-cover shadow-sm border border-slate-200 transition-transform group-hover:scale-105">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 text-slate-400 flex justify-center items-center shadow-sm transition-transform group-hover:scale-105">
                                        <i class="bi bi-image text-xl"></i>
                                    </div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-extrabold text-slate-800 text-lg">
                                {{ $category->name }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($category->is_active)
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span> Activo
                                    </span>
                                @else
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-slate-50 text-slate-500 border border-slate-200 shadow-sm">
                                        <span class="w-1.5 h-1.5 rounded-full bg-slate-400 mr-2"></span> Inactivo
                                    </span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                <form action="{{ route('categories.destroy', $category) }}" method="POST" class="inline-block">
                                    @csrf
                                    @method('DELETE')
                                    <button type="button" @click="window.confirmAction($el, '¿Estás seguro de eliminar esta categoría?')" class="text-rose-500 hover:text-white bg-rose-50 hover:bg-rose-500 p-2.5 rounded-xl transition-all border border-rose-100 hover:shadow-md hover:shadow-rose-500/20">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-6 py-16 text-center">
                                <i class="bi bi-tags text-5xl text-slate-200 mb-4 block"></i>
                                <span class="text-slate-400 font-medium">No hay categorías registradas aún.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal (Alpine) -->
    <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <div x-show="createModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="createModalOpen = false"></div>
        
        <div x-show="createModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h5 class="font-extrabold text-lg text-slate-800">Nueva Categoría</h5>
                <button @click="createModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-white rounded-xl p-2 shadow-sm border border-slate-200 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            
            <form action="{{ route('categories.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="p-6 space-y-5">
                    <div>
                        <label for="name" class="block text-sm font-bold text-slate-700 mb-2">Nombre de la Categoría</label>
                        <input type="text" id="name" name="name" required placeholder="Ej: Bebidas Calientes" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 text-sm transition-all outline-none">
                    </div>
                    <div>
                        <label for="image" class="block text-sm font-bold text-slate-700 mb-2">Imagen <span class="text-slate-400 font-normal">(Opcional)</span></label>
                        <div class="border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:bg-slate-50 transition-colors">
                            <input type="file" id="image" name="image" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer outline-none">
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 hover:text-slate-800 transition-colors shadow-sm">Cancelar</button>
                    <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">Guardar Categoría</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection