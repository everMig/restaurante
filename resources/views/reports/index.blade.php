@extends('layouts.app')

@section('content')
<div class="w-full">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-bar-chart-fill me-2 text-indigo-500"></i>Reportes Gerenciales</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Análisis detallado de rendimiento comercial</p>
        </div>
        
        <form action="{{ route('reports.index') }}" method="GET" class="flex flex-wrap sm:flex-nowrap items-end gap-3 bg-white p-3 rounded-2xl shadow-sm border border-slate-100 w-full md:w-auto">
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-bold text-slate-500 mb-1">Desde</label>
                <input type="date" name="start_date" value="{{ $startDate }}" class="w-full sm:w-auto bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 rounded-xl px-3 py-2 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            </div>
            <div class="w-full sm:w-auto">
                <label class="block text-xs font-bold text-slate-500 mb-1">Hasta</label>
                <input type="date" name="end_date" value="{{ $endDate }}" class="w-full sm:w-auto bg-slate-50 border border-slate-200 text-sm font-bold text-slate-700 rounded-xl px-3 py-2 outline-none focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 transition-all">
            </div>
            <button class="w-full sm:w-auto bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2 px-4 rounded-xl shadow-md shadow-indigo-500/30 transition-all active:scale-95 flex items-center justify-center gap-2">
                <i class="bi bi-filter"></i> Analizar
            </button>
        </form>
    </div>

    <!-- Gráficos -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-6">
        
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h5 class="font-extrabold text-slate-800 text-lg">Ingresos por Categoría</h5>
            </div>
            <div class="p-6 flex-1 flex justify-center items-center">
                <div class="relative w-full" style="height: 320px;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 flex flex-col overflow-hidden">
            <div class="p-6 border-b border-slate-100 bg-slate-50/50">
                <h5 class="font-extrabold text-slate-800 text-lg">Ranking de Ventas por Personal</h5>
            </div>
            <div class="p-6 flex-1 flex justify-center items-center">
                <div class="relative w-full" style="height: 320px;">
                    <canvas id="waiterChart"></canvas>
                </div>
            </div>
        </div>

    </div>

    <!-- Tablas -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        
        <!-- Top Productos -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-emerald-100 bg-gradient-to-r from-emerald-50 to-white text-emerald-900">
                <h6 class="font-extrabold text-lg mb-0 flex items-center gap-2"><i class="bi bi-trophy-fill text-amber-500 text-xl"></i> Top 5: Platos Estrella</h6>
            </div>
            <div class="flex-1 overflow-x-auto p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-4 font-bold">Producto</th>
                            <th class="px-6 py-4 font-bold text-center">Cant. Vendida</th>
                            <th class="px-6 py-4 font-bold text-right">Ingresos</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($topProducts as $prod)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4 font-extrabold text-slate-800 text-[1.05rem]">{{ $prod->name }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="inline-flex items-center justify-center min-w-[2.5rem] px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-sm font-bold shadow-sm">{{ $prod->qty }}</span>
                                </td>
                                <td class="px-6 py-4 text-right font-extrabold text-emerald-600 text-lg">
                                    {{ $currency }}{{ number_format($prod->revenue, 2) }}
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="px-6 py-12 text-center text-slate-400 font-medium">Sin datos para este periodo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Peores Productos -->
        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
            <div class="p-6 border-b border-amber-100 bg-gradient-to-r from-amber-50 to-white text-amber-900">
                <h6 class="font-extrabold text-lg mb-0 flex items-center gap-2"><i class="bi bi-exclamation-triangle-fill text-amber-500 text-xl"></i> Ojo: Menos Vendidos</h6>
            </div>
            <div class="flex-1 overflow-x-auto p-0">
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-slate-50/50 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                            <th class="px-6 py-4 font-bold">Producto</th>
                            <th class="px-6 py-4 font-bold text-right">Cant. Vendida</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($worstProducts as $prod)
                            <tr class="hover:bg-slate-50/80 transition-colors group">
                                <td class="px-6 py-4 font-bold text-slate-600">{{ $prod->name }}</td>
                                <td class="px-6 py-4 text-right font-extrabold text-slate-800 text-lg">{{ $prod->qty }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-6 py-12 text-center text-slate-400 font-medium">Sin datos para este periodo</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    Chart.defaults.font.family = "'Inter', 'sans-serif'";
    Chart.defaults.color = '#64748b';

    // 1. Gráfico de Categorías (Dona)
    const ctxCat = document.getElementById('categoryChart');
    if(ctxCat) {
        new Chart(ctxCat, {
            type: 'doughnut',
            data: {
                labels: @json($catLabels),
                datasets: [{
                    data: @json($catValues),
                    backgroundColor: [
                        '#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6', '#06b6d4'
                    ],
                    borderWidth: 0,
                    hoverOffset: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '75%',
                plugins: {
                    legend: { 
                        position: 'right',
                        labels: { padding: 25, font: { weight: 'bold', size: 13 } }
                    }
                }
            }
        });
    }

    // 2. Gráfico de Mozos (Barras)
    const ctxWait = document.getElementById('waiterChart');
    if(ctxWait) {
        new Chart(ctxWait, {
            type: 'bar',
            data: {
                labels: @json($waiterLabels),
                datasets: [{
                    label: 'Ventas Totales ({{ $currency }})',
                    data: @json($waiterValues),
                    backgroundColor: '#10b981',
                    borderRadius: 12,
                    borderSkipped: false
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: { 
                    y: { 
                        beginAtZero: true,
                        grid: { borderDash: [4, 4], drawBorder: false, color: '#f1f5f9' },
                        ticks: { font: { weight: 'bold' } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { font: { weight: 'bold' } }
                    }
                }
            }
        });
    }
</script>
@endsection