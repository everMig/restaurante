import React from 'react';

/**
 * PosCart — Sidebar derecho del POS con el detalle de la cuenta activa.
 * Es un componente puro (sin estado propio) — toda la lógica viene de PosOrder.
 */
export default function PosCart({
    order,
    currency = 'S/',
    loading = false,
    onUpdateQty,
    onRemove,
    onOpenNote,
    onOpenOptions,
    onOpenCheckout,
    splitUrl,
    precheckUrl,
}) {
    const hasItems = order && order.details && order.details.length > 0;
    const total = order
        ? Number(order.total) + Number(order.tip ?? 0) - Number(order.discount ?? 0)
        : 0;

    return (
        <div className="w-72 md:w-80 lg:w-[350px] bg-white border-l border-slate-200 flex flex-col z-10 shadow-[-4px_0_10px_rgba(0,0,0,0.02)] relative">

            {/* Header */}
            <div className="p-4 bg-white border-b border-slate-100 flex items-center gap-3 shrink-0 shadow-sm z-10">
                <div className="w-10 h-10 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center border border-indigo-100">
                    <i className="bi bi-cart-fill text-xl" />
                </div>
                <h6 className="font-extrabold text-slate-800 text-lg m-0">Cuenta Actual</h6>
                {loading && (
                    <span className="ml-auto inline-block w-4 h-4 border-2 border-indigo-400 border-t-transparent rounded-full animate-spin" />
                )}
            </div>

            {/* Items List */}
            <div className="flex-1 overflow-auto p-3" style={{ scrollbarWidth: 'none' }}>
                {hasItems ? (
                    <table className="w-full text-left border-collapse table-fixed">
                        <thead className="text-slate-400 text-[10px] uppercase tracking-widest border-b border-slate-100">
                            <tr>
                                <th className="w-8 pb-2" />
                                <th className="pb-2">Prod.</th>
                                <th className="text-center w-24 pb-2">Cant.</th>
                                <th className="text-right w-16 pb-2">Tot.</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-slate-100/50">
                            {order.details.map((detail) => (
                                <tr key={detail.id} className="group hover:bg-white transition-colors">
                                    {/* Eliminar */}
                                    <td className="py-3">
                                        <button
                                            onClick={() => onRemove(detail.id)}
                                            className="text-rose-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg p-1 transition-all"
                                            title="Eliminar"
                                        >
                                            <i className="bi bi-x-lg text-sm font-extrabold" />
                                        </button>
                                    </td>

                                    {/* Nombre + nota */}
                                    <td className="py-3 px-1 overflow-hidden">
                                        <div className="font-extrabold text-slate-800 text-sm truncate" title={detail.product?.name}>
                                            {detail.product?.name}
                                        </div>
                                        <div className="flex items-center mt-0.5 gap-1">
                                            <small className="text-slate-400 font-bold text-[10px]">
                                                {currency}{Number(detail.price).toFixed(2)}
                                            </small>
                                            {detail.note && (
                                                <i className="bi bi-chat-square-text-fill text-amber-500 text-[10px]" title={detail.note} />
                                            )}
                                            <button
                                                onClick={() => onOpenNote(detail)}
                                                className="text-indigo-400 hover:text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded px-1.5 py-0.5 transition-colors"
                                            >
                                                <i className="bi bi-pencil-fill text-[10px]" />
                                            </button>
                                        </div>
                                    </td>

                                    {/* Cantidad */}
                                    <td className="py-3 px-0 text-center">
                                        <div className="flex items-center justify-center bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden w-[80px] mx-auto">
                                            <button
                                                className="w-7 h-7 flex items-center justify-center bg-slate-50 text-slate-600 hover:bg-slate-200 font-black transition-colors"
                                                onClick={() => onUpdateQty(detail.id, detail.quantity - 1)}
                                            >-</button>
                                            <span className="w-7 text-center font-black text-sm text-slate-800">{detail.quantity}</span>
                                            <button
                                                className="w-7 h-7 flex items-center justify-center bg-slate-50 text-slate-600 hover:bg-slate-200 font-black transition-colors"
                                                onClick={() => onUpdateQty(detail.id, detail.quantity + 1)}
                                            >+</button>
                                        </div>
                                    </td>

                                    {/* Total */}
                                    <td className="text-right py-3 pr-1 font-black text-slate-800 text-sm">
                                        {Number(detail.quantity * detail.price).toFixed(2)}
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                ) : (
                    <div className="h-full flex flex-col items-center justify-center text-slate-300">
                        <div className="w-20 h-20 bg-white border border-slate-100 shadow-sm rounded-full flex items-center justify-center mb-4">
                            <i className="bi bi-basket3-fill text-4xl text-slate-200" />
                        </div>
                        <p className="text-sm font-bold text-slate-400">Cuenta vacía</p>
                    </div>
                )}
            </div>

            {/* Footer: Totales + Acciones */}
            <div className="bg-white border-t border-slate-200 p-5 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)] z-10 relative">
                {order ? (
                    <>
                        {/* Subtotales */}
                        <div className="flex justify-between items-center mb-2 text-xs font-bold text-slate-500">
                            <span className="uppercase tracking-widest">Subtotal</span>
                            <span className="text-slate-700">{Number(order.total).toFixed(2)}</span>
                        </div>
                        {Number(order.discount) > 0 && (
                            <div className="flex justify-between items-center mb-2 text-xs font-bold text-rose-500">
                                <span className="uppercase tracking-widest">Descuento</span>
                                <span>-{Number(order.discount).toFixed(2)}</span>
                            </div>
                        )}
                        {Number(order.tip) > 0 && (
                            <div className="flex justify-between items-center mb-2 text-xs font-bold text-emerald-500">
                                <span className="uppercase tracking-widest">Propina</span>
                                <span>+{Number(order.tip).toFixed(2)}</span>
                            </div>
                        )}

                        {/* Total */}
                        <div className="flex justify-between items-end mb-4 mt-3 pt-3 border-t border-slate-100">
                            <h5 className="m-0 font-extrabold text-slate-800 text-sm uppercase tracking-widest">TOTAL</h5>
                            <h3 className="m-0 font-black text-indigo-600 text-3xl tracking-tight leading-none">
                                {currency}{total.toFixed(2)}
                            </h3>
                        </div>

                        {/* Acciones secundarias */}
                        <div className="grid grid-cols-3 gap-3 mb-4">
                            <ActionButton icon="bi-sliders" label="Ajustes" color="indigo" onClick={onOpenOptions} />
                            <a href={splitUrl} className="bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-600 hover:text-amber-600 rounded-xl py-2.5 flex flex-col items-center justify-center transition-all shadow-sm group">
                                <i className="bi bi-layout-split text-xl mb-1 group-hover:scale-110 transition-transform" />
                                <span className="text-[9px] font-extrabold uppercase tracking-widest">Dividir</span>
                            </a>
                            <a href={precheckUrl} target="_blank" rel="noreferrer" className="bg-white border border-slate-200 hover:border-sky-300 hover:bg-sky-50 text-slate-600 hover:text-sky-600 rounded-xl py-2.5 flex flex-col items-center justify-center transition-all shadow-sm group">
                                <i className="bi bi-receipt text-xl mb-1 group-hover:scale-110 transition-transform" />
                                <span className="text-[9px] font-extrabold uppercase tracking-widest">Ticket</span>
                            </a>
                        </div>

                        {/* Cobrar */}
                        <button
                            onClick={onOpenCheckout}
                            disabled={!hasItems}
                            className="w-full bg-emerald-500 hover:bg-emerald-600 disabled:opacity-50 disabled:cursor-not-allowed text-white font-black py-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex items-center justify-center gap-2 text-lg uppercase tracking-widest"
                        >
                            <i className="bi bi-cash-coin text-2xl" /> COBRAR
                        </button>
                    </>
                ) : (
                    <p className="text-center text-sm font-bold text-slate-400">Agrega productos para comenzar</p>
                )}
            </div>
        </div>
    );
}

function ActionButton({ icon, label, color = 'indigo', onClick }) {
    const colors = {
        indigo: 'hover:border-indigo-300 hover:bg-indigo-50 hover:text-indigo-600',
        amber:  'hover:border-amber-300  hover:bg-amber-50  hover:text-amber-600',
        sky:    'hover:border-sky-300    hover:bg-sky-50    hover:text-sky-600',
    };
    return (
        <button
            onClick={onClick}
            className={`bg-white border border-slate-200 text-slate-600 rounded-xl py-2.5 flex flex-col items-center justify-center transition-all shadow-sm group ${colors[color]}`}
        >
            <i className={`bi ${icon} text-xl mb-1 group-hover:scale-110 transition-transform`} />
            <span className="text-[9px] font-extrabold uppercase tracking-widest">{label}</span>
        </button>
    );
}
