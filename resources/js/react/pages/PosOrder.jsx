import React, { useEffect, useReducer, useCallback, useRef } from 'react';
import { posReducer, initialState } from '../store/posReducer';
import { usePosApi } from '../hooks/usePosApi';
import PosCart from '../components/pos/PosCart';
import PosCheckout from '../components/pos/PosCheckout';

/**
 * PosOrder — Pantalla de toma de comanda (isla React).
 *
 * Props (desde Blade via data-react-props):
 *   tableId     : number
 *   tableName   : string
 *   areaName    : string
 *   currency    : string
 *   userName    : string
 *   userInitial : string
 */
export default function PosOrder({
    tableId,
    tableName = 'Mesa',
    areaName = '',
    currency = 'S/',
    userName = '',
    userInitial = '?',
}) {
    const [state, dispatch] = useReducer(posReducer, initialState);
    const api = usePosApi();
    const barcodeRef = useRef(null);

    // ── Helpers ──────────────────────────────────────────────────────
    const setCartLoading = (v) => dispatch({ type: 'SET_LOADING', key: 'cart', value: v });

    const refreshOrder = useCallback(async () => {
        try {
            const data = await api.fetchOrder(tableId);
            dispatch({ type: 'SET_ORDER', order: data });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: e.message });
        }
    }, [tableId]);

    // ── Inicialización ───────────────────────────────────────────────
    useEffect(() => {
        const init = async () => {
            dispatch({ type: 'SET_LOADING', key: 'products', value: true });
            dispatch({ type: 'SET_LOADING', key: 'order', value: true });
            try {
                const [products, clients, order] = await Promise.all([
                    api.fetchProducts(),
                    api.fetchClients(),
                    api.fetchOrder(tableId),
                ]);
                dispatch({ type: 'SET_PRODUCTS', products });
                dispatch({ type: 'SET_CLIENTS', clients });
                dispatch({ type: 'SET_ORDER', order });
            } catch (e) {
                dispatch({ type: 'SET_ERROR', error: e.message });
            } finally {
                dispatch({ type: 'SET_LOADING', key: 'products', value: false });
                dispatch({ type: 'SET_LOADING', key: 'order', value: false });
            }
        };
        init();
        barcodeRef.current?.focus();
    }, [tableId]);

    // ── Acciones del carrito ─────────────────────────────────────────
    const handleAddItem = useCallback(async (productId) => {
        setCartLoading(true);
        try {
            const order = await api.addItem(tableId, productId);
            dispatch({ type: 'SET_ORDER', order });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: e.message });
        } finally {
            setCartLoading(false);
        }
    }, [tableId]);

    const handleBarcode = useCallback(async (code) => {
        if (!code.trim()) return;
        setCartLoading(true);
        try {
            const order = await api.addByBarcode(tableId, code.trim());
            dispatch({ type: 'SET_ORDER', order });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: `Código no encontrado: ${code}` });
        } finally {
            setCartLoading(false);
            if (barcodeRef.current) { barcodeRef.current.value = ''; barcodeRef.current.focus(); }
        }
    }, [tableId]);

    const handleUpdateQty = useCallback(async (detailId, qty) => {
        if (qty < 1) {
            const result = await window.Swal.fire({
                title: '¿Eliminar producto?',
                text: 'El producto será removido de la cuenta.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar',
                customClass: { popup: 'rounded-3xl', confirmButton: 'rounded-xl', cancelButton: 'rounded-xl' }
            });
            if (!result.isConfirmed) return;
        }

        setCartLoading(true);
        try {
            const order = await api.updateQty(detailId, qty);
            dispatch({ type: 'SET_ORDER', order });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: e.message });
        } finally { setCartLoading(false); }
    }, []);

    const handleRemoveItem = useCallback(async (detailId) => {
        setCartLoading(true);
        try {
            const order = await api.removeItem(detailId);
            dispatch({ type: 'SET_ORDER', order });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: e.message });
        } finally { setCartLoading(false); }
    }, []);

    const handleSaveNote = useCallback(async (detailId, note) => {
        setCartLoading(true);
        try {
            const order = await api.updateNote(detailId, note);
            dispatch({ type: 'SET_ORDER', order });
            dispatch({ type: 'CLOSE_MODAL' });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: e.message });
        } finally { setCartLoading(false); }
    }, []);

    const handleApplyOptions = useCallback(async (discount, tip) => {
        if (!state.order) return;
        setCartLoading(true);
        try {
            const order = await api.applyOptions(state.order.id, discount, tip);
            dispatch({ type: 'SET_ORDER', order });
            dispatch({ type: 'CLOSE_MODAL' });
        } catch (e) {
            dispatch({ type: 'SET_ERROR', error: e.message });
        } finally { setCartLoading(false); }
    }, [state.order]);

    // ── Filtrado de productos ────────────────────────────────────────
    const { products, ui, loading, error, order, clients } = state;

    const filteredProducts = products.filter((p) => {
        const matchCat = ui.activeCategoryId === 'all' || p.category_id === ui.activeCategoryId;
        const matchSearch = !ui.searchTerm ||
            p.name.toLowerCase().includes(ui.searchTerm.toLowerCase()) ||
            (p.barcode && p.barcode.includes(ui.searchTerm));
        return matchCat && matchSearch;
    });

    // Obtener categorías únicas de los productos cargados
    const categories = Array.from(
        new Map(products.filter(p => p.category).map(p => [p.category_id, p.category])).values()
    );

    // ── Render ───────────────────────────────────────────────────────
    return (
        <div className="w-full h-[calc(100vh-2rem)] flex flex-col bg-slate-100 overflow-hidden rounded-3xl shadow-inner border border-slate-200 -mt-2">

            {/* ── Header ── */}
            <div className="flex justify-between items-center bg-white border-b border-slate-200 px-6 py-4 shrink-0 z-10 shadow-sm">
                <div className="flex items-center gap-4">
                    <a href="/pos" className="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2 px-4 rounded-xl shadow-sm border border-slate-200 transition-colors flex items-center gap-2 text-sm">
                        <i className="bi bi-arrow-left" /> Volver
                    </a>
                    <div className="h-8 w-px bg-slate-200" />
                    <div>
                        <h5 className="font-extrabold text-indigo-600 text-lg leading-tight m-0">Mesa: {tableName}</h5>
                        <small className="text-slate-500 font-bold tracking-widest text-[10px] uppercase">Zona: {areaName}</small>
                    </div>
                </div>

                <div className="flex items-center gap-3">
                    {order && (
                        <button
                            onClick={() => dispatch({ type: 'OPEN_MODAL', modal: 'move' })}
                            className="bg-white hover:bg-indigo-50 text-indigo-600 font-bold py-2 px-4 rounded-xl shadow-sm border border-indigo-200 transition-colors flex items-center gap-2 text-sm"
                        >
                            <i className="bi bi-arrow-left-right" /> Mover Mesa
                        </button>
                    )}
                    <div className="hidden sm:flex items-center gap-2 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200">
                        <div className="w-6 h-6 rounded-full bg-indigo-100 text-indigo-600 flex items-center justify-center text-xs font-bold">
                            {userInitial}
                        </div>
                        <span className="text-xs font-bold text-slate-700">{userName}</span>
                    </div>
                </div>
            </div>

            {/* ── Error toast ── */}
            {error && (
                <div className="bg-rose-50 border-b border-rose-200 px-6 py-2 flex items-center justify-between gap-3 text-sm text-rose-700 font-bold shrink-0">
                    <span><i className="bi bi-exclamation-triangle-fill mr-2" />{error}</span>
                    <button onClick={() => dispatch({ type: 'CLEAR_ERROR' })} className="text-rose-400 hover:text-rose-600">
                        <i className="bi bi-x-lg" />
                    </button>
                </div>
            )}

            {/* ── Main: Categorías | Productos | Carrito ── */}
            <div className="flex flex-1 overflow-hidden">

                {/* Sidebar Categorías */}
                <div className="w-24 md:w-32 bg-white border-r border-slate-200 overflow-y-auto pb-6 z-10 shadow-[4px_0_10px_rgba(0,0,0,0.02)] pt-2" style={{ scrollbarWidth: 'none' }}>
                    <div className="flex flex-col p-2 gap-2">
                        {/* "Todo" */}
                        <CategoryButton
                            active={ui.activeCategoryId === 'all'}
                            onClick={() => dispatch({ type: 'SET_ACTIVE_CATEGORY', id: 'all' })}
                            icon={<i className="bi bi-grid-fill text-xl" />}
                            label="Todo"
                        />
                        {categories.map((cat) => (
                            <CategoryButton
                                key={cat.id}
                                active={ui.activeCategoryId === cat.id}
                                onClick={() => dispatch({ type: 'SET_ACTIVE_CATEGORY', id: cat.id })}
                                image={cat.image ? `/storage/${cat.image}` : null}
                                icon={<i className="bi bi-tag-fill text-xl" />}
                                label={cat.name}
                            />
                        ))}
                    </div>
                </div>

                {/* Grid de Productos */}
                <div className="flex-1 bg-slate-50 overflow-y-auto px-4 md:px-6 pb-12 relative" style={{ scrollbarWidth: 'none' }}>
                    {/* Buscador sticky */}
                    <div className="sticky top-0 bg-slate-50/90 backdrop-blur-md pt-4 pb-4 mb-2 z-20">
                        <div className="relative max-w-xl mx-auto shadow-sm">
                            <span className="absolute left-4 top-1/2 -translate-y-1/2 text-indigo-500 font-bold">
                                <i className="bi bi-upc-scan text-lg" />
                            </span>
                            <input
                                ref={barcodeRef}
                                type="text"
                                className="w-full bg-white rounded-2xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/10 pl-12 pr-4 py-3.5 outline-none transition-all font-bold text-slate-700 shadow-sm"
                                placeholder="Escanear código o buscar producto…"
                                autoComplete="off"
                                onChange={(e) => dispatch({ type: 'SET_SEARCH', term: e.target.value })}
                                onKeyDown={(e) => {
                                    if (e.key === 'Enter') {
                                        e.preventDefault();
                                        handleBarcode(e.target.value);
                                    }
                                }}
                            />
                        </div>
                    </div>

                    {/* Loading skeleton */}
                    {loading.products && (
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            {Array.from({ length: 10 }).map((_, i) => (
                                <div key={i} className="bg-white rounded-2xl border border-slate-200 h-44 animate-pulse" />
                            ))}
                        </div>
                    )}

                    {/* Grid de productos */}
                    {!loading.products && (
                        <div className="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 xl:grid-cols-5 gap-4">
                            {filteredProducts.map((product) => (
                                <ProductCard
                                    key={product.id}
                                    product={product}
                                    currency={currency}
                                    loading={loading.cart}
                                    onAdd={() => handleAddItem(product.id)}
                                />
                            ))}
                            {filteredProducts.length === 0 && (
                                <div className="col-span-full py-20 flex flex-col items-center text-slate-300">
                                    <i className="bi bi-search text-6xl" />
                                    <p className="mt-3 font-bold text-slate-400">Sin resultados</p>
                                </div>
                            )}
                        </div>
                    )}
                </div>

                {/* Sidebar Carrito */}
                <PosCart
                    order={order}
                    currency={currency}
                    loading={loading.cart}
                    onUpdateQty={handleUpdateQty}
                    onRemove={handleRemoveItem}
                    onOpenNote={(detail) => dispatch({ type: 'OPEN_MODAL', modal: 'note', noteDetail: detail })}
                    onOpenOptions={() => dispatch({ type: 'OPEN_MODAL', modal: 'options' })}
                    onOpenCheckout={() => dispatch({ type: 'OPEN_MODAL', modal: 'checkout' })}
                    splitUrl={order ? `/pos/order/${order.id}/split-content` : null}
                    precheckUrl={order ? `/pos/order/${order.id}/precheck` : null}
                />
            </div>

            {/* ── Modales ── */}
            <NoteModal
                open={ui.modal === 'note'}
                detail={ui.noteDetail}
                onClose={() => dispatch({ type: 'CLOSE_MODAL' })}
                onSave={handleSaveNote}
            />
            <OptionsModal
                open={ui.modal === 'options'}
                order={order}
                currency={currency}
                onClose={() => dispatch({ type: 'CLOSE_MODAL' })}
                onApply={handleApplyOptions}
            />
            <PosCheckout
                open={ui.modal === 'checkout'}
                order={order}
                clients={clients}
                currency={currency}
                onClose={() => dispatch({ type: 'CLOSE_MODAL' })}
            />
            <MoveTableModal
                open={ui.modal === 'move'}
                order={order}
                tableId={tableId}
                onClose={() => dispatch({ type: 'CLOSE_MODAL' })}
            />
        </div>
    );
}

// ── Sub-componentes locales ──────────────────────────────────────────────────

function CategoryButton({ active, onClick, image, icon, label }) {
    return (
        <button
            onClick={onClick}
            className={`relative w-full flex flex-col items-center justify-center p-3 rounded-2xl transition-all font-bold text-xs border ${
                active
                    ? 'bg-slate-50 text-indigo-600 border-transparent'
                    : 'text-slate-500 hover:bg-slate-50 hover:text-indigo-600 border-transparent'
            }`}
        >
            <div className={`w-12 h-12 rounded-xl mb-2 shadow-sm border overflow-hidden flex items-center justify-center transition-all ${
                active ? 'border-indigo-200 bg-indigo-100 text-indigo-600 scale-105' : 'border-slate-200 bg-slate-100'
            }`}>
                {image ? <img src={image} alt={label} className="w-full h-full object-cover" /> : icon}
            </div>
            <span className="text-center leading-tight line-clamp-2">{label}</span>
        </button>
    );
}

function ProductCard({ product, currency, loading, onAdd }) {
    return (
        <div
            onClick={!loading ? onAdd : undefined}
            className={`bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-lg hover:border-indigo-300 overflow-hidden cursor-pointer transition-all duration-200 hover:-translate-y-1 group relative flex flex-col h-full ${loading ? 'opacity-60 pointer-events-none' : ''}`}
        >
            <div className="relative h-28 w-full overflow-hidden bg-slate-100 shrink-0">
                {product.image ? (
                    <img src={`/storage/${product.image}`} alt={product.name} className="w-full h-full object-cover group-hover:scale-110 transition-transform duration-500" />
                ) : (
                    <div className="w-full h-full flex justify-center items-center">
                        <i className="bi bi-cup-straw text-4xl text-slate-300 group-hover:text-indigo-300 transition-colors" />
                    </div>
                )}
                <div className="absolute inset-x-0 bottom-0 h-1/2 bg-gradient-to-t from-black/50 to-transparent" />
                <div className="absolute bottom-2 right-2">
                    <span className="bg-white/95 backdrop-blur-sm text-indigo-700 font-black px-2 py-1 rounded-lg text-sm shadow-sm border border-white/50">
                        {currency}{Number(product.price).toFixed(0)}
                    </span>
                </div>
                {product.stock !== null && (
                    <div className="absolute top-2 left-2">
                        <span className={`px-2 py-1 rounded-lg text-[10px] font-extrabold shadow-sm border border-white/20 text-white ${product.stock <= 5 ? 'bg-rose-500/90' : 'bg-slate-800/80'} backdrop-blur-sm`}>
                            {product.stock}
                        </span>
                    </div>
                )}
            </div>
            <div className="p-3 text-center flex-1 flex items-center justify-center">
                <h6 className="font-extrabold text-slate-700 text-[13px] leading-tight line-clamp-2 m-0 group-hover:text-indigo-600 transition-colors">
                    {product.name}
                </h6>
            </div>
        </div>
    );
}

function NoteModal({ open, detail, onClose, onSave }) {
    const [note, setNote] = React.useState('');
    const textRef = React.useRef(null);

    React.useEffect(() => {
        if (open) { setNote(detail?.note ?? ''); setTimeout(() => textRef.current?.focus(), 100); }
    }, [open, detail]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={onClose} />
            <div className="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div className="bg-amber-400 p-4 flex justify-between items-center">
                    <h6 className="font-black text-amber-900 m-0 flex items-center gap-2">
                        <i className="bi bi-sticky-fill" /> Nota Cocina
                    </h6>
                    <button onClick={onClose} className="text-amber-900 opacity-70 hover:opacity-100 transition-opacity"><i className="bi bi-x-lg font-bold" /></button>
                </div>
                <div className="p-4">
                    <textarea
                        ref={textRef}
                        value={note}
                        onChange={(e) => setNote(e.target.value)}
                        rows={3}
                        className="w-full border border-slate-200 rounded-xl p-3 outline-none focus:border-amber-400 focus:ring-4 focus:ring-amber-400/20 transition-all text-slate-700 font-bold shadow-sm resize-none"
                        placeholder="Instrucciones especiales para cocina…"
                    />
                </div>
                <div className="p-4 bg-slate-50 border-t border-slate-100">
                    <button
                        onClick={() => onSave(detail?.id, note)}
                        className="w-full bg-amber-500 hover:bg-amber-600 text-white font-black py-3 rounded-xl shadow-lg shadow-amber-500/30 transition-all active:scale-95"
                    >
                        GUARDAR NOTA
                    </button>
                </div>
            </div>
        </div>
    );
}

function OptionsModal({ open, order, currency, onClose, onApply }) {
    const [discount, setDiscount] = React.useState('0');
    const [tip, setTip] = React.useState('0');

    React.useEffect(() => {
        if (open && order) {
            setDiscount(order.discount ?? '0');
            setTip(order.tip ?? '0');
        }
    }, [open, order]);

    if (!open) return null;

    return (
        <div className="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={onClose} />
            <div className="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div className="bg-slate-100 p-4 flex justify-between items-center border-b border-slate-200">
                    <h6 className="font-black text-slate-800 m-0 flex items-center gap-2"><i className="bi bi-sliders" /> Ajustes</h6>
                    <button onClick={onClose} className="text-slate-400 hover:text-slate-600 transition-colors"><i className="bi bi-x-lg font-bold" /></button>
                </div>
                <div className="p-4 space-y-4">
                    {[
                        { label: `Descuento (${currency})`, value: discount, setter: setDiscount, color: 'text-rose-500' },
                        { label: `Propina (${currency})`, value: tip, setter: setTip, color: 'text-emerald-500' },
                    ].map(({ label, value, setter, color }) => (
                        <div key={label}>
                            <label className="block text-sm font-bold text-slate-700 mb-2">{label}</label>
                            <input
                                type="number" step="0.01" min="0"
                                value={value}
                                onChange={(e) => setter(e.target.value)}
                                onClick={(e) => e.target.select()}
                                className={`w-full rounded-xl border border-slate-200 focus:border-indigo-500 focus:ring-4 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all font-black ${color} text-lg`}
                            />
                        </div>
                    ))}
                </div>
                <div className="p-4 bg-slate-50 border-t border-slate-100">
                    <button
                        onClick={() => onApply(discount, tip)}
                        className="w-full bg-slate-800 hover:bg-slate-900 text-white font-black py-3 rounded-xl shadow-lg shadow-slate-500/30 transition-all active:scale-95"
                    >
                        APLICAR CAMBIOS
                    </button>
                </div>
            </div>
        </div>
    );
}

function MoveTableModal({ open, order, tableId, onClose }) {
    if (!open || !order) return null;
    return (
        <div className="fixed inset-0 z-[200] flex items-center justify-center p-4">
            <div className="absolute inset-0 bg-slate-900/60 backdrop-blur-sm" onClick={onClose} />
            <div className="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
                <div className="bg-indigo-600 p-4 flex justify-between items-center">
                    <h6 className="font-black text-white m-0 flex items-center gap-2"><i className="bi bi-arrow-left-right" /> Mover Mesa</h6>
                    <button onClick={onClose} className="text-white opacity-70 hover:opacity-100 transition-opacity"><i className="bi bi-x-lg font-bold" /></button>
                </div>
                {/* Redirige al formulario Blade existente para no duplicar lógica de mesas libres */}
                <div className="p-6 text-center text-slate-600 text-sm">
                    <p className="font-bold mb-4">Para mover la mesa usa el flujo estándar:</p>
                    <a
                        href={`/pos/table/${tableId}`}
                        className="bg-indigo-600 hover:bg-indigo-700 text-white font-black py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 inline-block"
                    >
                        Ir a la comanda
                    </a>
                </div>
            </div>
        </div>
    );
}
