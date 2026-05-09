@extends('layouts.app')

@section('content')
<div class="w-full">
    @php
        $dashboardProps = [
            "currency" => $currency ?? "S/",
            "totalSalesToday" => $totalSalesToday ?? 0,
            "ordersCountToday" => $ordersCountToday ?? 0,
            "activeTables" => $activeTables ?? 0,
            "lowStockProducts" => $lowStockProducts ?? 0,
            "chartLabels" => $chartLabels ?? [],
            "chartValues" => $chartValues ?? [],
        ];
    @endphp
    
    <!-- Componente React (Isla) -->
    <div
        data-react-component="DashboardPulse"
        data-react-props='@json($dashboardProps)'
    ></div>
    
    <!-- Cabecera de Búsqueda y Botón -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4 mt-6">
        <div class="relative w-full max-w-md">
            <i class="bi bi-search absolute left-4 top-1/2 -translate-y-1/2 text-slate-400"></i>
            <input type="text" class="w-full pl-11 pr-4 py-3 rounded-full border border-slate-200 shadow-sm bg-white focus:ring-2 focus:ring-indigo-500 focus:border-indigo-500 outline-none text-slate-700 transition-shadow" placeholder="Buscar en el sistema...">
        </div>

        <div class="flex gap-4 items-center w-full md:w-auto">
            <div class="text-right hidden md:block">
                <h5 class="font-extrabold text-slate-800 text-lg">Panel de Control</h5>
                <small class="text-slate-500 font-bold uppercase tracking-wider text-xs">{{ \Carbon\Carbon::now()->locale('es')->isoFormat('dddd, D [de] MMMM') }}</small>
            </div>
            <a href="{{ route('pos.index') }}" class="flex-1 md:flex-none flex items-center justify-center gap-2 bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
                <i class="bi bi-basket2-fill text-lg"></i> <span>Nuevo Pedido</span>
            </a>
        </div>
    </div>

    <!-- Tarjetas de Resumen (Legacy, convertidas a Tailwind) -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-gradient-to-br from-blue-500 to-blue-700 rounded-3xl p-6 text-white shadow-xl shadow-blue-500/20 relative overflow-hidden">
            <h6 class="font-semibold text-blue-100 mb-2">Venta de Hoy</h6>
            <h2 class="text-4xl font-extrabold mb-3">{{ $currency ?? 'S/' }}{{ number_format($totalSalesToday ?? 0, 2) }}</h2>
            <span class="inline-block bg-white/20 rounded-full px-4 py-1 text-sm font-bold backdrop-blur-md border border-white/10">{{ $ordersCountToday ?? 0 }} órdenes</span>
            <i class="bi bi-wallet2 absolute -right-4 top-1/2 -translate-y-1/2 text-[8rem] opacity-10 pointer-events-none transform -rotate-12"></i>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-6 text-white shadow-xl shadow-emerald-500/20 relative overflow-hidden">
            <h6 class="font-semibold text-emerald-100 mb-2">Mesas Activas</h6>
            <h2 class="text-4xl font-extrabold mb-3">{{ $activeTables ?? 0 }}</h2>
            <span class="inline-block bg-white/20 rounded-full px-4 py-1 text-sm font-bold backdrop-blur-md border border-white/10">En servicio</span>
            <i class="bi bi-shop-window absolute -right-4 top-1/2 -translate-y-1/2 text-[8rem] opacity-10 pointer-events-none transform -rotate-12"></i>
        </div>

        <div class="bg-gradient-to-br from-rose-500 to-rose-700 rounded-3xl p-6 text-white shadow-xl shadow-rose-500/20 relative overflow-hidden">
            <h6 class="font-semibold text-rose-100 mb-2">Alertas Stock</h6>
            <h2 class="text-4xl font-extrabold mb-3">{{ $lowStockProducts ?? 0 }}</h2>
            <span class="inline-block bg-white/20 rounded-full px-4 py-1 text-sm font-bold backdrop-blur-md border border-white/10">Productos bajos</span>
            <i class="bi bi-box-seam absolute -right-4 top-1/2 -translate-y-1/2 text-[8rem] opacity-10 pointer-events-none transform -rotate-12"></i>
        </div>
    </div>

    <!-- Secciones Inferiores -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Estado de Salones -->
        <div class="lg:col-span-2 bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-slate-50/50">
                <h5 class="font-extrabold text-slate-800 text-xl">Estado de Salones</h5>
                <div class="flex gap-2 text-xs font-bold uppercase tracking-wider">
                    <span class="bg-emerald-50 text-emerald-600 border border-emerald-200 px-3 py-1.5 rounded-full">Disponible</span>
                    <span class="bg-rose-50 text-rose-600 border border-rose-200 px-3 py-1.5 rounded-full">Ocupada</span>
                </div>
            </div>
            
            <div class="p-6 flex-1 bg-white" x-data="{ activeTab: '{{ count($areas ?? []) > 0 ? $areas[0]->id : 0 }}' }">
                @if(isset($areas) && count($areas) > 0)
                    <!-- Tabs (Alpine.js) -->
                    <div class="flex flex-wrap gap-2 mb-8 p-1 bg-slate-100 rounded-2xl w-max">
                        @foreach($areas as $area)
                            <button @click="activeTab = '{{ $area->id }}'" 
                                    :class="activeTab === '{{ $area->id }}' ? 'bg-white text-indigo-600 shadow-sm' : 'text-slate-500 hover:text-slate-700'"
                                    class="px-6 py-2 rounded-xl font-bold text-sm transition-all">
                                {{ $area->name }}
                            </button>
                        @endforeach
                    </div>

                    <!-- Contenido Tabs -->
                    <div class="relative min-h-[300px]">
                        @foreach($areas as $area)
                            <div x-show="activeTab === '{{ $area->id }}'" 
                                 x-transition:enter="transition ease-out duration-200 delay-100"
                                 x-transition:enter-start="opacity-0 translate-y-2"
                                 x-transition:enter-end="opacity-100 translate-y-0"
                                 x-transition:leave="transition ease-in duration-100 absolute inset-0"
                                 x-transition:leave-start="opacity-100 translate-y-0"
                                 x-transition:leave-end="opacity-0 translate-y-2"
                                 class="grid grid-cols-2 sm:grid-cols-3 xl:grid-cols-4 gap-4 w-full">
                                
                                @foreach($area->tables as $table)
                                    @php $isBusy = $table->orders->count() > 0; @endphp
                                    <a href="{{ route('pos.order', $table->id) }}" class="block group h-full">
                                        <div class="h-full rounded-2xl p-5 text-center transition-all duration-300 border {{ $isBusy ? 'bg-rose-50/50 border-rose-100 group-hover:shadow-lg group-hover:shadow-rose-500/20 group-hover:-translate-y-1' : 'bg-slate-50 border-slate-200 group-hover:bg-white group-hover:shadow-lg group-hover:shadow-slate-200/50 group-hover:-translate-y-1' }}">
                                            <div class="mb-4">
                                                <i class="bi {{ $isBusy ? 'bi-person-workspace text-rose-500' : 'bi-check-circle-fill text-emerald-500' }} text-4xl"></i>
                                            </div>
                                            <h6 class="font-extrabold text-slate-800 text-lg mb-2">{{ $table->name }}</h6>
                                            @if($isBusy)
                                                <span class="inline-block bg-rose-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-sm">{{ $currency }}{{ number_format($table->orders->first()->total, 2) }}</span>
                                            @else
                                                <span class="inline-block bg-slate-200 text-slate-500 text-xs font-bold px-3 py-1 rounded-full">Libre</span>
                                            @endif
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-16">
                        <i class="bi bi-grid-3x3-gap text-6xl text-slate-200 mb-4 block"></i>
                        <h4 class="text-slate-500 font-bold text-lg mb-1">Sin áreas configuradas</h4>
                        <p class="text-slate-400 text-sm">Crea un salón o terraza para comenzar a gestionar mesas.</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Top Productos -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h5 class="font-extrabold text-slate-800 text-xl">🏆 Más Vendidos</h5>
            </div>
            <div class="flex-1 overflow-y-auto p-2">
                <div class="divide-y divide-slate-100">
                    @forelse($topProducts ?? [] as $product)
                        <div class="flex items-center p-4 hover:bg-slate-50 transition-colors rounded-2xl group">
                            <div class="relative mr-4 shrink-0">
                                @if($product->image)
                                    <img src="{{ asset('storage/'.$product->image) }}" class="w-14 h-14 rounded-2xl object-cover shadow-sm border border-slate-200 transition-transform group-hover:scale-105">
                                @else
                                    <div class="w-14 h-14 rounded-2xl bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400 shadow-sm transition-transform group-hover:scale-105">
                                        <i class="bi bi-image text-xl"></i>
                                    </div>
                                @endif
                                <span class="absolute -top-2 -left-2 w-6 h-6 bg-indigo-600 text-white text-xs font-bold flex items-center justify-center rounded-full border-2 border-white shadow-sm">
                                    {{ $loop->iteration }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h6 class="font-bold text-slate-800 truncate">{{ $product->name }}</h6>
                                <span class="text-sm text-slate-500 font-medium">{{ $product->total_qty }} unidades</span>
                            </div>
                            <div class="ml-4 text-amber-400 text-xl">
                                <i class="bi bi-trophy-fill opacity-75 group-hover:opacity-100 transition-opacity"></i>
                            </div>
                        </div>
                    @empty
                        <div class="text-center py-12">
                            <i class="bi bi-trophy text-5xl text-slate-200 mb-3 block"></i>
                            <span class="text-slate-400 font-medium text-sm">Sin datos de ventas aún.</span>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</div>

@includeIf('products.create_modal')
@endsection