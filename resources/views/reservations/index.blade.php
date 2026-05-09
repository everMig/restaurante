@extends('layouts.app')

@section('content')
<div class="w-full" x-data="{ createModalOpen: false }">
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-calendar-check-fill me-2 text-indigo-500"></i>Reservas</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Agenda y control de visitas futuras</p>
        </div>
        <button @click="createModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-plus-lg"></i> Nueva Reserva
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
        @forelse($reservations as $res)
            <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden relative transition-all duration-300 hover:shadow-lg {{ $res->status == 'cancelled' ? 'opacity-60 grayscale-[0.5]' : '' }}">
                
                @if($res->status == 'confirmed')
                    <div class="absolute top-4 right-4 px-3 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-extrabold shadow-sm">CONFIRMADA</div>
                @elseif($res->status == 'cancelled')
                    <div class="absolute top-4 right-4 px-3 py-1 bg-rose-100 text-rose-700 rounded-full text-xs font-extrabold shadow-sm">CANCELADA</div>
                @else
                    <div class="absolute top-4 right-4 px-3 py-1 bg-amber-100 text-amber-700 rounded-full text-xs font-extrabold shadow-sm">PENDIENTE</div>
                @endif

                <div class="p-6">
                    <h5 class="font-extrabold text-indigo-600 text-xl mb-1 pr-24 truncate" title="{{ $res->client_name }}">{{ $res->client_name }}</h5>
                    <div class="text-slate-500 text-sm font-medium mb-5 flex items-center gap-2"><i class="bi bi-telephone text-slate-400"></i> {{ $res->phone ?? 'Sin teléfono' }}</div>
                    
                    <div class="flex items-center justify-between gap-2 mb-5 bg-slate-50 p-3 rounded-2xl border border-slate-100">
                        <div class="text-center flex-1">
                            <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest block mb-0.5">Fecha</small>
                            <span class="font-extrabold text-slate-800">{{ $res->reservation_time->format('d/m') }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="text-center flex-1">
                            <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest block mb-0.5">Hora</small>
                            <span class="font-extrabold text-rose-600">{{ $res->reservation_time->format('H:i') }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="text-center flex-1">
                            <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest block mb-0.5">Pax</small>
                            <span class="font-extrabold text-slate-800">{{ $res->people }}</span>
                        </div>
                        <div class="w-px h-8 bg-slate-200"></div>
                        <div class="text-center flex-1">
                            <small class="text-slate-400 font-bold text-[10px] uppercase tracking-widest block mb-0.5">Mesa</small>
                            <span class="font-extrabold text-indigo-600 text-sm">{{ $res->table->name ?? '---' }}</span>
                        </div>
                    </div>

                    @if($res->note)
                        <div class="bg-indigo-50 text-indigo-800 p-3 rounded-xl text-sm font-medium mb-5 border border-indigo-100 flex items-start gap-2 shadow-sm">
                            <i class="bi bi-sticky text-indigo-500 mt-0.5"></i> {{ $res->note }}
                        </div>
                    @endif

                    <div class="flex gap-3">
                        @if($res->status == 'pending')
                            <form action="{{ route('reservations.status', $res->id) }}" method="POST" class="flex-1">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="confirmed">
                                <button class="w-full bg-emerald-50 hover:bg-emerald-500 text-emerald-600 hover:text-white font-bold py-2.5 rounded-xl border border-emerald-200 transition-colors shadow-sm text-sm active:scale-95"><i class="bi bi-check-lg mr-1"></i> Confirmar</button>
                            </form>
                            <form action="{{ route('reservations.status', $res->id) }}" method="POST" class="flex-1">
                                @csrf @method('PUT')
                                <input type="hidden" name="status" value="cancelled">
                                <button class="w-full bg-rose-50 hover:bg-rose-500 text-rose-600 hover:text-white font-bold py-2.5 rounded-xl border border-rose-200 transition-colors shadow-sm text-sm active:scale-95"><i class="bi bi-x-lg mr-1"></i> Cancelar</button>
                            </form>
                        @else
                            <form action="{{ route('reservations.destroy', $res->id) }}" method="POST" class="w-full" @submit="window.confirmAction($el, '¿Borrar historial de esta reserva de forma permanente?', $event)">
                                @csrf @method('DELETE')
                                <button class="w-full bg-slate-50 hover:bg-rose-50 text-slate-500 hover:text-rose-600 font-bold py-3 rounded-xl border border-slate-200 hover:border-rose-200 transition-colors shadow-sm text-sm active:scale-95"><i class="bi bi-trash mr-1"></i> Eliminar Historial</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-32 bg-white rounded-3xl border border-slate-100 shadow-sm">
                <i class="bi bi-calendar-x text-6xl text-slate-200 block mb-5"></i>
                <h3 class="text-2xl font-extrabold text-slate-800">Sin reservas próximas</h3>
                <p class="mt-2 text-slate-500 font-medium">No tienes reservas programadas por el momento.</p>
            </div>
        @endforelse
    </div>

    <!-- Modal Reserva -->
    <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="createModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="createModalOpen = false"></div>
        <form action="{{ route('reservations.store') }}" method="POST" x-show="createModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-2xl overflow-hidden">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-indigo-600">
                <h5 class="font-extrabold text-lg text-white">Nueva Reserva</h5>
                <button type="button" @click="createModalOpen = false" class="text-white/70 hover:text-white bg-white/10 rounded-xl p-2 transition-colors"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-5 mb-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre Cliente <span class="text-rose-500">*</span></label>
                        <input type="text" name="client_name" required placeholder="Ej: Familia Gómez" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Teléfono</label>
                        <input type="text" name="phone" placeholder="Opcional" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Fecha y Hora <span class="text-rose-500">*</span></label>
                        <input type="datetime-local" name="reservation_time" required class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Personas</label>
                            <input type="number" name="people" value="2" min="1" required class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div class="col-span-2 md:col-span-1">
                            <label class="block text-sm font-bold text-slate-700 mb-2">Mesa</label>
                            <select name="table_id" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-white font-bold">
                                <option value="">-- Al llegar --</option>
                                @foreach($tables as $table)
                                    <option value="{{ $table->id }}">{{ $table->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Notas / Pedidos Especiales</label>
                    <textarea name="note" rows="2" placeholder="Ej: Necesitan silla de bebé" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all"></textarea>
                </div>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                <button type="button" @click="createModalOpen = false" class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors shadow-sm">Cancelar</button>
                <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-lg shadow-indigo-500/30 transition-all active:scale-95">Agendar Reserva</button>
            </div>
        </form>
    </div>
</div>
@endsection