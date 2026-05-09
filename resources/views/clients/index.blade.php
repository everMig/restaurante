@extends('layouts.app')

@section('content')
<div class="w-full" x-data="{ createModalOpen: false, editModalOpen: false, editingClient: {} }">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-people-fill me-2 text-indigo-500"></i>Cartera de Clientes</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Gestión de relaciones y fidelización (CRM)</p>
        </div>
        <button @click="createModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-person-plus-fill"></i> Nuevo Cliente
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="px-6 py-5 font-bold">Nombre Cliente</th>
                        <th class="px-6 py-5 font-bold">Documento / RUC</th>
                        <th class="px-6 py-5 font-bold">Contacto</th>
                        <th class="px-6 py-5 font-bold text-center">Visitas</th>
                        <th class="px-6 py-5 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($clients as $client)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <a href="{{ route('clients.show', $client->id) }}" class="font-extrabold text-slate-800 hover:text-indigo-600 transition-colors text-[1.05rem]">
                                    {{ $client->name }}
                                </a>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="inline-block px-3 py-1 bg-slate-100 text-slate-600 border border-slate-200 rounded-lg text-xs font-bold tracking-wider">
                                    {{ $client->document_number ?? '---' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 text-sm font-medium">
                                @if($client->phone) 
                                    <div class="flex items-center gap-2 mb-1"><i class="bi bi-telephone text-slate-400"></i> {{ $client->phone }}</div>
                                @endif
                                @if($client->email) 
                                    <div class="flex items-center gap-2"><i class="bi bi-envelope text-slate-400"></i> {{ $client->email }}</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                @if($client->orders_count > 0)
                                    <span class="inline-flex items-center justify-center min-w-[2rem] px-2 py-1 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold">{{ $client->orders_count }}</span>
                                @else
                                    <span class="text-slate-300 font-bold">-</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('clients.show', $client->id) }}" class="text-blue-500 hover:text-white bg-blue-50 hover:bg-blue-500 p-2.5 rounded-xl transition-all border border-blue-100 shadow-sm" title="Ver Perfil 360">
                                        <i class="bi bi-eye-fill"></i>
                                    </a>
                                    <button @click="editingClient = {{ json_encode($client) }}; editModalOpen = true" class="text-amber-500 hover:text-white bg-amber-50 hover:bg-amber-500 p-2.5 rounded-xl transition-all border border-amber-100 shadow-sm" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    <form action="{{ route('clients.destroy', $client->id) }}" method="POST" class="inline-block">
                                        @csrf @method('DELETE')
                                        <button type="button" @click="window.confirmAction($el, '¿Estás seguro de eliminar a {{ $client->name }}?')" class="text-rose-500 hover:text-white bg-rose-50 hover:bg-rose-500 p-2.5 rounded-xl transition-all border border-rose-100 shadow-sm" title="Eliminar">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-400 font-medium">
                                <i class="bi bi-people text-5xl text-slate-200 mb-4 block"></i>
                                No hay clientes registrados aún.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modal Crear -->
    <div x-show="createModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <div x-show="createModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="createModalOpen = false"></div>
        <div x-show="createModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-indigo-600">
                <h5 class="font-extrabold text-lg text-white">Registrar Cliente</h5>
                <button @click="createModalOpen = false" class="text-white/70 hover:text-white bg-white/10 rounded-xl p-2 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('clients.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre Completo <span class="text-rose-500">*</span></label>
                        <input type="text" name="name" required class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">DNI / RUC</label>
                            <input type="text" name="document_number" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Teléfono</label>
                            <input type="text" name="phone" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Dirección</label>
                        <input type="text" name="address" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors shadow-sm">Cancelar</button>
                    <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/30 transition-all active:scale-95">Guardar Cliente</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="editModalOpen = false"></div>
        <div x-show="editModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-amber-500">
                <h5 class="font-extrabold text-lg text-white">Editar Cliente</h5>
                <button @click="editModalOpen = false" class="text-white/70 hover:text-white bg-white/10 rounded-xl p-2 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form :action="`{{ url('/clients') }}/${editingClient.id}`" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre</label>
                        <input type="text" name="name" x-model="editingClient.name" required class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Doc</label>
                            <input type="text" name="document_number" x-model="editingClient.document_number" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Teléfono</label>
                            <input type="text" name="phone" x-model="editingClient.phone" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" x-model="editingClient.email" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Dirección</label>
                        <input type="text" name="address" x-model="editingClient.address" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="editModalOpen = false" class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors shadow-sm">Cancelar</button>
                    <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-amber-500 hover:bg-amber-600 shadow-md shadow-amber-500/30 transition-all active:scale-95">Actualizar Cliente</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection