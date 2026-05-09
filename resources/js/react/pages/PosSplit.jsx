import React, { useState, useEffect } from 'react';

/**
 * PosSplit — División de cuenta en React.
 *
 * Mejoras sobre el Blade anterior:
 *  - Selección visual de ítems con feedback inmediato
 *  - Cálculo de total en tiempo real sin JS suelto
 *  - UI clara del resumen de lo que queda en cuenta
 *  - Envío via form nativo (mantiene compatibilidad con Laravel)
 *
 * Props (desde Blade):
 *   orderId    : number
 *   orderLabel : string  — "Mesa X - Orden #00001"
 *   details    : { id, product_name, quantity, price, note }[]
 *   currency   : string
 *   splitUrl   : string  — action del form POST
 *   backUrl    : string
 *   csrfToken  : string
 */
export default function PosSplit({
    orderId,
    orderLabel = 'Orden',
    details = [],
    currency = 'S/',
    splitUrl,
    backUrl,
    csrfToken,
}) {
    const [selected, setSelected] = useState(new Set());

    const toggleItem = (id) => {
        setSelected(prev => {
            const next = new Set(prev);
            if (next.has(id)) { next.delete(id); } else { next.add(id); }
            return next;
        });
    };

    const toggleAll = (checked) => {
        setSelected(checked ? new Set(details.map(d => d.id)) : new Set());
    };

    const allChecked = details.length > 0 && selected.size === details.length;

    const selectedTotal = details
        .filter(d => selected.has(d.id))
        .reduce((acc, d) => acc + d.price * d.quantity, 0);

    const remainingTotal = details
        .filter(d => !selected.has(d.id))
        .reduce((acc, d) => acc + d.price * d.quantity, 0);

    const [payMethod, setPayMethod] = useState('cash');

    return (
        <div className="w-full flex justify-center">
            <div className="w-full max-w-3xl">

                {/* Header */}
                <div className="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
                    <div>
                        <h2 className="text-2xl font-extrabold text-slate-800 tracking-tight">
                            <i className="bi bi-arrows-angle-expand me-2 text-indigo-500" />
                            Dividir Cuenta
                        </h2>
                        <p className="text-slate-500 text-sm mt-1 font-medium">
                            Selecciona los ítems que deseas cobrar por separado
                        </p>
                    </div>
                    <a href={backUrl} className="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-5 rounded-xl shadow-sm border border-slate-200 transition-colors flex items-center gap-2">
                        <i className="bi bi-arrow-left" /> Volver
                    </a>
                </div>

                <div className="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden flex flex-col">
                    {/* Orden header */}
                    <div className="bg-indigo-600 text-white p-6 flex justify-between items-center shadow-sm">
                        <h5 className="mb-0 font-extrabold text-lg">{orderLabel}</h5>
                        <span className="text-indigo-200 text-sm font-bold">
                            {selected.size} de {details.length} ítems seleccionados
                        </span>
                    </div>

                    <form action={splitUrl} method="POST">
                        <input type="hidden" name="_token" value={csrfToken} />
                        <input type="hidden" name="payment_method" value={payMethod} />

                        {/* Tabla de ítems */}
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse min-w-[500px]">
                                <thead className="bg-slate-50 border-b border-slate-200 text-slate-500 text-xs uppercase tracking-widest">
                                    <tr>
                                        <th className="px-6 py-4 w-12">
                                            <input
                                                type="checkbox"
                                                checked={allChecked}
                                                onChange={(e) => toggleAll(e.target.checked)}
                                                className="w-4 h-4 text-indigo-600 rounded cursor-pointer"
                                            />
                                        </th>
                                        <th className="px-6 py-4 font-bold">Producto</th>
                                        <th className="px-6 py-4 font-bold text-center">Cant.</th>
                                        <th className="px-6 py-4 font-bold text-right">Subtotal</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-slate-100">
                                    {details.map(detail => {
                                        const isSelected = selected.has(detail.id);
                                        return (
                                            <tr
                                                key={detail.id}
                                                onClick={() => toggleItem(detail.id)}
                                                className={`cursor-pointer transition-colors ${isSelected ? 'bg-indigo-50/60 hover:bg-indigo-50' : 'hover:bg-slate-50'}`}
                                            >
                                                <td className="px-6 py-4" onClick={(e) => e.stopPropagation()}>
                                                    <input
                                                        type="checkbox"
                                                        name="selected_items[]"
                                                        value={detail.id}
                                                        checked={isSelected}
                                                        onChange={() => toggleItem(detail.id)}
                                                        className="w-4 h-4 text-indigo-600 rounded cursor-pointer"
                                                    />
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className={`font-extrabold text-[1.05rem] transition-colors ${isSelected ? 'text-indigo-700' : 'text-slate-800'}`}>
                                                        {detail.product_name}
                                                    </div>
                                                    {detail.note && (
                                                        <small className="text-amber-600 font-bold mt-1 flex items-center gap-1">
                                                            <i className="bi bi-sticky-fill" /> {detail.note}
                                                        </small>
                                                    )}
                                                </td>
                                                <td className="px-6 py-4 text-center">
                                                    <span className="inline-flex items-center justify-center min-w-[2.5rem] py-1 px-2 bg-slate-100 text-slate-800 rounded-lg font-extrabold border border-slate-200 shadow-sm">
                                                        {detail.quantity}
                                                    </span>
                                                </td>
                                                <td className="px-6 py-4 text-right font-extrabold text-slate-800 text-lg">
                                                    {currency} {(detail.price * detail.quantity).toFixed(2)}
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>

                        {/* Footer: totales + método pago + acción */}
                        <div className="bg-slate-50/80 p-6 border-t border-slate-200 space-y-5">

                            {/* Resumen: cobrar ahora vs lo que queda */}
                            <div className="grid grid-cols-2 gap-4">
                                <div className="bg-indigo-50 rounded-2xl p-4 border border-indigo-100">
                                    <p className="text-xs font-bold text-indigo-500 uppercase tracking-widest mb-1">A cobrar ahora</p>
                                    <p className="text-2xl font-black text-indigo-700">{currency} {selectedTotal.toFixed(2)}</p>
                                </div>
                                <div className="bg-slate-100 rounded-2xl p-4 border border-slate-200">
                                    <p className="text-xs font-bold text-slate-400 uppercase tracking-widest mb-1">Queda en cuenta</p>
                                    <p className="text-2xl font-black text-slate-600">{currency} {remainingTotal.toFixed(2)}</p>
                                </div>
                            </div>

                            {/* Método de pago */}
                            <div>
                                <label className="block text-sm font-bold text-slate-700 mb-3">Método de pago para esta parte:</label>
                                <div className="flex gap-3">
                                    {[
                                        { value: 'cash', icon: 'bi-cash', label: 'Efectivo', activeStyle: 'border-emerald-500 bg-emerald-50 text-emerald-700' },
                                        { value: 'card', icon: 'bi-credit-card', label: 'Tarjeta', activeStyle: 'border-indigo-500 bg-indigo-50 text-indigo-700' },
                                    ].map(({ value, icon, label, activeStyle }) => (
                                        <label key={value} className="flex-1 cursor-pointer">
                                            <input
                                                type="radio" name="_pay_method_ui" value={value}
                                                checked={payMethod === value}
                                                onChange={() => setPayMethod(value)}
                                                className="sr-only"
                                            />
                                            <div className={`border-2 rounded-xl px-4 py-3 flex items-center justify-center gap-2 font-bold transition-all shadow-sm ${payMethod === value ? activeStyle : 'border-slate-200 bg-white text-slate-600 hover:border-slate-300'}`}>
                                                <i className={`bi ${icon}`} /> {label}
                                            </div>
                                        </label>
                                    ))}
                                </div>
                            </div>

                            {/* Botón de acción */}
                            <button
                                type="submit"
                                disabled={selected.size === 0}
                                className="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-4 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex justify-center items-center gap-2 disabled:opacity-40 disabled:cursor-not-allowed disabled:hover:scale-100 disabled:hover:bg-indigo-600 text-lg"
                            >
                                <i className="bi bi-check-circle-fill" />
                                {selected.size > 0
                                    ? `Cobrar ${currency} ${selectedTotal.toFixed(2)}`
                                    : 'Selecciona ítems para cobrar'
                                }
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    );
}
