import React, { useState } from 'react';
import { Card, CardContent, CardHeader, CardTitle } from '../components/ui/Card';
import { Badge } from '../components/ui/Badge';
import { Button } from '../components/ui/Button';
import { Input } from '../components/ui/Input';
import { 
    Package, Plus, Clock, Search, Image as ImageIcon, 
    ArrowLeftRight, Edit, Trash2, ShieldAlert
} from 'lucide-react';

export default function ProductList({ 
    productsData = [], 
    routes = {}, 
    csrfToken = '',
    currency = 'S/'
}) {
    const [searchTerm, setSearchTerm] = useState('');
    const [adjustStockModal, setAdjustStockModal] = useState(null);

    const filteredProducts = productsData.filter(p => 
        p.name.toLowerCase().includes(searchTerm.toLowerCase()) || 
        (p.barcode && p.barcode.includes(searchTerm))
    );

    return (
        <div className="space-y-6">
            <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div>
                    <h2 className="text-2xl font-bold text-slate-900 flex items-center gap-2">
                        <Package className="h-6 w-6 text-indigo-600" />
                        Inventario de Productos
                    </h2>
                    <p className="text-slate-500">Gestión de carta y existencias</p>
                </div>
                <div className="flex gap-2">
                    <a href={routes.kardex}>
                        <Button variant="secondary" className="shadow-sm">
                            <Clock className="w-4 h-4 mr-2" /> Ver Kardex
                        </Button>
                    </a>
                    <a href={routes.create}>
                        <Button className="shadow-sm">
                            <Plus className="w-4 h-4 mr-2" /> Nuevo Producto
                        </Button>
                    </a>
                </div>
            </div>

            <Card className="shadow-sm border-0 ring-1 ring-slate-200">
                <CardHeader className="bg-slate-50 border-b border-slate-100 pb-4">
                    <div className="relative max-w-sm">
                        <Search className="absolute left-3 top-1/2 -translate-y-1/2 h-4 w-4 text-slate-400" />
                        <Input 
                            placeholder="Buscar producto por nombre o código..." 
                            className="pl-9 bg-white"
                            value={searchTerm}
                            onChange={(e) => setSearchTerm(e.target.value)}
                        />
                    </div>
                </CardHeader>
                <CardContent className="p-0">
                    <div className="overflow-x-auto">
                        <table className="w-full text-sm text-left">
                            <thead className="bg-slate-50/50 text-slate-500 font-semibold border-b border-slate-100">
                                <tr>
                                    <th className="px-6 py-4">Producto</th>
                                    <th className="px-6 py-4">Categoría</th>
                                    <th className="px-6 py-4">Precio</th>
                                    <th className="px-6 py-4">Stock</th>
                                    <th className="px-6 py-4 text-center">Estado</th>
                                    <th className="px-6 py-4 text-right">Acciones</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-slate-100">
                                {filteredProducts.map(product => (
                                    <tr key={product.id} className="hover:bg-slate-50/80 transition-colors">
                                        <td className="px-6 py-4">
                                            <div className="flex items-center gap-3">
                                                {product.image ? (
                                                    <img src={`/storage/${product.image}`} alt={product.name} className="w-12 h-12 rounded-lg object-cover border border-slate-200 shadow-sm" />
                                                ) : (
                                                    <div className="w-12 h-12 rounded-lg bg-slate-100 border border-slate-200 flex items-center justify-center text-slate-400">
                                                        <ImageIcon className="w-5 h-5" />
                                                    </div>
                                                )}
                                                <div>
                                                    <div className="font-semibold text-slate-900">{product.name}</div>
                                                    {product.barcode && (
                                                        <div className="text-xs text-slate-500 font-mono mt-0.5">{product.barcode}</div>
                                                    )}
                                                    {!product.is_saleable && (
                                                        <Badge variant="secondary" className="mt-1 text-[10px] py-0 h-4">Solo Insumo</Badge>
                                                    )}
                                                </div>
                                            </div>
                                        </td>
                                        <td className="px-6 py-4">
                                            <Badge variant="outline" className="bg-white">{product.category?.name || 'Sin categoría'}</Badge>
                                        </td>
                                        <td className="px-6 py-4 font-bold text-indigo-600">
                                            {currency} {Number(product.price).toFixed(2)}
                                        </td>
                                        <td className="px-6 py-4">
                                            {product.stock === null ? (
                                                <span className="text-slate-400">--</span>
                                            ) : product.stock <= 5 ? (
                                                <Badge variant="destructive" className="bg-red-50 text-red-700 border-red-200">Bajo: {product.stock}</Badge>
                                            ) : (
                                                <Badge variant="success" className="bg-emerald-50 text-emerald-700 border-emerald-200 font-bold">{product.stock}</Badge>
                                            )}
                                        </td>
                                        <td className="px-6 py-4 text-center">
                                            <form action={product.toggle_url} method="POST">
                                                <input type="hidden" name="_token" value={csrfToken} />
                                                <button type="submit" className={`text-xs font-bold px-3 py-1 rounded-full border transition-colors ${product.is_active ? 'bg-emerald-50 text-emerald-700 border-emerald-200 hover:bg-emerald-100' : 'bg-slate-50 text-slate-500 border-slate-200 hover:bg-slate-100'}`}>
                                                    {product.is_active ? 'ACTIVO' : 'INACTIVO'}
                                                </button>
                                            </form>
                                        </td>
                                        <td className="px-6 py-4 text-right">
                                            <div className="flex items-center justify-end gap-2">
                                                <Button variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-indigo-600 hover:bg-indigo-50" title="Ajustar Stock" onClick={() => setAdjustStockModal(product)}>
                                                    <ArrowLeftRight className="w-4 h-4" />
                                                </Button>
                                                <a href={product.edit_url}>
                                                    <Button variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-blue-600 hover:bg-blue-50" title="Editar">
                                                        <Edit className="w-4 h-4" />
                                                    </Button>
                                                </a>
                                                <form action={product.delete_url} method="POST" onSubmit={(e) => window.confirmAction(e.target, '¿Eliminar producto?', e)}>
                                                    <input type="hidden" name="_token" value={csrfToken} />
                                                    <input type="hidden" name="_method" value="DELETE" />
                                                    <Button type="submit" variant="ghost" size="icon" className="h-8 w-8 text-slate-500 hover:text-red-600 hover:bg-red-50" title="Eliminar">
                                                        <Trash2 className="w-4 h-4" />
                                                    </Button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                ))}
                                {filteredProducts.length === 0 && (
                                    <tr>
                                        <td colSpan="6" className="px-6 py-12 text-center text-slate-500">
                                            <ShieldAlert className="w-12 h-12 mx-auto text-slate-300 mb-3" />
                                            <p className="text-base font-medium text-slate-900">No hay productos</p>
                                            <p className="text-sm">No se encontraron productos que coincidan con la búsqueda.</p>
                                        </td>
                                    </tr>
                                )}
                            </tbody>
                        </table>
                    </div>
                </CardContent>
            </Card>

            {/* Modal para Ajustar Stock */}
            {adjustStockModal && (
                <div className="fixed inset-0 z-50 flex items-center justify-center bg-slate-900/50 backdrop-blur-sm p-4">
                    <Card className="w-full max-w-sm shadow-2xl">
                        <form action={adjustStockModal.adjust_url} method="POST">
                            <input type="hidden" name="_token" value={csrfToken} />
                            <CardHeader className="bg-slate-50 border-b border-slate-100 pb-4">
                                <CardTitle className="text-lg">Ajustar Stock</CardTitle>
                            </CardHeader>
                            <CardContent className="pt-6">
                                <p className="text-sm text-slate-600 mb-4">
                                    Producto: <strong className="text-slate-900">{adjustStockModal.name}</strong>
                                </p>
                                <div className="flex gap-3">
                                    <select name="type" className="flex h-10 w-28 rounded-lg border border-slate-300 bg-white px-3 py-2 text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500">
                                        <option value="add">➕ Añadir</option>
                                        <option value="sub">➖ Restar</option>
                                    </select>
                                    <Input type="number" name="quantity" placeholder="Cantidad" required min="1" className="flex-1" />
                                </div>
                            </CardContent>
                            <div className="flex justify-end gap-2 p-4 border-t border-slate-100 bg-slate-50/50">
                                <Button variant="ghost" type="button" onClick={() => setAdjustStockModal(null)}>Cancelar</Button>
                                <Button type="submit">Guardar Ajuste</Button>
                            </div>
                        </form>
                    </Card>
                </div>
            )}
        </div>
    );
}
