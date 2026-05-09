<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\InventoryLog;
use Throwable;

class SystemController extends Controller
{
    public function index()
    {
        // Contamos qué vamos a borrar para informar al usuario
        $counts = [
            'orders' => Order::count(),
            'reservations' => Reservation::count(),
            'logs' => InventoryLog::count(),
        ];
        return view('system.index', compact('counts'));
    }

    public function resetData(Request $request)
    {
        $request->validate(['password' => 'required']);
        $currentUser = $request->user();

        // Verificación de seguridad simple: La contraseña debe ser la del usuario actual
        if (! $currentUser || ! password_verify($request->password, $currentUser->password)) {
            return back()->with('error', 'Contraseña incorrecta. No se realizaron cambios.');
        }

        $constraintsDisabled = false;

        try {
            Schema::disableForeignKeyConstraints();
            $constraintsDisabled = true;

            // 1. Borrar Ventas y Detalles
            DB::table('order_details')->truncate();
            DB::table('orders')->truncate();

            // 2. Borrar Reservas
            DB::table('reservations')->truncate();

            // 3. Borrar Kardex y reiniciar stock operativo
            DB::table('inventory_logs')->truncate();
            DB::table('products')->update(['stock' => 0]);

            return back()->with('success', '¡Sistema reiniciado! Ventas, Reservas y Stock han vuelto a cero.');
        } catch (Throwable $exception) {
            report($exception);

            return back()->with('error', 'No se pudo completar el reinicio del sistema.');
        } finally {
            if ($constraintsDisabled) {
                Schema::enableForeignKeyConstraints();
            }
        }
    }
}