<?php

namespace App\Providers;

use App\Models\Expense;
use App\Models\Order;
use App\Models\OrderDetail;
use App\Models\Product;
use App\Models\Reservation;
use App\Models\User;
use App\Policies\ExpensePolicy;
use App\Policies\OrderDetailPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ProductPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\UserPolicy;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Schema;
use App\Models\Setting;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::policy(User::class, UserPolicy::class);
        Gate::policy(Product::class, ProductPolicy::class);
        Gate::policy(Expense::class, ExpensePolicy::class);
        Gate::policy(Reservation::class, ReservationPolicy::class);
        Gate::policy(Order::class, OrderPolicy::class);
        Gate::policy(OrderDetail::class, OrderDetailPolicy::class);

        Paginator::useTailwind();

        // Compartir el símbolo de moneda con TODAS las vistas
        // Usamos un try-catch para evitar errores si la tabla settings aún no existe (durante migraciones)
        try {
            if (Schema::hasTable('settings')) {
                $currency = Setting::where('key', 'currency_symbol')->value('value') ?? '$';
                
                // Ahora la variable $currency estará disponible en cualquier archivo .blade.php
                View::share('currency', $currency);
            }
        } catch (\Exception $e) {
            // Si falla (ej: base de datos caída), usamos default
            View::share('currency', '$');
        }
    }
}