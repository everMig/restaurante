@extends('layouts.app')

@section('content')
<div class="w-full flex justify-center">
    <div class="w-full max-w-4xl">
        
        <div class="mb-8">
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-gear-fill me-2 text-indigo-500"></i>Configuración</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Personaliza la identidad y región de tu negocio</p>
        </div>

        <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
            <div class="p-8">
                <form action="{{ route('settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <h5 class="text-lg font-extrabold text-indigo-600 mb-6 flex items-center gap-2"><i class="bi bi-shop"></i> Datos de la Empresa</h5>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nombre del Restaurante</label>
                            <input type="text" name="company_name" value="{{ $settings['company_name'] ?? '' }}" placeholder="Ej: Restaurante Vito" required class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Teléfono / Pedidos</label>
                            <input type="text" name="company_phone" value="{{ $settings['company_phone'] ?? '' }}" placeholder="Ej: 999-888-777" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Dirección</label>
                            <input type="text" name="company_address" value="{{ $settings['company_address'] ?? '' }}" placeholder="Ej: Av. Principal 123, Ica" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                    </div>

                    <hr class="border-slate-100 mb-8">

                    <h5 class="text-lg font-extrabold text-indigo-600 mb-6 flex items-center gap-2"><i class="bi bi-globe-americas"></i> Región y Sistema</h5>
                    <div class="grid grid-cols-1 md:grid-cols-12 gap-6 mb-8">
                        <div class="md:col-span-8">
                            <label class="block text-sm font-bold text-slate-700 mb-2"><i class="bi bi-clock"></i> Zona Horaria</label>
                            <select name="timezone" class="w-full rounded-xl border border-indigo-200 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-indigo-50/30 text-indigo-900 font-bold">
                                @foreach($timezones as $tz => $label)
                                    <option value="{{ $tz }}" {{ ($settings['timezone'] ?? 'America/Lima') == $tz ? 'selected' : '' }}>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                            <p class="text-xs text-slate-500 mt-2 font-medium">Hora actual del sistema: <strong class="text-slate-700">{{ \Carbon\Carbon::now()->format('H:i:s') }}</strong></p>
                        </div>

                        <div class="md:col-span-4" x-data="{ 
                            currencyType: '{{ in_array($settings['currency_symbol'] ?? 'S/', ['S/', '$', 'Bs.', '€']) ? ($settings['currency_symbol'] ?? 'S/') : 'custom' }}',
                            customValue: '{{ !in_array($settings['currency_symbol'] ?? 'S/', ['S/', '$', 'Bs.', '€']) ? ($settings['currency_symbol'] ?? '') : '' }}'
                        }">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Símbolo de Moneda</label>
                            
                            <!-- Select Principal -->
                            <select x-model="currencyType" :name="currencyType === 'custom' ? '' : 'currency_symbol'" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-white font-extrabold text-slate-800 mb-2">
                                <option value="Bs.">Bs. (Bolivianos)</option>
                                <option value="$">$ (Dólares / Pesos)</option>
                                <option value="S/">S/ (Soles)</option>
                                <option value="€">€ (Euros)</option>
                                <option value="custom">Otra moneda...</option>
                            </select>

                            <!-- Input para Moneda Personalizada -->
                            <div x-show="currencyType === 'custom'" x-transition>
                                <input type="text" x-model="customValue" :name="currencyType === 'custom' ? 'currency_symbol' : ''" placeholder="Escribe tu moneda (Ej: CLP, Mex$)" class="w-full rounded-xl border border-indigo-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-2 outline-none transition-all bg-indigo-50/30 font-bold text-indigo-800">
                            </div>
                        </div>

                        <div class="md:col-span-12">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mensaje Pie de Ticket</label>
                            <input type="text" name="ticket_footer" value="{{ $settings['ticket_footer'] ?? '¡Gracias por su visita!' }}" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                    </div>

                    <hr class="border-slate-100 mb-8">

                    <h5 class="text-lg font-extrabold text-indigo-600 mb-6 flex items-center gap-2"><i class="bi bi-image"></i> Logotipo</h5>
                    <div class="flex flex-col md:flex-row items-start md:items-center gap-6">
                        <div class="flex-1 w-full">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Subir Logo (Ticket y Sistema)</label>
                            <div class="border-2 border-dashed border-slate-200 rounded-2xl p-4 text-center hover:bg-slate-50 transition-colors">
                                <input type="file" name="company_logo" accept="image/*" class="w-full text-sm text-slate-500 file:mr-4 file:py-2.5 file:px-5 file:rounded-xl file:border-0 file:text-sm file:font-bold file:bg-indigo-50 file:text-indigo-600 hover:file:bg-indigo-100 cursor-pointer outline-none">
                                <p class="text-xs text-slate-400 mt-2 font-medium">Formatos soportados: JPG, PNG, GIF. Tamaño máximo: 2MB.</p>
                            </div>
                        </div>
                        <div class="flex-shrink-0 mt-4 md:mt-0">
                            @if(isset($settings['company_logo']) && $settings['company_logo'])
                                <div class="p-2 bg-white border border-slate-200 rounded-2xl shadow-sm relative group">
                                    <img src="{{ asset('storage/'.$settings['company_logo']) }}" onerror="this.onerror=null; this.outerHTML='<div class=\'w-24 h-24 rounded-2xl bg-rose-50 border border-rose-200 flex flex-col items-center justify-center text-rose-400 shadow-sm\'><i class=\'bi bi-image-fill text-3xl mb-1\'></i><span class=\'text-[10px] font-bold text-center leading-tight\'>Error<br>Cargando</span></div>';" class="h-20 w-auto object-contain rounded-xl">
                                </div>
                            @else
                                <div class="w-24 h-24 rounded-2xl bg-slate-50 border border-slate-200 flex items-center justify-center text-slate-300 shadow-sm">
                                    <i class="bi bi-image text-3xl"></i>
                                </div>
                            @endif
                        </div>
                    </div>

                    <div class="mt-10 flex justify-end pt-6 border-t border-slate-100">
                        <button type="submit" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
                            <i class="bi bi-save"></i> Guardar Configuración
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection