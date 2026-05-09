<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Product;
use App\Models\Category;
use App\Models\InventoryLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class ProductController extends Controller
{
    public function index()
    {
        $this->authorize('viewAny', Product::class);

        // Listamos productos con su categoría, ordenados por los más nuevos
        $products = Product::with('category')->orderBy('created_at', 'desc')->paginate(10);
        $currency = \App\Models\Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        return view('products.index', compact('products', 'currency'));
    }

    public function create()
    {
        $this->authorize('create', Product::class);

        $categories = Category::where('is_active', true)->get();
        $currency = \App\Models\Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        return view('products.create', compact('categories', 'currency'));
    }

    public function store(StoreProductRequest $request)
    {
        $this->authorize('create', Product::class);

        $data = $request->validated();
        
        // 2. Manejo de Imagen
        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        // Checkbox de "Disponible en POS" (si no viene, es false)
        $data['is_saleable'] = $request->has('is_saleable');
        $data['is_active'] = true;

        // 3. Crear Producto
        $product = Product::create($data);

        // 4. Registro inicial en Kardex si hay stock
        if($request->stock > 0) {
            InventoryLog::create([
                'product_id' => $product->id,
                'user_id' => Auth::id(),
                'type' => 'entry',
                'quantity' => $request->stock,
                'old_stock' => 0,
                'new_stock' => $request->stock,
                'note' => 'Inventario Inicial'
            ]);
        }

        return redirect()->route('products.index')->with('success', 'Producto creado correctamente.');
    }

    public function edit(Product $product)
    {
        $this->authorize('update', $product);

        $categories = Category::where('is_active', true)->get();
        // Productos que pueden ser insumos (todos menos él mismo)
        $ingredients = Product::where('id', '!=', $product->id)->where('is_active', true)->get();
        $currency = \App\Models\Setting::where('key', 'currency_symbol')->value('value') ?? 'S/';
        
        return view('products.edit', compact('product', 'categories', 'ingredients', 'currency'));
    }

    public function update(UpdateProductRequest $request, Product $product)
    {
        $this->authorize('update', $product);

        $data = $request->validated();

        // 2. Manejo de Imagen
        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_saleable'] = $request->has('is_saleable');

        // 3. Actualizar
        $product->update($data);

        // Actualizar receta/insumos (Si enviaste array de ingredientes)
        if ($request->has('ingredients')) {
            $syncData = [];
            foreach ($request->ingredients as $id => $qty) {
                if ($qty > 0) $syncData[$id] = ['quantity' => $qty];
            }
            $product->ingredients()->sync($syncData);
        }

        return redirect()->route('products.index')->with('success', 'Producto actualizado.');
    }

    public function destroy(Product $product)
    {
        $this->authorize('delete', $product);

        // Eliminado lógico (desactivar) en lugar de borrar para mantener historial
        $product->update(['is_active' => false]);
        return redirect()->route('products.index')->with('success', 'Producto eliminado (desactivado).');
    }

    // Funciones extra para ajustes rápidos
    public function toggleStatus(Product $product)
    {
        $this->authorize('toggleStatus', $product);

        $product->update(['is_active' => !$product->is_active]);
        return back();
    }

    public function adjustStock(Request $request, Product $product)
    {
        $this->authorize('adjustStock', $product);

        $request->validate(['quantity' => 'required|integer|min:1', 'type' => 'required|in:add,sub']);
        
        $oldStock = $product->stock;
        $qty = $request->quantity;
        
        if ($request->type === 'sub') {
            $product->decrement('stock', $qty);
            $newStock = $oldStock - $qty;
            $type = 'adjustment_out';
        } else {
            $product->increment('stock', $qty);
            $newStock = $oldStock + $qty;
            $type = 'adjustment_in';
        }

        InventoryLog::create([
            'product_id' => $product->id,
            'user_id' => Auth::id(),
            'type' => $type,
            'quantity' => ($request->type === 'sub' ? -$qty : $qty),
            'old_stock' => $oldStock,
            'new_stock' => $newStock,
            'note' => 'Ajuste manual desde panel'
        ]);

        return back()->with('success', 'Stock ajustado.');
    }
}