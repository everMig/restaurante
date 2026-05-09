/**
 * usePosApi — Hook centralizado para todas las llamadas al backend del POS.
 * Principio: Un solo lugar para manejar CSRF, errores y loading state.
 */

const getCsrf = () =>
    document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') ?? '';

const defaultHeaders = (extra = {}) => ({
    'Content-Type': 'application/json',
    'X-CSRF-TOKEN': getCsrf(),
    'Accept': 'application/json',
    ...extra,
});

async function apiFetch(url, options = {}) {
    const response = await fetch(url, options);

    if (!response.ok) {
        let message = `Error ${response.status}`;
        try {
            const data = await response.json();
            message = data.message ?? message;
        } catch (_) { /* ignore */ }
        throw new Error(message);
    }

    // Algunos endpoints devuelven 204 (No Content)
    if (response.status === 204) return null;

    return response.json();
}

export function usePosApi() {
    /** GET /api/pos/areas — mapa de mesas */
    const fetchAreas = () =>
        apiFetch('/api/pos/areas');

    /** GET /api/pos/order/{tableId} — orden activa de la mesa */
    const fetchOrder = (tableId) =>
        apiFetch(`/api/pos/order/${tableId}`);

    /** GET /api/pos/products — productos activos y vendibles */
    const fetchProducts = () =>
        apiFetch('/api/pos/products');

    /** GET /api/pos/clients — lista de clientes */
    const fetchClients = () =>
        apiFetch('/api/pos/clients');

    /** POST /pos/order/{tableId}/add */
    const addItem = (tableId, productId) =>
        apiFetch(`/pos/order/${tableId}/add`, {
            method: 'POST',
            headers: defaultHeaders(),
            body: JSON.stringify({ product_id: productId }),
        });

    /** POST /pos/order/{tableId}/barcode */
    const addByBarcode = (tableId, barcode) =>
        apiFetch(`/pos/order/${tableId}/barcode`, {
            method: 'POST',
            headers: defaultHeaders(),
            body: JSON.stringify({ barcode }),
        });

    /** POST /pos/detail/{detailId}/update */
    const updateQty = (detailId, quantity) =>
        apiFetch(`/pos/detail/${detailId}/update`, {
            method: 'POST',
            headers: defaultHeaders(),
            body: JSON.stringify({ quantity }),
        });

    /** DELETE /pos/detail/{detailId} */
    const removeItem = (detailId) =>
        apiFetch(`/pos/detail/${detailId}`, {
            method: 'DELETE',
            headers: defaultHeaders({ 'Content-Type': undefined }),
        });

    /** POST /pos/detail/{detailId}/note */
    const updateNote = (detailId, note) =>
        apiFetch(`/pos/detail/${detailId}/note`, {
            method: 'POST',
            headers: defaultHeaders(),
            body: JSON.stringify({ note }),
        });

    /** POST /pos/order/{orderId}/discount */
    const applyOptions = (orderId, discount, tip) =>
        apiFetch(`/pos/order/${orderId}/discount`, {
            method: 'POST',
            headers: defaultHeaders(),
            body: JSON.stringify({ discount, tip }),
        });

    /** POST /pos/order/{orderId}/move */
    const moveTable = (orderId, targetTableId) =>
        apiFetch(`/pos/order/${orderId}/move`, {
            method: 'POST',
            headers: defaultHeaders(),
            body: JSON.stringify({ target_table_id: targetTableId }),
        });

    return {
        fetchAreas,
        fetchOrder,
        fetchProducts,
        fetchClients,
        addItem,
        addByBarcode,
        updateQty,
        removeItem,
        updateNote,
        applyOptions,
        moveTable,
    };
}
