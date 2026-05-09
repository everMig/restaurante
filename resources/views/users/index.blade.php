@extends('layouts.app')

@section('content')
<div class="w-full" x-data="{ createModalOpen: false, editModalOpen: false, editingUser: {} }">
    
    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-people-fill me-2 text-indigo-500"></i>Personal</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Gestiona los accesos y roles de tu equipo</p>
        </div>
        <button @click="createModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-6 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95 flex items-center gap-2">
            <i class="bi bi-person-plus-fill"></i> Nuevo Usuario
        </button>
    </div>

    <div class="bg-white rounded-3xl shadow-sm border border-slate-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse">
                <thead>
                    <tr class="bg-slate-50/80 border-b border-slate-100 text-slate-500 text-xs uppercase tracking-widest">
                        <th class="px-6 py-5 font-bold">Usuario</th>
                        <th class="px-6 py-5 font-bold">Rol / Cargo</th>
                        <th class="px-6 py-5 font-bold">Email de Acceso</th>
                        <th class="px-6 py-5 font-bold">Fecha Registro</th>
                        <th class="px-6 py-5 font-bold text-right">Acciones</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100">
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50/80 transition-colors group">
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="flex items-center gap-4">
                                    <div class="w-11 h-11 rounded-full text-white flex items-center justify-center font-extrabold text-lg shadow-sm {{ $user->role == 'admin' ? 'bg-gradient-to-br from-rose-400 to-rose-600' : ($user->role == 'cashier' ? 'bg-gradient-to-br from-blue-400 to-blue-600' : 'bg-gradient-to-br from-emerald-400 to-emerald-600') }}">
                                        {{ substr($user->name, 0, 1) }}
                                    </div>
                                    <div>
                                        <div class="font-extrabold text-slate-800 text-[1.05rem]">{{ $user->name }}</div>
                                        @if($user->id === Auth::id())
                                            <span class="inline-block mt-0.5 px-2 py-0.5 bg-slate-100 text-slate-500 border border-slate-200 rounded text-[10px] font-bold tracking-wider">TÚ</span>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($user->role == 'admin')
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-rose-50 text-rose-600 border border-rose-200 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-rose-500 mr-2"></span>Administrador</span>
                                @elseif($user->role == 'cashier')
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-blue-50 text-blue-600 border border-blue-200 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-blue-500 mr-2"></span>Cajero</span>
                                @else
                                    <span class="inline-flex items-center px-4 py-1.5 rounded-full text-xs font-bold bg-emerald-50 text-emerald-600 border border-emerald-200 shadow-sm"><span class="w-1.5 h-1.5 rounded-full bg-emerald-500 mr-2"></span>Mozo / Staff</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-500 font-medium">{{ $user->email }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-slate-400 text-sm font-medium">{{ $user->created_at->format('d/m/Y') }}</td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <button @click="editingUser = {{ json_encode($user) }}; editModalOpen = true" class="text-indigo-500 hover:text-white bg-indigo-50 hover:bg-indigo-500 p-2.5 rounded-xl transition-all border border-indigo-100 shadow-sm" title="Editar">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>
                                    
                                    @if($user->id !== Auth::id())
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" class="inline-block">
                                            @csrf @method('DELETE')
                                            <button type="button" @click="window.confirmAction($el, '¿Estás seguro de eliminar a {{ $user->name }}?')" class="text-rose-500 hover:text-white bg-rose-50 hover:bg-rose-500 p-2.5 rounded-xl transition-all border border-rose-100 shadow-sm" title="Eliminar">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-16 text-center text-slate-400 font-medium">
                                <i class="bi bi-people text-5xl text-slate-200 mb-4 block"></i>
                                No hay usuarios registrados.
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
                <h5 class="font-extrabold text-lg text-white">Registrar Personal</h5>
                <button @click="createModalOpen = false" class="text-white/70 hover:text-white bg-white/10 rounded-xl p-2 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form action="{{ route('users.store') }}" method="POST">
                @csrf
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre Completo</label>
                        <input type="text" name="name" required placeholder="Ej: Juan Pérez" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Correo Electrónico (Login)</label>
                        <input type="email" name="email" required placeholder="juan@restaurante.com" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Contraseña</label>
                            <input type="password" name="password" required class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Rol / Permisos</label>
                            <select name="role" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-white">
                                <option value="waiter">Mozo (Solo Pedidos)</option>
                                <option value="cashier">Cajero (Cobros y Gastos)</option>
                                <option value="admin">Administrador (Total)</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="button" @click="createModalOpen = false" class="px-6 py-3 rounded-xl font-bold text-slate-600 bg-white border border-slate-200 hover:bg-slate-100 transition-colors shadow-sm">Cancelar</button>
                    <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-indigo-600 hover:bg-indigo-700 shadow-md shadow-indigo-500/30 transition-all active:scale-95">Guardar Usuario</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal Editar -->
    <div x-show="editModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <div x-show="editModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="editModalOpen = false"></div>
        <div x-show="editModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-amber-500">
                <h5 class="font-extrabold text-lg text-white">Editar Usuario</h5>
                <button @click="editModalOpen = false" class="text-white/70 hover:text-white bg-white/10 rounded-xl p-2 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form :action="`{{ url('/users') }}/${editingUser.id}`" method="POST">
                @csrf @method('PUT')
                <div class="p-6 space-y-5">
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Nombre</label>
                        <input type="text" name="name" x-model="editingUser.name" required class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-2">Email</label>
                        <input type="email" name="email" x-model="editingUser.email" required class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                    </div>
                    <div class="grid grid-cols-2 gap-5">
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Nueva Contraseña</label>
                            <input type="password" name="password" placeholder="Dejar vacío para no cambiar" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                        </div>
                        <div>
                            <label class="block text-sm font-bold text-slate-700 mb-2">Rol</label>
                            <select name="role" x-model="editingUser.role" class="w-full rounded-xl border border-slate-300 focus:border-amber-500 focus:ring-2 focus:ring-amber-500/20 shadow-sm px-4 py-3 outline-none transition-all bg-white">
                                <option value="waiter">Mozo</option>
                                <option value="cashier">Cajero</option>
                                <option value="admin">Administrador</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="px-6 py-5 bg-slate-50 border-t border-slate-100 flex justify-end gap-3">
                    <button type="submit" class="px-6 py-3 rounded-xl font-bold text-white bg-amber-500 hover:bg-amber-600 shadow-md shadow-amber-500/30 transition-all active:scale-95">Actualizar Datos</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection