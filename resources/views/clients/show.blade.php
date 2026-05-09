@extends('layouts.app')

@section('content')
<div class="w-full">
    
    <div class="flex flex-col sm:flex-row items-start sm:items-center mb-8 gap-4">
        <a href="{{ route('clients.index') }}" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-5 rounded-xl shadow-sm border border-slate-200 transition-colors flex items-center gap-2">
            <i class="bi bi-arrow-left"></i> Volver
        </a>
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight">Perfil de Cliente</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Historial y preferencias</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
        
        <!-- Sidebar Perfil -->
        <div class="lg:col-span-4 xl:col-span-3">
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden text-center p-8 relative">
                <div class="mb-4 mt-2 relative inline-block">
                    <div class="w-28 h-28 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-[2rem] flex items-center justify-center text-indigo-600 font-extrabold text-5xl border-4 border-white shadow-lg mx-auto transform -rotate-3">
                        <span class="transform rotate-3">{{ substr($client->name, 0, 1) }}</span>
                    </div>
                    <span class="absolute -bottom-2 -right-2 px-3 py-1 bg-{{ $badgeColor == 'warning' ? 'amber-500 text-white' : ($badgeColor == 'secondary' ? 'slate-500 text-white' : 'indigo-600 text-white') }} rounded-full text-xs font-extrabold shadow-sm border-2 border-white">
                        {{ $rank }}
                    </span>
                </div>

                <h4 class="text-xl font-extrabold text-slate-800 mb-1">{{ $client->name }}</h4>
                <p class="text-slate-500 text-sm font-medium mb-6 flex items-center justify-center gap-1">
                    <i class="bi bi-geo-alt-fill text-rose-500"></i> {{ $client->address ?? 'Sin dirección' }}
                </p>
                
                <div class="bg-slate-50 rounded-2xl p-4 text-left border border-slate-100 space-y-3">
                    <div class="flex justify-between items-center">
                        <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest">Documento</small>
                        <div class="font-extrabold text-slate-700">{{ $client->document_number ?? '-' }}</div>
                    </div>
                    <div class="w-full h-px bg-slate-200"></div>
                    <div class="flex justify-between items-center">
                        <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest">Teléfono</small>
                        <div class="font-extrabold text-slate-700">{{ $client->phone ?? '-' }}</div>
                    </div>
                    <div class="w-full h-px bg-slate-200"></div>
                    <div class="flex justify-between items-center">
                        <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest">Email</small>
                        <div class="font-bold text-slate-600 text-sm max-w-[120px] truncate" title="{{ $client->email }}">{{ $client->email ?? '-' }}</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Contenido Principal -->
        <div class="lg:col-span-8 xl:col-span-9">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 mb-6">
                <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden group">
                    <small class="text-indigo-200 font-bold uppercase tracking-widest text-[10px]">Total Gastado</small>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $currency ?? 'S/' }} {{ number_format($totalSpent, 2) }}</h3>
                    <i class="bi bi-wallet2 absolute -right-4 -bottom-4 text-[6rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
                </div>
                <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-6 text-white shadow-xl shadow-emerald-500/20 relative overflow-hidden group">
                    <small class="text-emerald-200 font-bold uppercase tracking-widest text-[10px]">Visitas Totales</small>
                    <h3 class="text-3xl font-extrabold mt-1">{{ $visitCount }}</h3>
                    <i class="bi bi-door-open absolute -right-4 -bottom-4 text-[6rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
                </div>
                <div class="bg-gradient-to-br from-amber-400 to-amber-500 rounded-3xl p-6 text-amber-900 shadow-xl shadow-amber-500/20 relative overflow-hidden group">
                    <small class="text-amber-800/70 font-bold uppercase tracking-widest text-[10px]">Plato Favorito</small>
                    <h5 class="text-xl font-extrabold mt-1 truncate" title="{{ $favoriteProduct }}">{{ $favoriteProduct }}</h5>
                    <i class="bi bi-star-fill absolute -right-4 -bottom-4 text-[6rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
                </div>
            </div>

            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col h-full max-h-[500px]">
                <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                    <h6 class="font-extrabold text-slate-800 text-lg mb-0 flex items-center gap-2"><i class="bi bi-clock-history text-indigo-500"></i> Historial de Pedidos</h6>
                </div>
                <div class="flex-1 overflow-y-auto p-0">
                    <table class="w-full text-left border-collapse">
                        <thead class="sticky top-0 bg-white/90 backdrop-blur-sm shadow-sm z-10">
                            <tr class="border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                                <th class="px-6 py-4 font-bold">Fecha</th>
                                <th class="px-6 py-4 font-bold">Folio</th>
                                <th class="px-6 py-4 font-bold">Mesa</th>
                                <th class="px-6 py-4 font-bold text-right">Total</th>
                                <th class="px-6 py-4"></th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100">
                            @forelse($orders as $order)
                                <tr class="hover:bg-slate-50/80 transition-colors group">
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-bold text-slate-700">{{ $order->created_at->format('d/m/Y') }}</div>
                                        <small class="text-slate-400 font-medium">{{ $order->created_at->format('H:i') }}</small>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-xs font-bold tracking-wider">
                                            #{{ str_pad($order->id, 5, '0', STR_PAD_LEFT) }}
                                        </span>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap font-extrabold text-slate-800">{{ $order->table->name ?? 'Barra' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right font-extrabold text-slate-800 text-lg">{{ $currency ?? 'S/' }} {{ number_format($order->total, 2) }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right">
                                        <a href="{{ route('sales.ticket', $order->id) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-600 hover:bg-indigo-600 hover:text-white transition-all shadow-sm" title="Ver Ticket">
                                            <i class="bi bi-printer"></i>
                                        </a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-20 text-slate-400 font-medium">Aún no tiene pedidos registrados.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection