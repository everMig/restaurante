<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Category;
use App\Models\Product;
use App\Models\Area;
use App\Models\Table;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 0. Ejecutar SettingSeeder si existe
        if (class_exists(SettingSeeder::class)) {
            $this->call(SettingSeeder::class);
        }

        // 1. Crear Usuarios (Roles)
        $admin = User::updateOrCreate(['email' => 'admin@admin.com'], [
            'name' => 'Administrador',
            'password' => Hash::make('password'),
            'role' => 'admin'
        ]);
        
        $cajero = User::updateOrCreate(['email' => 'cajero@restaurante.com'], [
            'name' => 'Cajero Principal',
            'password' => Hash::make('password'),
            'role' => 'cashier'
        ]);
        
        $mozo = User::updateOrCreate(['email' => 'mozo@restaurante.com'], [
            'name' => 'Juan Mozo',
            'password' => Hash::make('password'),
            'role' => 'waiter'
        ]);

        // 2. Crear Clientes Frecuentes
        $client1 = \App\Models\Client::updateOrCreate(['document_number' => '12345678'], [
            'name' => 'Consumidor Final',
            'email' => 'cliente@ejemplo.com',
            'phone' => '999888777',
            'address' => 'Av. Principal 123'
        ]);
        $client2 = \App\Models\Client::updateOrCreate(['document_number' => '87654321'], [
            'name' => 'Empresa SAC',
            'email' => 'empresa@sac.com',
            'phone' => '111222333',
            'address' => 'Calle Falsa 123'
        ]);

        // 3. Crear Áreas (Salones) y Mesas
        $salon = Area::firstOrCreate(['name' => 'Salón Principal']);
        $terraza = Area::firstOrCreate(['name' => 'Terraza']);

        for ($i = 1; $i <= 5; $i++) {
            Table::firstOrCreate([
                'area_id' => $salon->id,
                'name' => 'Mesa ' . $i
            ], ['seats' => 4, 'status' => 'available']);
        }
        for ($i = 1; $i <= 3; $i++) {
            Table::firstOrCreate([
                'area_id' => $terraza->id,
                'name' => 'T-' . $i
            ], ['seats' => 2, 'status' => 'available']);
        }

        // 4. Crear Categorías
        $catBebidas = Category::firstOrCreate(['name' => 'Bebidas'], ['is_active' => true]);
        $catComidas = Category::firstOrCreate(['name' => 'Hamburguesas'], ['is_active' => true]);
        $catPostres = Category::firstOrCreate(['name' => 'Postres'], ['is_active' => true]);
        $catInsumos = Category::firstOrCreate(['name' => 'Insumos'], ['is_active' => true]);

        // 5. Crear Insumos (Productos no vendibles)
        $pan = Product::firstOrCreate(['name' => 'Pan de Hamburguesa'], [
            'category_id' => $catInsumos->id,
            'price' => 0.50,
            'stock' => 100,
            'is_saleable' => false,
            'is_active' => true
        ]);
        $carne = Product::firstOrCreate(['name' => 'Carne 200g'], [
            'category_id' => $catInsumos->id,
            'price' => 2.50,
            'stock' => 50,
            'is_saleable' => false,
            'is_active' => true
        ]);
        $queso = Product::firstOrCreate(['name' => 'Queso Cheddar'], [
            'category_id' => $catInsumos->id,
            'price' => 0.30,
            'stock' => 200,
            'is_saleable' => false,
            'is_active' => true
        ]);

        // 6. Crear Productos Finales (Vendibles)
        $hamburguesa = Product::firstOrCreate(['name' => 'Hamburguesa Clásica'], [
            'category_id' => $catComidas->id,
            'price' => 12.50,
            'stock' => null, // Controlado por receta
            'is_saleable' => true,
            'is_active' => true
        ]);
        // Receta: 1 Pan, 1 Carne
        if ($hamburguesa->ingredients()->count() === 0) {
            $hamburguesa->ingredients()->attach([
                $pan->id => ['quantity' => 1],
                $carne->id => ['quantity' => 1]
            ]);
        }

        $hamburguesaDoble = Product::firstOrCreate(['name' => 'Hamburguesa Doble Queso'], [
            'category_id' => $catComidas->id,
            'price' => 16.00,
            'stock' => null, 
            'is_saleable' => true,
            'is_active' => true
        ]);
        // Receta: 1 Pan, 2 Carne, 2 Queso
        if ($hamburguesaDoble->ingredients()->count() === 0) {
            $hamburguesaDoble->ingredients()->attach([
                $pan->id => ['quantity' => 1],
                $carne->id => ['quantity' => 2],
                $queso->id => ['quantity' => 2]
            ]);
        }

        $coca = Product::firstOrCreate(['name' => 'Coca Cola 500ml'], [
            'category_id' => $catBebidas->id,
            'price' => 3.50,
            'stock' => 100,
            'is_saleable' => true,
            'is_active' => true
        ]);
        
        $limonada = Product::firstOrCreate(['name' => 'Limonada Frozen'], [
            'category_id' => $catBebidas->id,
            'price' => 5.00,
            'stock' => null, // Ilimitado
            'is_saleable' => true,
            'is_active' => true
        ]);

        // 7. Generar Datos de Prueba (Ventas, Órdenes y Gastos para Dashboard)
        // Crear 3 ventas pasadas completadas de hoy
        for ($i = 1; $i <= 3; $i++) {
            $order = \App\Models\Order::create([
                'table_id' => Table::inRandomOrder()->first()->id,
                'user_id' => $cajero->id,
                'client_id' => $client1->id,
                'client_name' => $client1->name,
                'status' => 'completed',
                'total' => 0,
                'payment_method' => $i % 2 == 0 ? 'card' : 'cash',
                'document_type' => 'Boleta',
                'created_at' => now()->subHours(rand(1, 8))
            ]);

            $order->details()->create([
                'product_id' => $hamburguesa->id,
                'quantity' => 2,
                'price' => $hamburguesa->price,
                'status' => 'served'
            ]);
            $order->details()->create([
                'product_id' => $coca->id,
                'quantity' => 2,
                'price' => $coca->price,
                'status' => 'served'
            ]);

            $order->update(['total' => (2 * $hamburguesa->price) + (2 * $coca->price), 'received_amount' => (2 * $hamburguesa->price) + (2 * $coca->price)]);
        }

        // Crear 1 orden pendiente (Para probar KDS y POS)
        $pendingTable = Table::where('area_id', $salon->id)->first();
        $pendingOrder = \App\Models\Order::create([
            'table_id' => $pendingTable->id,
            'user_id' => $mozo->id,
            'status' => 'pending',
            'total' => $hamburguesaDoble->price + $limonada->price,
        ]);
        
        $pendingOrder->details()->create([
            'product_id' => $hamburguesaDoble->id,
            'quantity' => 1,
            'price' => $hamburguesaDoble->price,
            'status' => 'pending', // Para que aparezca en el KDS
            'note' => 'Sin cebolla'
        ]);
        $pendingOrder->details()->create([
            'product_id' => $limonada->id,
            'quantity' => 1,
            'price' => $limonada->price,
            'status' => 'served', 
        ]);

        // Crear 1 Gasto para caja
        \App\Models\Expense::create([
            'user_id' => $admin->id ?? 1,
            'description' => 'Pago a proveedor de carnes',
            'amount' => 50.00,
            'created_at' => now()->subHours(2)
        ]);
    }
}