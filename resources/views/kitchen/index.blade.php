@extends('layouts.app')

@section('content')
{{-- KitchenDisplay — React Island (Fase 4) --}}
{{-- React gestiona el polling, estados de platos y el reloj. --}}
{{-- Ya no hay window.location.reload() cada 15s. --}}
<div
    data-react-component="KitchenDisplay"
    data-react-props="{}"
    class="w-full"
>
    {{-- Skeleton de carga inicial --}}
    <div class="flex items-center justify-center py-32 text-slate-400">
        <div class="w-6 h-6 border-2 border-rose-400 border-t-transparent rounded-full animate-spin mr-3"></div>
        <span class="font-bold">Cargando monitor de cocina…</span>
    </div>
</div>
@endsection