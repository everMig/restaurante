import React, { useState, useEffect, useRef } from 'react';

/**
 * PosCheckout — Modal de cobro completo.
 * Envía el formulario al endpoint de Laravel via POST tradicional
 * (sin fetch) para mantener el redirect + flash session de Laravel.
 *
 * Props:
 *   open     : boolean
 *   order    : Order object | null
 *   clients  : Client[]
 *   currency : string
 *   onClose  : () => void
 */
export default function PosCheckout({ open, order, clients = [], currency = 'S/', onClose }) {
    const [payMethod, setPayMethod] = useState('cash');
    const [received, setReceived] = useState('0.00');
    const [clientSearch, setClientSearch] = useState('');
    const [clientId, setClientId] = useState('');
    const [clientDoc, setClientDoc] = useState('');
    const [docType, setDocType] = useState('Ticket');
    const formRef = useRef(null);

    const total = order
        ? (Number(order.total) + Number(order.tip ?? 0) - Number(order.discount ?? 0)).toFixed(2)
        : '0.00';

    const change = Math.max(0, parseFloat(received || 0) - parseFloat(total)).toFixed(2);

    useEffect(() => {
        if (open && order) {
            setReceived(total);
            setPayMethod('cash');
            setClientSearch('');
            setClientId('');
            setClientDoc('');
            setDocType('Ticket');
        }
    }, [open, order, total]);

    const handleClientChange = (value) => {
        setClientSearch(value);
        const match = clients.find((c) => c.name === value);
        if (match) {
            setClientId(match.id);
            setClientDoc(match.document_number ?? '');
        } else {
            setClientId('');
            setClientDoc('');
        }
    };

    if (!open || !order) return null;

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';
    const actionUrl = `/pos/order/${order.id}/checkout`;

    return (
        <div className="fixed inset-0 z-[200] flex items-center justify-center p-4">
            {/* Backdrop */}
            <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={onClose} />

            {/* Panel */}
            <form
                ref={formRef}
                action={actionUrl}
                method="POST"
                className="relative bg-white rounded-3xl shadow-2xl w-full max-w-md overflow-hidden"
                onClick={(e) => e.stopPropagation()}
            >
                <input type="hidden" name="_token" value={csrfToken} />
                <input type="hidden" name="client_id" value={clientId} />

                {/* Header */}
                <div className="bg-emerald-500 p-5 flex justify-between items-center shadow-sm">
                    <h5 className="font-black text-white m-0 tracking-tight flex items-center gap-2">
                        <i className="bi bi-cash-coin text-2xl" /> Cobrar Venta
                    </h5>
                    <button type="button" onClick={onClose} className="text-emerald-100 hover:text-white transition-colors">
                        <i className="bi bi-x-lg text-xl font-bold" />
                    </button>
                </div>

                <div className="p-6 bg-slate-50/50 space-y-5 max-h-[70vh] overflow-y-auto" style={{ scrollbarWidth: 'thin' }}>

                    {/* Cliente */}
                    <div className="bg-white p-4 rounded-2xl shadow-sm border border-slate-200">
                        <label className="block text-xs font-bold text-slate-500 mb-2 uppercase tracking-widest">Cliente</label>
                        <div className="relative mb-3">
                            <span className="absolute left-4 top-1/2 -translate-y-1/2 text-slate-400">
                                <i className="bi bi-search" />
                            </span>
                            <input
                                type="text"
                                list="pos-clients-list"
                                value={clientSearch}
                                onChange={(e) => handleClientChange(e.target.value)}
                                placeholder="Buscar por nombre o doc…"
                                autoComplete="off"
                                className="w-full bg-slate-50 rounded-xl border border-slate-200 focus:border-emerald-500 focus:bg-white focus:ring-4 focus:ring-emerald-500/10 pl-11 pr-10 py-3 outline-none transition-all font-bold text-slate-700 shadow-inner"
                            />
                            <datalist id="pos-clients-list">
                                {clients.map((c) => (
                                    <option key={c.id} value={c.name} data-id={c.id} data-doc={c.document_number} />
                                ))}
                            </datalist>
                            {clientSearch && (
                                <button type="button" onClick={() => { setClientSearch(''); setClientId(''); setClientDoc(''); }}
                                    className="absolute right-3 top-1/2 -translate-y-1/2 text-slate-400 hover:text-rose-500 transition-colors">
                                    <i className="bi bi-x-circle-fill" />
                                </button>
                            )}
                        </div>

                        <div className="grid grid-cols-12 gap-3">
                            <div className="col-span-7">
                                <input
                                    type="text" name="client_document" value={clientDoc} readOnly
                                    placeholder="RUC/DNI"
                                    className="w-full bg-slate-100 rounded-xl border border-slate-200 px-4 py-2.5 outline-none font-bold text-slate-500 cursor-not-allowed shadow-inner"
                                />
                            </div>
                            <div className="col-span-5">
                                <select
                                    name="document_type" value={docType}
                                    onChange={(e) => setDocType(e.target.value)}
                                    className="w-full bg-white rounded-xl border border-slate-200 focus:border-emerald-500 px-4 py-2.5 outline-none font-extrabold text-slate-700 shadow-sm transition-all"
                                >
                                    <option value="Ticket">Ticket</option>
                                    <option value="Boleta">Boleta</option>
                                    <option value="Factura">Factura</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    {/* Método de pago */}
                    <div className="flex bg-slate-200/70 p-1 rounded-2xl shadow-inner">
                        {[
                            { value: 'cash', icon: 'bi-cash', label: 'EFECTIVO', activeColor: 'text-emerald-600' },
                            { value: 'card', icon: 'bi-credit-card', label: 'TARJETA', activeColor: 'text-indigo-600' },
                        ].map(({ value, icon, label, activeColor }) => (
                            <button
                                key={value}
                                type="button"
                                onClick={() => setPayMethod(value)}
                                className={`flex-1 cursor-pointer font-black py-3 rounded-xl transition-all flex items-center justify-center gap-2 ${
                                    payMethod === value
                                        ? `bg-white ${activeColor} shadow-sm`
                                        : 'text-slate-500'
                                }`}
                            >
                                <i className={`bi ${icon}`} /> {label}
                            </button>
                        ))}
                    </div>

                    {/* input hidden para método de pago */}
                    <input type="hidden" name="payment_method" value={payMethod} />

                    {/* Monto recibido (solo efectivo) */}
                    {payMethod === 'cash' && (
                        <div className="bg-white p-5 rounded-2xl shadow-sm border border-slate-200 text-center">
                            <label className="block text-sm font-extrabold text-slate-500 mb-2 uppercase tracking-widest">
                                Recibido ({currency})
                            </label>
                            <input
                                type="number" step="0.01"
                                name="received_amount"
                                value={received}
                                onChange={(e) => setReceived(e.target.value)}
                                onClick={(e) => e.target.select()}
                                className="w-full text-center font-black text-5xl text-emerald-600 bg-emerald-50/50 border-none rounded-xl focus:ring-0 outline-none p-2 mb-4 tracking-tight"
                            />
                            <div className="flex justify-between items-center border-t border-slate-100 pt-3">
                                <span className="text-sm font-bold text-slate-400 uppercase tracking-widest">Vuelto:</span>
                                <div className="flex items-center gap-1">
                                    <span className="text-slate-400 font-bold">{currency}</span>
                                    <h4 className="font-black text-3xl m-0 text-slate-700 tracking-tight">{change}</h4>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Resumen del total */}
                    <div className="flex justify-between items-center bg-white rounded-2xl px-5 py-4 border border-slate-200 shadow-sm">
                        <span className="font-extrabold text-slate-600 uppercase tracking-widest text-sm">Total a cobrar</span>
                        <span className="font-black text-2xl text-indigo-600">{currency}{total}</span>
                    </div>
                </div>

                {/* Footer */}
                <div className="p-5 bg-white border-t border-slate-200">
                    <button
                        type="submit"
                        className="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95 text-xl tracking-wider"
                    >
                        CONFIRMAR PAGO
                    </button>
                </div>
            </form>
        </div>
    );
}
