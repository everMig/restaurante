import React from 'react';
import { Card, CardContent, CardHeader, CardTitle, CardDescription } from '../components/ui/Card';
import { Badge } from '../components/ui/Badge';
import { Button } from '../components/ui/Button';
import { RefreshCw, TrendingUp, AlertTriangle, Utensils, DollarSign } from 'lucide-react';

function formatCurrency(symbol, value) {
    return `${symbol}${Number(value ?? 0).toFixed(2)}`;
}

export default function DashboardPulse({
    currency = 'S/',
    totalSalesToday = 0,
    ordersCountToday = 0,
    activeTables = 0,
    lowStockProducts = 0,
    chartLabels = [],
    chartValues = [],
}) {
    const peakValue = Math.max(...chartValues, 1);

    return (
        <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            {/* Main Stats Area */}
            <div className="lg:col-span-2 flex flex-col gap-6">
                <Card className="bg-gradient-to-br from-indigo-50 to-white border-indigo-100 shadow-sm h-full">
                    <CardHeader className="pb-2">
                        <div className="flex justify-between items-start">
                            <div>
                                <Badge variant="default" className="mb-2">React Islands</Badge>
                                <CardTitle className="text-2xl text-indigo-950">Centro Operativo</CardTitle>
                                <CardDescription className="text-indigo-700/70">
                                    Resumen visual del pulso comercial para caja y administración
                                </CardDescription>
                            </div>
                            <Button variant="outline" size="icon" className="bg-white/50 hover:bg-white text-indigo-600 border-indigo-200">
                                <RefreshCw className="h-4 w-4" />
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent>
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mt-4">
                            <div className="p-4 rounded-xl bg-white border border-slate-100 shadow-sm flex flex-col">
                                <div className="flex justify-between items-center mb-2">
                                    <span className="text-sm font-medium text-slate-500">Venta de hoy</span>
                                    <DollarSign className="h-4 w-4 text-emerald-500" />
                                </div>
                                <strong className="text-2xl font-bold text-slate-900">{formatCurrency(currency, totalSalesToday)}</strong>
                                <small className="text-xs text-slate-500 mt-1">{ordersCountToday} órdenes cerradas</small>
                            </div>

                            <div className="p-4 rounded-xl bg-white border border-slate-100 shadow-sm flex flex-col">
                                <div className="flex justify-between items-center mb-2">
                                    <span className="text-sm font-medium text-slate-500">Mesas activas</span>
                                    <Utensils className="h-4 w-4 text-blue-500" />
                                </div>
                                <strong className="text-2xl font-bold text-slate-900">{activeTables}</strong>
                                <small className="text-xs text-slate-500 mt-1">Operación en curso</small>
                            </div>

                            <div className="p-4 rounded-xl bg-red-50 border border-red-100 shadow-sm flex flex-col">
                                <div className="flex justify-between items-center mb-2">
                                    <span className="text-sm font-medium text-red-600">Stock crítico</span>
                                    <AlertTriangle className="h-4 w-4 text-red-600" />
                                </div>
                                <strong className="text-2xl font-bold text-red-700">{lowStockProducts}</strong>
                                <small className="text-xs text-red-600/80 mt-1">Productos por revisar</small>
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            {/* Chart Area */}
            <Card className="shadow-sm h-full flex flex-col">
                <CardHeader className="pb-2">
                    <div className="flex items-center justify-between">
                        <CardTitle className="text-base text-slate-800 flex items-center gap-2">
                            <TrendingUp className="h-4 w-4 text-indigo-500" />
                            Tendencia 7 días
                        </CardTitle>
                    </div>
                </CardHeader>
                <CardContent className="flex-grow flex items-end gap-2 pt-4 min-h-[160px]">
                    {chartValues.length > 0 ? chartValues.map((value, index) => {
                        const height = `${Math.max((value / peakValue) * 100, 10)}%`;
                        return (
                            <div key={`${chartLabels[index]}-${index}`} className="group relative flex flex-col items-center flex-1 h-full justify-end">
                                <div 
                                    className="w-full bg-indigo-100 rounded-t-sm hover:bg-indigo-500 transition-colors relative"
                                    style={{ height }}
                                >
                                    <div className="absolute -top-8 left-1/2 -translate-x-1/2 opacity-0 group-hover:opacity-100 bg-slate-800 text-white text-[10px] py-1 px-2 rounded whitespace-nowrap transition-opacity pointer-events-none z-10">
                                        {formatCurrency(currency, value)}
                                    </div>
                                </div>
                                <span className="text-[10px] text-slate-400 mt-2 rotate-[-45deg] origin-top-left md:rotate-0 md:origin-center">
                                    {chartLabels[index]}
                                </span>
                            </div>
                        );
                    }) : (
                        <div className="w-full h-full flex items-center justify-center text-slate-400 text-sm">
                            Sin datos suficientes
                        </div>
                    )}
                </CardContent>
            </Card>
        </div>
    );
}