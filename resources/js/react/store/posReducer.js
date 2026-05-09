/**
 * posReducer — Estado global del POS manejado con useReducer.
 * Patrón: Redux-lite sin dependencias externas (React 18 es suficiente).
 *
 * Shapes:
 *   areas:    Area[]        — salones con mesas
 *   order:    Order|null    — orden activa de la mesa seleccionada
 *   products: Product[]     — catálogo filtrado/ordenado
 *   clients:  Client[]      — para el autocomplete del checkout
 *   loading:  object        — flags de loading por acción
 *   error:    string|null   — último error legible
 *   ui:       object        — estado de modales y filtros de UI
 */

export const initialState = {
    areas: [],
    order: null,
    products: [],
    clients: [],
    loading: {
        areas: false,
        order: false,
        products: false,
        cart: false,        // cualquier mutación del carrito
        checkout: false,
    },
    error: null,
    ui: {
        activeAreaId: null,
        activeCategoryId: 'all',
        searchTerm: '',
        modal: null,        // 'note' | 'options' | 'checkout' | 'move' | null
        noteDetail: null,   // { id, note } del item que está siendo editado
    },
};

export function posReducer(state, action) {
    switch (action.type) {

        // ── Loading flags ────────────────────────────────────────────
        case 'SET_LOADING':
            return { ...state, loading: { ...state.loading, [action.key]: action.value } };

        case 'SET_ERROR':
            return { ...state, error: action.error };

        case 'CLEAR_ERROR':
            return { ...state, error: null };

        // ── Data hydration ───────────────────────────────────────────
        case 'SET_AREAS':
            return {
                ...state,
                areas: action.areas,
                ui: {
                    ...state.ui,
                    activeAreaId: state.ui.activeAreaId ?? action.areas[0]?.id ?? null,
                },
            };

        case 'SET_ORDER':
            return { ...state, order: action.order };

        case 'SET_PRODUCTS':
            return { ...state, products: action.products };

        case 'SET_CLIENTS':
            return { ...state, clients: action.clients };

        // ── UI ───────────────────────────────────────────────────────
        case 'SET_ACTIVE_AREA':
            return { ...state, ui: { ...state.ui, activeAreaId: action.id } };

        case 'SET_ACTIVE_CATEGORY':
            return { ...state, ui: { ...state.ui, activeCategoryId: action.id } };

        case 'SET_SEARCH':
            return { ...state, ui: { ...state.ui, searchTerm: action.term } };

        case 'OPEN_MODAL':
            return { ...state, ui: { ...state.ui, modal: action.modal, noteDetail: action.noteDetail ?? null } };

        case 'CLOSE_MODAL':
            return { ...state, ui: { ...state.ui, modal: null, noteDetail: null } };

        default:
            return state;
    }
}
