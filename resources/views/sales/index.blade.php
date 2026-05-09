@extends('layouts.app')

@section('content')
<div class="w-full" x-data="{ activeTab: 'sales', expenseModalOpen: false }">
    
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-cash-coin me-2 text-indigo-500"></i>Caja y Movimientos</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Control de Ingresos, Egresos y Cortes de Caja</p>
        </div>
        
        <div class="flex flex-wrap gap-3 items-center w-full xl:w-auto">
            <!-- Formulario Filtro de Fechas -->
            <form action="{{ route('sales.index') }}" method="GET" class="flex items-center gap-2 bg-white p-1.5 rounded-xl border border-slate-200 shadow-sm w-full sm:w-auto">
                <input type="date" name="start_date" class="bg-transparent border-0 text-sm font-bold text-slate-700 outline-none px-2 focus:ring-0" value="{{ $startDate }}">
                <span class="text-slate-300 font-bold">-</span>
                <input type="date" name="end_date" class="bg-transparent border-0 text-sm font-bold text-slate-700 outline-none px-2 focus:ring-0" value="{{ $endDate }}">
                <button type="submit" class="bg-slate-100 hover:bg-slate-200 text-slate-600 p-2 rounded-lg transition-colors">
                    <i class="bi bi-search"></i>
                </button>
            </form>
            
            <a href="{{ route('sales.daily.report', ['start_date' => $startDate, 'end_date' => $endDate]) }}" target="_blank" class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-slate-800 hover:bg-slate-900 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-slate-500/20 transition-all active:scale-95">
                <i class="bi bi-printer"></i> Corte Z
            </a>
            
            <button @click="expenseModalOpen = true" class="flex-1 sm:flex-none flex items-center justify-center gap-2 bg-rose-600 hover:bg-rose-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-rose-500/30 transition-all active:scale-95">
                <i class="bi bi-dash-circle"></i> Registrar Salida
            </button>
        </div>
    </div>

    <!-- Tarjetas de Resumen -->
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">
        <div class="bg-gradient-to-br from-indigo-500 to-indigo-700 rounded-3xl p-6 text-white shadow-xl shadow-indigo-500/20 relative overflow-hidden group">
            <small class="text-indigo-200 font-bold uppercase tracking-widest text-[10px]">Venta Total</small>
            <h3 class="text-3xl font-extrabold mt-1 mb-3">{{ $currency ?? 'S/' }}{{ number_format($totalSales, 2) }}</h3>
            <span class="inline-block bg-white/20 rounded-lg px-3 py-1 text-xs font-bold backdrop-blur-md border border-white/10">{{ $orders->count() }} operaciones</span>
            <i class="bi bi-graph-up-arrow absolute -right-4 -bottom-4 text-[7rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-gradient-to-br from-emerald-500 to-emerald-700 rounded-3xl p-6 text-white shadow-xl shadow-emerald-500/20 relative overflow-hidden group">
            <small class="text-emerald-200 font-bold uppercase tracking-widest text-[10px]">Entrada Efectivo</small>
            <h3 class="text-3xl font-extrabold mt-1 mb-3">{{ $currency ?? 'S/' }}{{ number_format($totalCash, 2) }}</h3>
            <span class="inline-block bg-white/20 rounded-lg px-3 py-1 text-xs font-bold backdrop-blur-md border border-white/10">Dinero Físico</span>
            <i class="bi bi-cash-stack absolute -right-4 -bottom-4 text-[7rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="bg-gradient-to-br from-rose-500 to-rose-700 rounded-3xl p-6 text-white shadow-xl shadow-rose-500/20 relative overflow-hidden group">
            <small class="text-rose-200 font-bold uppercase tracking-widest text-[10px]">Gastos / Salidas</small>
            <h3 class="text-3xl font-extrabold mt-1 mb-3">{{ $currency ?? 'S/' }}{{ number_format($totalExpenses, 2) }}</h3>
            <span class="inline-block bg-white/20 rounded-lg px-3 py-1 text-xs font-bold backdrop-blur-md border border-white/10">{{ $expenses->count() }} movimientos</span>
            <i class="bi bi-cart-dash absolute -right-4 -bottom-4 text-[7rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
        </div>

        <div class="rounded-3xl p-6 shadow-xl relative overflow-hidden group {{ $balance >= 0 ? 'bg-gradient-to-br from-slate-800 to-slate-900 text-white shadow-slate-900/20' : 'bg-gradient-to-br from-amber-400 to-amber-500 text-amber-900 shadow-amber-500/20' }}">
            <small class="font-bold uppercase tracking-widest text-[10px] {{ $balance >= 0 ? 'text-slate-400' : 'text-amber-800/70' }}">Dinero en Caja (Balance)</small>
            <h3 class="text-3xl font-extrabold mt-1 mb-3">{{ $currency ?? 'S/' }}{{ number_format($balance, 2) }}</h3>
            <span class="inline-block rounded-lg px-3 py-1 text-xs font-bold border {{ $balance >= 0 ? 'bg-white/10 border-white/10' : 'bg-white/30 border-white/20' }}">Efectivo - Gastos</span>
            <i class="bi bi-safe absolute -right-4 -bottom-4 text-[7rem] opacity-10 pointer-events-none group-hover:scale-110 transition-transform duration-500"></i>
        </div>
    </div>

    <!-- Pestañas y Tablas -->
    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="border-b border-slate-100 bg-slate-50/50 p-2 flex gap-2">
            <button @click="activeTab = 'sales'" 
                    :class="activeTab === 'sales' ? 'bg-white text-indigo-700 shadow-sm font-extrabold border border-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100 font-bold border border-transparent'"
                    class="px-6 py-3 rounded-2xl text-sm transition-all flex items-center gap-2">
                <i class="bi bi-receipt"></i> Historial de Ventas
            </button>
            <button @click="activeTab = 'expenses'" 
                    :class="activeTab === 'expenses' ? 'bg-white text-rose-600 shadow-sm font-extrabold border border-slate-200' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-100 font-bold border border-transparent'"
                    class="px-6 py-3 rounded-2xl text-sm transition-all flex items-center gap-2">
                <i class="bi bi-journal-minus"></i> Historial de Gastos
            </button>
        </div>
        
        <div class="p-0">
            <!-- Ventas -->
            <div x-show="activeTab === 'sales'" x-transition.opacity class="overflow-x-auto min-h-[300px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-5 font-bold">Hora</th>
                            <th class="px-6 py-5 font-bold">Folio</th>
                            <th class="px-6 py-5 font-bold">Cliente</th>
                            <th class="px-6 py-5 font-bold">Mesa</th>
                            <th class="px-6 py-5 font-bold">Método</th>
                            <th class="px-6 py-5 font-bold text-right">Total</th>
                            <th class="px-6 py-5 font-bold text-center">Ticket</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($orders as $order)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium">{{ $order->created_at->format('H:i') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap font-extrabold text-slate-800 text-[1.05rem]">#{{ str_pad($order->id, 6, '0', STR_PAD_LEFT) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="font-bold text-slate-700">{{ $order->client_name }}</div>
                                <div class="text-xs text-slate-400 font-bold tracking-wider mt-0.5">{{ $order->document_type }}</div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block px-4 py-1.5 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-xs font-bold shadow-sm">
                                    {{ $order->table->name ?? 'Barra' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($order->payment_method == 'cash')
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm"><i class="bi bi-cash me-1"></i> Efectivo</span>
                                @else
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-indigo-50 text-indigo-600 border border-indigo-200 shadow-sm"><i class="bi bi-credit-card me-1"></i> Tarjeta</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-extrabold text-slate-800 text-lg">
                                {{ number_format($order->total, 2) }}
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                <a href="{{ route('sales.ticket', $order->id) }}" target="_blank" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-100 text-slate-600 hover:bg-slate-800 hover:text-white transition-all shadow-sm" title="Imprimir Ticket">
                                    <i class="bi bi-printer"></i>
                                </a>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="text-center py-20 text-slate-400 font-medium"><i class="bi bi-receipt text-5xl text-slate-200 mb-4 block"></i>No hay ventas registradas en este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Gastos -->
            <div x-show="activeTab === 'expenses'" style="display: none;" x-transition.opacity class="overflow-x-auto min-h-[300px]">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-5 font-bold">Hora</th>
                            <th class="px-6 py-5 font-bold">Descripción / Motivo</th>
                            <th class="px-6 py-5 font-bold">Registrado Por</th>
                            <th class="px-6 py-5 font-bold text-right">Monto</th>
                            <th class="px-6 py-5 font-bold text-right">Acción</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($expenses as $expense)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium">{{ $expense->created_at->format('d/m H:i') }}</td>
                            <td class="px-6 py-4 font-extrabold text-slate-800 text-[1.05rem]">{{ $expense->description }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium text-sm"><i class="bi bi-person me-1 text-slate-400"></i> {{ $expense->user->name }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right font-extrabold text-rose-600 text-lg">-{{ number_format($expense->amount, 2) }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <form action="{{ route('expenses.destroy', $expense->id) }}" method="POST" @submit="window.confirmAction($el, '¿Estás seguro de anular este gasto? El dinero regresará a la caja automáticamente.', $event)">
                                    @csrf @method('DELETE')
                                    <button class="text-rose-400 hover:text-white bg-rose-50 hover:bg-rose-500 p-2.5 rounded-xl transition-all border border-rose-100 shadow-sm" title="Anular Gasto">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="5" class="text-center py-20 text-slate-400 font-medium"><i class="bi bi-journal-x text-5xl text-slate-200 mb-4 block"></i>No hay gastos registrados en este periodo.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Modal Gastos -->
    <div x-show="expenseModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="expenseModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="expenseModalOpen = false"></div>
        <form action="{{ route('expenses.store') }}" method="POST" x-show="expenseModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden">
            @csrf
            <div class="px-6 py-5 border-b border-rose-100 flex justify-between items-center bg-rose-600">
                <h5 class="font-extrabold text-lg text-white">Registrar Salida de Dinero</h5>
                <button type="button" @click="expenseModalOpen = false" class="text-white/70 hover:text-white bg-white/10 rounded-xl p-2 transition-colors"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6 space-y-5">
                <div class="bg-amber-50 border border-amber-200 text-amber-800 px-4 py-3 rounded-2xl flex items-start gap-3 shadow-sm">
                    <i class="bi bi-exclamation-triangle-fill text-amber-500 mt-0.5"></i>
                    <p class="text-sm font-medium">Esta acción restará el dinero indicado del <strong class="font-extrabold text-amber-900">Efectivo en Caja</strong> de forma inmediata.</p>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Descripción del Gasto</label>
                    <input type="text" name="description" required placeholder="Ej: Compra de hielo, Pago a proveedor..." class="w-full rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Monto a Retirar</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 font-extrabold text-slate-400 text-lg">{{ $currency ?? 'S/' }}</span>
                        <input type="number" step="0.01" name="amount" required placeholder="0.00" class="w-full rounded-xl border border-slate-300 focus:border-rose-500 focus:ring-2 focus:ring-rose-500/20 shadow-sm pl-12 pr-4 py-3 outline-none transition-all text-2xl font-extrabold text-rose-600">
                    </div>
                </div>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="expenseModalOpen = false" class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors shadow-sm">Cancelar</button>
                <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-rose-600 hover:bg-rose-700 shadow-lg shadow-rose-500/30 transition-all active:scale-95">Confirmar Salida</button>
            </div>
        </form>
    </div>
</div>
@endsection