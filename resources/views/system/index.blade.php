@extends('layouts.app')

@section('content')
<div class="w-full flex justify-center">
    <div class="w-full max-w-2xl mt-8">
        <div class="bg-white rounded-3xl shadow-xl shadow-rose-500/10 border-2 border-rose-200 overflow-hidden relative">
            
            <!-- Diagonal stripes background for danger warning -->
            <div class="absolute inset-0 opacity-[0.03] pointer-events-none" style="background-image: repeating-linear-gradient(45deg, #000 25%, transparent 25%, transparent 75%, #000 75%, #000), repeating-linear-gradient(45deg, #000 25%, #fff 25%, #fff 75%, #000 75%, #000); background-position: 0 0, 10px 10px; background-size: 20px 20px;"></div>

            <div class="bg-rose-600 text-white p-6 relative z-10 flex items-center justify-center gap-3">
                <i class="bi bi-exclamation-triangle-fill text-3xl animate-pulse"></i>
                <h2 class="text-xl font-black uppercase tracking-widest m-0">Zona de Peligro: Reinicio del Sistema</h2>
            </div>
            
            <div class="p-8 md:p-10 text-center relative z-10">
                
                <h3 class="text-2xl font-black text-rose-600 mb-4 tracking-tight">¿Estás listo para inaugurar?</h3>
                <p class="text-slate-500 text-lg font-medium leading-relaxed">Esta acción eliminará todos los datos de prueba para dejar el sistema listo para producción.</p>

                <div class="bg-amber-50 border border-amber-200 rounded-2xl p-6 mt-6 text-left shadow-inner">
                    <strong class="text-amber-800 font-black text-lg block mb-3 flex items-center gap-2"><i class="bi bi-trash text-xl"></i> Se eliminarán permanentemente:</strong>
                    <ul class="space-y-2 text-amber-700 font-bold ml-1 list-none">
                        <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-amber-500 text-sm"></i> {{ $counts['orders'] }} Ventas y pedidos registrados.</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-amber-500 text-sm"></i> {{ $counts['reservations'] }} Reservas de mesa.</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-amber-500 text-sm"></i> {{ $counts['logs'] }} Movimientos de Kardex.</li>
                        <li class="flex items-center gap-2"><i class="bi bi-check-circle-fill text-amber-500 text-sm"></i> El <span class="font-black underline decoration-amber-400">Stock</span> de todos los productos volverá a <span class="font-black text-rose-600 bg-white px-2 py-0.5 rounded shadow-sm border border-rose-100">0</span>.</li>
                    </ul>
                </div>
                
                <p class="mt-6 text-sm text-slate-500 font-bold bg-slate-50 py-3 px-4 rounded-xl border border-slate-200 shadow-sm">
                    <i class="bi bi-info-circle-fill text-indigo-500 mr-1"></i> Tus Usuarios, Productos, Mesas, Clientes y Configuración <strong class="text-indigo-600 font-black uppercase">NO</strong> se borrarán.
                </p>

                <hr class="border-slate-200 my-8">

                <form action="{{ route('system.reset') }}" method="POST" class="mt-4" onsubmit="window.confirmAction(this, '¿ESTÁS 100% SEGURO? NO HAY VUELTA ATRÁS.', event)">
                    @csrf
                    <div class="mb-6">
                        <label class="block text-sm font-bold text-slate-700 mb-3">Ingresa tu contraseña para confirmar:</label>
                        <input type="password" name="password" class="w-full md:w-2/3 mx-auto rounded-xl border-2 border-slate-300 focus:border-rose-500 focus:ring-4 focus:ring-rose-500/20 shadow-sm px-4 py-3 outline-none transition-all text-center font-bold text-slate-800 text-lg tracking-widest" required placeholder="Tu contraseña actual">
                    </div>
                    <button type="submit" class="w-full bg-rose-600 hover:bg-rose-700 text-white font-black py-4 px-6 rounded-xl shadow-lg shadow-rose-500/30 transition-all active:scale-95 flex justify-center items-center gap-3 text-lg uppercase tracking-widest border border-rose-700/50">
                        <i class="bi bi-trash3-fill text-2xl"></i> BORRAR TODO E INICIAR DE CERO
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection