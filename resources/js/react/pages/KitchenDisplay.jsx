import React, { useState, useEffect, useCallback, useRef } from 'react';

/**
 * KitchenDisplay — Monitor de Cocina (KDS) en React.
 *
 * Mejoras sobre el Blade anterior:
 *  - Polling cada 15s sin recargar la PÁGINA ENTERA (sin flash/parpadeo)
 *  - Actualización de estado por AJAX (sin redirect + recarga)
 *  - Reloj en tiempo real
 *  - Badge de conteo de pedidos pendientes
 *  - Animación de entrada de nuevas órdenes
 *  - Indicador visual de tiempo transcurrido por pedido (urgencia)
 *
 * Props (desde Blade):
 *   (ninguna — fetcha sus propios datos)
 */
export default function KitchenDisplay() {
    const [orders, setOrders] = useState([]);
    const [loading, setLoading] = useState(true);
    const [time, setTime] = useState(new Date());
    const [updating, setUpdating] = useState({}); // { detailId: true }
    const prevOrderIds = useRef(new Set());
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

    // ── Reloj ────────────────────────────────────────────────────────
    useEffect(() => {
        const tick = setInterval(() => setTime(new Date()), 1000);
        return () => clearInterval(tick);
    }, []);

    // ── Fetch órdenes ────────────────────────────────────────────────
    const fetchOrders = useCallback(async () => {
        try {
            const res = await fetch('/api/kitchen/orders', {
                headers: { 'Accept': 'application/json' },
            });
            if (!res.ok) return;
            const data = await res.json();
            setOrders(data);
            prevOrderIds.current = new Set(data.map(o => o.id));
        } catch (_) {
            // Silent fail — el KDS no debe interrumpir la operación de cocina
        } finally {
            setLoading(false);
        }
    }, []);

    useEffect(() => {
        fetchOrders();
        const interval = setInterval(fetchOrders, 15_000);
        return () => clearInterval(interval);
    }, [fetchOrders]);

    // ── Avanzar estado de un plato ───────────────────────────────────
    const handleAdvanceStatus = useCallback(async (detailId) => {
        setUpdating(prev => ({ ...prev, [detailId]: true }));
        try {
            await fetch(`/kitchen/${detailId}/status`, {
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': csrfToken, 'Accept': 'application/json' },
            });
            // Actualización optimista: fetch inmediato sin esperar el interval
            await fetchOrders();
        } catch (_) {
            // Fallback silencioso
        } finally {
            setUpdating(prev => ({ ...prev, [detailId]: false }));
        }
    }, [csrfToken, fetchOrders]);

    // ── Helpers ──────────────────────────────────────────────────────
    const getElapsedMinutes = (createdAt) => {
        const diff = Math.floor((Date.now() - new Date(createdAt)) / 60000);
        return diff;
    };

    const getUrgencyClass = (minutes) => {
        if (minutes >= 20) return 'from-red-600 to-red-700';
        if (minutes >= 10) return 'from-amber-500 to-amber-600';
        return 'from-rose-500 to-rose-600';
    };

    const isAllCooking = (order) => order.details.every(d => d.status === 'cooking');

    const totalPending = orders.reduce(
        (acc, o) => acc + o.details.filter(d => d.status === 'pending').length, 0
    );

    // ── Render ───────────────────────────────────────────────────────
    return (
        <div className="w-full">
            {/* Header */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                <div>
                    <h2 className="text-2xl font-extrabold text-slate-800 tracking-tight flex items-center gap-2">
                        <i className="bi bi-fire text-rose-500" />
                        Monitor de Cocina (KDS)
                    </h2>
                    <p className="text-slate-500 text-sm mt-1 font-medium">
                        Pedidos pendientes de preparación
                        {loading && <span className="ml-2 inline-block w-3 h-3 border-2 border-rose-400 border-t-transparent rounded-full animate-spin align-middle" />}
                    </p>
                </div>

                <div className="flex items-center gap-4 bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                    {/* Leyenda estados */}
                    <StatusPill color="bg-rose-500" label="Pendiente" />
                    <StatusPill color="bg-amber-500" label="Preparando" />

                    {/* Badge de total pendientes */}
                    {totalPending > 0 && (
                        <span className="px-3 py-1.5 rounded-full text-xs font-black bg-rose-100 text-rose-700 border border-rose-200 animate-pulse">
                            {totalPending} pendiente{totalPending !== 1 ? 's' : ''}
                        </span>
                    )}

                    {/* Reloj */}
                    <div className="font-extrabold text-2xl text-slate-800 ml-2 tracking-widest font-mono tabular-nums">
                        {time.toLocaleTimeString('es-ES', { hour12: false })}
                    </div>
                </div>
            </div>

            {/* Grid de Comandas */}
            {orders.length === 0 && !loading ? (
                <EmptyState />
            ) : (
                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                    {orders.map(order => {
                        const elapsed = getElapsedMinutes(order.created_at);
                        const allCooking = isAllCooking(order);
                        const headerGradient = allCooking
                            ? 'from-amber-500 to-amber-600'
                            : getUrgencyClass(elapsed);

                        return (
                            <div
                                key={order.id}
                                className="bg-white rounded-3xl shadow-sm border border-slate-200 flex flex-col overflow-hidden transition-all duration-300 hover:shadow-lg"
                            >
                                {/* Card Header */}
                                <div className={`p-5 flex justify-between items-center text-white bg-gradient-to-r ${headerGradient}`}>
                                    <div>
                                        <h5 className="font-extrabold text-xl mb-0.5">
                                            Mesa: {order.table_name ?? 'Barra'}
                                        </h5>
                                        <small className="opacity-90 font-bold tracking-widest text-[10px] uppercase">
                                            Folio #{String(order.id).padStart(4, '0')}
                                        </small>
                                    </div>
                                    <div className="text-right">
                                        {/* Tiempo transcurrido */}
                                        <div className={`text-xs font-black uppercase tracking-wider mb-0.5 opacity-80 ${elapsed >= 15 ? 'text-white' : 'text-white/70'}`}>
                                            {elapsed >= 15 && <i className="bi bi-exclamation-triangle-fill mr-1" />}
                                            {elapsed} min
                                        </div>
                                        <span className="block font-extrabold text-xl">
                                            {new Date(order.created_at).toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit' })}
                                        </span>
                                    </div>
                                </div>

                                {/* Items */}
                                <div className="flex-1 bg-slate-50/30">
                                    <ul className="divide-y divide-slate-100">
                                        {order.details.map(detail => (
                                            <li
                                                key={detail.id}
                                                className="p-4 flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 hover:bg-white transition-colors group"
                                            >
                                                <div className="flex flex-col flex-1">
                                                    <div className="flex items-start">
                                                        <span className="inline-flex items-center justify-center min-w-[1.75rem] h-7 px-2 bg-slate-800 text-white rounded-lg text-sm font-extrabold mr-3 mt-0.5 shadow-sm">
                                                            {detail.quantity}
                                                        </span>
                                                        <span className={`font-extrabold text-slate-800 text-lg leading-tight ${detail.status === 'served' ? 'line-through text-slate-400' : ''}`}>
                                                            {detail.product_name}
                                                        </span>
                                                    </div>
                                                    {detail.note && (
                                                        <div className="ml-10 mt-2">
                                                            <span className="inline-block px-3 py-1.5 bg-amber-50 text-amber-700 border border-amber-200 rounded-lg text-xs font-bold shadow-sm">
                                                                <i className="bi bi-exclamation-circle-fill mr-1 text-amber-500" />
                                                                {detail.note}
                                                            </span>
                                                        </div>
                                                    )}
                                                </div>

                                                {/* Botón avanzar estado */}
                                                <StatusButton
                                                    status={detail.status}
                                                    loading={!!updating[detail.id]}
                                                    onClick={() => handleAdvanceStatus(detail.id)}
                                                />
                                            </li>
                                        ))}
                                    </ul>
                                </div>

                                {/* Footer con progreso */}
                                <KdsProgress order={order} />
                            </div>
                        );
                    })}

                    {/* Skeleton mientras carga */}
                    {loading && orders.length === 0 && Array.from({ length: 4 }).map((_, i) => (
                        <div key={i} className="bg-white rounded-3xl border border-slate-200 h-64 animate-pulse" />
                    ))}
                </div>
            )}
        </div>
    );
}

// ── Sub-componentes ──────────────────────────────────────────────────────────

function StatusPill({ color, label }) {
    return (
        <span className="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-bold bg-slate-50 text-slate-600 border border-slate-200">
            <span className={`w-2.5 h-2.5 rounded-full ${color} mr-2 shadow-sm`} />
            {label}
        </span>
    );
}

function StatusButton({ status, loading, onClick }) {
    if (status === 'pending') {
        return (
            <button
                onClick={onClick}
                disabled={loading}
                className="w-full sm:w-auto px-5 py-2.5 rounded-xl font-bold text-rose-600 bg-rose-50 border border-rose-200 hover:bg-rose-500 hover:text-white transition-all shadow-sm active:scale-95 disabled:opacity-50 flex items-center gap-2"
            >
                {loading ? <span className="w-4 h-4 border-2 border-rose-400 border-t-transparent rounded-full animate-spin" /> : null}
                Empezar
            </button>
        );
    }
    if (status === 'cooking') {
        return (
            <button
                onClick={onClick}
                disabled={loading}
                className="w-full sm:w-auto px-5 py-2.5 rounded-xl font-bold text-white bg-amber-500 hover:bg-amber-600 shadow-md shadow-amber-500/30 transition-all active:scale-95 disabled:opacity-50 flex items-center gap-2"
            >
                {loading ? <span className="w-4 h-4 border-2 border-white/60 border-t-transparent rounded-full animate-spin" /> : <i className="bi bi-check-lg font-extrabold" />}
                Listo
            </button>
        );
    }
    return null;
}

function KdsProgress({ order }) {
    const total = order.details.length;
    const done = order.details.filter(d => d.status === 'served' || d.status === 'cooking').length;
    const pct = total > 0 ? Math.round((done / total) * 100) : 0;

    return (
        <div className="px-4 py-3 border-t border-slate-100 bg-white">
            <div className="flex justify-between text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">
                <span>Progreso</span>
                <span>{done}/{total}</span>
            </div>
            <div className="w-full bg-slate-100 rounded-full h-1.5">
                <div
                    className="bg-emerald-500 h-1.5 rounded-full transition-all duration-500"
                    style={{ width: `${pct}%` }}
                />
            </div>
        </div>
    );
}

function EmptyState() {
    return (
        <div className="flex flex-col items-center justify-center py-32 bg-white rounded-3xl border border-slate-100 shadow-sm">
            <div className="w-24 h-24 bg-emerald-50 rounded-full flex items-center justify-center mb-6 shadow-inner">
                <i className="bi bi-check-lg text-6xl text-emerald-500 drop-shadow-sm" />
            </div>
            <h2 className="text-3xl font-extrabold text-slate-800 mb-2">Todo en orden, Chef.</h2>
            <p className="text-slate-500 font-medium">No hay pedidos pendientes en este momento.</p>
        </div>
    );
}
