@extends('layouts.app')

@section('content')
<div class="w-full">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <a href="{{ route('products.index') }}" class="inline-flex items-center text-slate-500 hover:text-indigo-600 font-bold text-sm mb-2 transition-colors">
                <i class="bi bi-arrow-left mr-1"></i> Volver a Productos
            </a>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-clock-history me-2 text-indigo-500"></i>Kardex de Movimientos</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Auditoría detallada de entradas y salidas de stock</p>
        </div>
        <div class="bg-white p-3 rounded-xl border border-slate-200 shadow-sm flex items-center gap-4 text-sm font-bold text-slate-600">
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-emerald-500 mr-2 shadow-sm shadow-emerald-500/50"></span> Entrada (Compra/Rep.)</div>
            <div class="flex items-center"><span class="w-3 h-3 rounded-full bg-rose-500 mr-2 shadow-sm shadow-rose-500/50"></span> Salida (Venta/Merma)</div>
        </div>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col overflow-hidden">
        <div class="p-0 overflow-x-auto flex-1">
            <table class="w-full text-left border-collapse min-w-[800px]">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="px-6 py-5 font-bold">Fecha / Hora</th>
                        <th class="px-6 py-5 font-bold">Producto</th>
                        <th class="px-6 py-5 font-bold">Tipo Movimiento</th>
                        <th class="px-6 py-5 font-bold">Motivo / Nota</th>
                        <th class="px-6 py-5 font-bold">Usuario</th>
                        <th class="px-6 py-5 font-bold text-center">Cant.</th>
                        <th class="px-6 py-5 font-bold text-center">Saldo</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($logs as $log)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-700">{{ $log->created_at->format('d/m/Y') }}</div>
                                <small class="text-slate-400 font-medium">{{ $log->created_at->format('H:i:s') }}</small>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap font-extrabold text-slate-800 text-[1.05rem]">{{ $log->product->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($log->type == 'sale')
                                    <span class="inline-flex items-center px-3 py-1 bg-indigo-50 text-indigo-700 border border-indigo-200 rounded-lg text-xs font-bold tracking-wider shadow-sm"><i class="bi bi-cart me-1"></i> VENTA POS</span>
                                @elseif($log->type == 'entry')
                                    <span class="inline-flex items-center px-3 py-1 bg-emerald-50 text-emerald-700 border border-emerald-200 rounded-lg text-xs font-bold tracking-wider shadow-sm"><i class="bi bi-box-arrow-in-right me-1"></i> ENTRADA</span>
                                @else
                                    <span class="inline-flex items-center px-3 py-1 bg-rose-50 text-rose-700 border border-rose-200 rounded-lg text-xs font-bold tracking-wider shadow-sm"><i class="bi bi-exclamation-triangle me-1"></i> AJUSTE / MERMA</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-slate-500 text-sm font-medium italic">
                                {{ $log->note ?? '-' }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-8 h-8 rounded-full bg-slate-100 border border-slate-200 text-slate-600 flex items-center justify-center font-bold text-xs shadow-sm">
                                        {{ substr($log->user->name ?? 'S', 0, 1) }}
                                    </div>
                                    <span class="font-bold text-slate-700 text-sm">{{ $log->user->name ?? 'Sistema' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center font-extrabold text-lg {{ $log->quantity > 0 ? 'text-emerald-600' : 'text-rose-600' }}">
                                {{ $log->quantity > 0 ? '+' : '' }}{{ $log->quantity }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <span class="inline-flex items-center justify-center min-w-[2.5rem] py-1.5 px-3 bg-slate-100 text-slate-800 rounded-lg font-extrabold border border-slate-200 shadow-inner">
                                    {{ $log->new_stock }}
                                </span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-24 text-slate-400 font-medium">
                                <i class="bi bi-box-seam text-6xl text-slate-200 block mb-4"></i>
                                <h3 class="text-xl font-extrabold text-slate-700 mb-1">Sin movimientos</h3>
                                <p>No hay movimientos registrados en el Kardex aún.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-6 bg-slate-50/50 border-t border-slate-100">
            {{ $logs->links() }}
        </div>
    </div>
</div>
@endsection