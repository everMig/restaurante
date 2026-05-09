@extends('layouts.app')

@section('content')
{{-- POS Order — React Island (Fase 4) --}}
{{-- Pasa todos los datos del controlador como props JSON. --}}
{{-- El carrito, modales, y búsqueda son completamente React. --}}
<div
    data-react-component="PosOrder"
    data-react-props="{{ json_encode([
        'tableId'     => $table->id,
        'tableName'   => $table->name,
        'areaName'    => $table->area->name ?? '',
        'currency'    => $currency,
        'userName'    => auth()->user()->name,
        'userInitial' => strtoupper(substr(auth()->user()->name, 0, 1)),
    ]) }}"
    class="w-full"
>
    {{-- Skeleton de carga mientras React hidrata --}}
    <div class="w-full h-[calc(100vh-2rem)] flex items-center justify-center bg-slate-100 rounded-3xl">
        <div class="w-6 h-6 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin mr-3"></div>
        <span class="font-bold text-slate-500">Cargando POS…</span>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection