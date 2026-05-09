@extends('layouts.app')

@section('content')
{{-- POS Tabla Map — React Island (Fase 4) --}}
{{-- El div recibe los props desde el controlador y React monta el componente. --}}
{{-- Si JS está desactivado o hay un error, el componente mostrará un estado de carga. --}}
<div
    data-react-component="PosTableMap"
    data-react-props="{{ json_encode([
        'currency' => $currency,
    ]) }}"
    class="w-full flex flex-col h-[calc(100vh-2rem)]"
>
    {{-- Skeleton de carga mientras React hidrata --}}
    <div class="flex items-center justify-center h-full text-slate-400">
        <div class="w-6 h-6 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin mr-3"></div>
        <span class="font-bold">Cargando mapa de mesas…</span>
    </div>
</div>

<style>
    .no-scrollbar::-webkit-scrollbar { display: none; }
    .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
</style>
@endsection