import React, { useEffect, useReducer, useCallback } from 'react';
import { posReducer, initialState } from '../store/posReducer';
import { usePosApi } from '../hooks/usePosApi';

/**
 * PosTableMap — Vista del mapa de mesas por salón.
 * Hidrata datos vía API JSON para tener estado en tiempo real.
 * Reemplaza la lógica Alpine + Blade en pos/index.blade.php.
 *
 * Props (inyectadas desde Blade via data-react-props):
 *   @param {string} currency  — símbolo de moneda (ej. "S/")
 */
export default function PosTableMap({ currency = 'S/' }) {
    const [state, dispatch] = useReducer(posReducer, initialState);
    const api = usePosApi();

    // ── Hidratación inicial ──────────────────────────────────────────
    const loadAreas = useCallback(async () => {
        dispatch({ type: 'SET_LOADING', key: 'areas', value: true });
        try {
            const data = await api.fetchAreas();
            dispatch({ type: 'SET_AREAS', areas: data });
        } catch (err) {
            dispatch({ type: 'SET_ERROR', error: err.message });
        } finally {
            dispatch({ type: 'SET_LOADING', key: 'areas', value: false });
        }
    }, []);

    useEffect(() => {
        loadAreas();
        // Polling suave: actualiza el mapa cada 30s para reflejar cambios en tiempo real
        const interval = setInterval(loadAreas, 30_000);
        return () => clearInterval(interval);
    }, [loadAreas]);

    const { areas, loading, error, ui } = state;
    const activeArea = areas.find((a) => a.id === ui.activeAreaId);

    // ── Helpers de UI ────────────────────────────────────────────────
    const getTableStyles = (table) => {
        const isBusy = !!table.active_order;
        const hasReservation = table.reservations_count > 0;

        if (isBusy) {
            return {
                card: 'border-rose-500 ring-4 ring-rose-500/20 shadow-rose-500/20 shadow-lg',
                badge: 'bg-rose-500 text-white',
                icon: 'text-rose-500',
                label: 'text-rose-700',
            };
        }
        if (hasReservation) {
            return {
                card: 'border-amber-400 ring-4 ring-amber-400/20 shadow-amber-500/20 shadow-lg',
                badge: 'bg-amber-100 text-amber-700 border border-amber-300',
                icon: 'text-amber-400',
                label: 'text-slate-500',
            };
        }
        return {
            card: 'border-emerald-500 ring-4 ring-emerald-500/20 shadow-emerald-500/20 shadow-lg',
            badge: 'bg-emerald-500 text-white',
            icon: 'text-slate-300 group-hover:text-emerald-500',
            label: 'text-slate-500',
        };
    };

    // ── Render ───────────────────────────────────────────────────────
    if (loading.areas && areas.length === 0) {
        return (
            <div className="flex-1 flex items-center justify-center text-slate-400">
                <svg className="w-6 h-6 animate-spin mr-3 text-indigo-400" fill="none" viewBox="0 0 24 24">
                    <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4" />
                    <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8v8H4z" />
                </svg>
                <span className="font-bold">Cargando mapa de mesas…</span>
            </div>
        );
    }

    if (error) {
        return (
            <div className="flex-1 flex items-center justify-center">
                <div className="bg-red-50 border border-red-200 text-red-800 rounded-2xl px-6 py-4 max-w-sm text-center shadow-sm">
                    <p className="font-bold mb-1">Error al cargar las mesas</p>
                    <p className="text-sm text-red-600">{error}</p>
                    <button
                        onClick={loadAreas}
                        className="mt-3 bg-red-100 hover:bg-red-200 text-red-700 font-bold text-sm px-4 py-2 rounded-xl transition-colors"
                    >
                        Reintentar
                    </button>
                </div>
            </div>
        );
    }

    return (
        <div className="w-full flex flex-col h-[calc(100vh-2rem)]">
            {/* ── Header ── */}
            <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-6 gap-4 shrink-0">
                <div>
                    <h2 className="text-2xl font-extrabold text-slate-800 tracking-tight">
                        <i className="bi bi-display me-2 text-indigo-500" />
                        Punto de Venta
                    </h2>
                    <p className="text-slate-500 text-sm mt-1 font-medium">
                        Selecciona una mesa para comenzar
                        {loading.areas && (
                            <span className="ml-2 inline-block w-3 h-3 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin align-middle" />
                        )}
                    </p>
                </div>
                {/* Leyenda */}
                <div className="flex flex-wrap gap-4 bg-white p-3 rounded-2xl shadow-sm border border-slate-100">
                    {[
                        { color: 'bg-emerald-500', label: 'Disponible' },
                        { color: 'bg-rose-500', label: 'Ocupada' },
                        { color: 'bg-amber-500', label: 'Reservada' },
                    ].map(({ color, label }) => (
                        <div key={label} className="flex items-center">
                            <span className={`w-3 h-3 rounded-full ${color} mr-2 shadow-sm`} />
                            <small className="font-bold text-slate-600">{label}</small>
                        </div>
                    ))}
                </div>
            </div>

            {/* ── Tabs de Salones ── */}
            <div className="flex gap-2 mb-4 overflow-x-auto pb-2 shrink-0" style={{ scrollbarWidth: 'none' }}>
                {areas.map((area) => (
                    <button
                        key={area.id}
                        onClick={() => dispatch({ type: 'SET_ACTIVE_AREA', id: area.id })}
                        className={`px-6 py-2.5 rounded-xl font-extrabold text-sm transition-all border whitespace-nowrap ${
                            ui.activeAreaId === area.id
                                ? 'bg-indigo-600 text-white shadow-md shadow-indigo-500/30 border-indigo-600'
                                : 'bg-white text-slate-600 hover:bg-slate-50 border-slate-200 shadow-sm'
                        }`}
                    >
                        {area.name}
                        <span className={`ml-2 text-[11px] font-bold opacity-70`}>
                            ({area.tables?.length ?? 0})
                        </span>
                    </button>
                ))}
            </div>

            {/* ── Canvas de Mesas ── */}
            <div
                className="flex-1 relative bg-slate-100 rounded-3xl shadow-inner border border-slate-200 overflow-hidden"
                style={{ backgroundImage: 'radial-gradient(#cbd5e1 1.5px, transparent 1.5px)', backgroundSize: '24px 24px' }}
            >
                {activeArea ? (
                    <div className="absolute inset-0 overflow-auto p-4">
                        {activeArea.tables?.map((table) => {
                            const s = getTableStyles(table);
                            const isBusy = !!table.active_order;
                            const hasReservation = table.reservations_count > 0;

                            return (
                                <a
                                    key={table.id}
                                    href={`/pos/table/${table.id}`}
                                    className={`absolute group flex flex-col items-center justify-between p-3 rounded-[2rem] border-2 bg-white transition-all duration-300 hover:scale-110 hover:z-50 ${s.card}`}
                                    style={{ width: 120, height: 120, left: table.x_pos, top: table.y_pos }}
                                >
                                    {/* Nombre */}
                                    <div className="w-full text-center border-b border-slate-100 pb-1 mb-1">
                                        <span className={`font-extrabold text-[10px] uppercase tracking-widest ${s.label}`}>
                                            {table.name}
                                        </span>
                                    </div>

                                    {/* Icono central */}
                                    <div className="flex-1 flex items-center justify-center relative w-full">
                                        <i className={`bi ${isBusy ? 'bi-display-fill' : 'bi-display'} text-4xl ${s.icon} transition-colors`} />

                                        {/* Badges de reservas */}
                                        {hasReservation && !isBusy && table.upcoming_reservations?.slice(0, 2).map((res) => (
                                            <div
                                                key={res.id}
                                                className="absolute inset-x-0 top-1/2 -translate-y-1/2 flex flex-col items-center pointer-events-none"
                                            >
                                                <div className="bg-amber-400 text-amber-900 px-2 py-0.5 rounded text-[8px] font-bold shadow-sm whitespace-nowrap mb-0.5 w-full overflow-hidden text-ellipsis text-center border border-amber-500">
                                                    {res.time} - {res.client_first_name}
                                                </div>
                                            </div>
                                        ))}
                                    </div>

                                    {/* Footer de estado */}
                                    <div className="w-full text-center mt-1">
                                        {isBusy ? (
                                            <div className={`${s.badge} w-full py-1.5 rounded-xl shadow-sm leading-none flex flex-col items-center justify-center`}>
                                                <span className="text-[8px] font-bold uppercase opacity-80 mb-0.5">Consumo</span>
                                                <span className="text-sm font-extrabold">
                                                    {currency}{Number(table.active_order.total).toFixed(2)}
                                                </span>
                                            </div>
                                        ) : hasReservation ? (
                                            <div className={`${s.badge} w-full py-1.5 rounded-xl shadow-sm text-[10px] font-extrabold`}>
                                                {table.reservations_count} RESERVAS
                                            </div>
                                        ) : (
                                            <div className={`${s.badge} w-full py-1.5 rounded-xl shadow-sm text-xs font-extrabold tracking-wider`}>
                                                LIBRE
                                            </div>
                                        )}
                                    </div>
                                </a>
                            );
                        })}

                        {/* Empty state */}
                        {(!activeArea.tables || activeArea.tables.length === 0) && (
                            <div className="absolute inset-0 flex items-center justify-center text-slate-400">
                                <div className="text-center">
                                    <i className="bi bi-grid-3x3-gap text-6xl text-slate-300" />
                                    <p className="mt-3 font-bold text-slate-500">No hay mesas en este salón</p>
                                </div>
                            </div>
                        )}
                    </div>
                ) : (
                    <div className="absolute inset-0 flex items-center justify-center text-slate-400 font-bold">
                        No hay salones configurados
                    </div>
                )}
            </div>
        </div>
    );
}
