<!doctype html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    
    <title>{{ \App\Models\Setting::where('key', 'company_name')->value('value') ?? 'Mi Restaurante' }}</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">

    @viteReactRefresh
    @vite(['resources/css/tailwind.css', 'resources/css/app.scss', 'resources/js/app.js'])
    
    <!-- Alpine.js para interactividad sin Bootstrap JS -->
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    
    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        window.confirmAction = function(element, message, event) {
            if(event) event.preventDefault();
            Swal.fire({
                title: '¿Estás seguro?',
                text: message,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#4f46e5',
                cancelButtonColor: '#f43f5e',
                confirmButtonText: 'Sí, continuar',
                cancelButtonText: 'Cancelar',
                customClass: {
                    popup: 'rounded-3xl shadow-2xl border border-slate-100',
                    title: 'font-extrabold text-slate-800',
                    htmlContainer: 'text-slate-500 font-medium',
                    confirmButton: 'font-bold rounded-xl shadow-lg shadow-indigo-500/30',
                    cancelButton: 'font-bold rounded-xl'
                }
            }).then((result) => {
                if (result.isConfirmed) {
                    let form = element instanceof HTMLFormElement ? element : element.closest('form');
                    if(form) form.submit();
                }
            });
            return false;
        };
        
        window.toastSuccess = function(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'success',
                title: message,
                showConfirmButton: false,
                timer: 3000,
                timerProgressBar: true,
                customClass: { popup: 'rounded-xl shadow-lg border border-slate-100' }
            });
        };
        
        window.toastError = function(message) {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'error',
                title: message,
                showConfirmButton: false,
                timer: 4000,
                timerProgressBar: true,
                customClass: { popup: 'rounded-xl shadow-lg border border-slate-100' }
            });
        };
    </script>
</head>
<body class="bg-slate-50 text-slate-900 font-sans antialiased overflow-x-hidden" x-data="{ sidebarOpen: false, profileModalOpen: false }">

    <!-- Mobile Overlay -->
    <div x-show="sidebarOpen" x-transition.opacity 
         class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm z-40 lg:hidden" 
         @click="sidebarOpen = false" style="display: none;"></div>

    <!-- Sidebar -->
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" 
           class="fixed inset-y-0 left-0 w-72 bg-slate-900 text-white z-50 flex flex-col transition-transform duration-300 lg:translate-x-0 shadow-2xl lg:shadow-none">
        
        <div class="px-6 py-6 flex items-center gap-3 flex-shrink-0">
            @php $logo = \App\Models\Setting::where('key', 'company_logo')->value('value'); @endphp
            @if($logo) 
                <img src="{{ asset('storage/'.$logo) }}" onerror="this.onerror=null; this.outerHTML='<div class=\'w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30\'><i class=\'bi bi-shop\'></i></div>';" class="w-12 h-12 object-cover rounded-xl shadow-md">
            @else 
                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-indigo-500 to-indigo-700 flex items-center justify-center text-2xl shadow-lg shadow-indigo-500/30">
                    <i class="bi bi-shop"></i>
                </div> 
            @endif
            <div class="font-extrabold text-xl tracking-tight truncate">{{ \App\Models\Setting::where('key', 'company_name')->value('value') ?? 'Mi Restaurante' }}</div>
            <button @click="sidebarOpen = false" class="lg:hidden ml-auto text-slate-400 hover:text-white p-1">
                <i class="bi bi-x-lg text-xl"></i>
            </button>
        </div>

        <nav class="flex-1 px-4 pb-6 overflow-y-auto overflow-x-hidden" style="scrollbar-width: thin; scrollbar-color: rgba(255,255,255,0.2) transparent;">
            @php $role = Auth::user()->role; @endphp
            
            @if(in_array($role, ['admin', 'cashier']))
                <a href="{{ route('dashboard') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('dashboard') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                    <i class="bi bi-grid-1x2-fill text-xl opacity-70"></i> Panel de Control
                </a>
            @endif

            @if($role === 'admin')
                <a href="{{ route('reports.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                    <i class="bi bi-bar-chart-line-fill text-xl opacity-70"></i> Reportes
                </a>
            @endif

            <div class="text-[0.65rem] uppercase font-bold tracking-widest text-slate-500 mt-6 mb-3 px-3">Operaciones</div>
            
            <a href="{{ route('pos.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('pos.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-bag-check-fill text-xl opacity-70"></i> Punto de Venta
            </a>
            <a href="{{ route('reservations.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('reservations.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-calendar-event-fill text-xl opacity-70"></i> Reservas
            </a>
            @if(in_array($role, ['admin', 'cashier']))
            <a href="{{ route('sales.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('sales.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-receipt text-xl opacity-70"></i> Caja / Historial
            </a>
            @endif
            <a href="{{ route('kitchen.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('kitchen.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-fire text-xl opacity-70"></i> Cocina (KDS)
            </a>

            <div class="text-[0.65rem] uppercase font-bold tracking-widest text-slate-500 mt-6 mb-3 px-3">Gestión y Admin</div>
            <a href="{{ route('clients.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('clients.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-people-fill text-xl opacity-70"></i> Clientes
            </a>
            @if($role === 'admin')
            <a href="{{ route('categories.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('categories.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-tags-fill text-xl opacity-70"></i> Categorías
            </a>
            <a href="{{ route('products.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('products.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-box-seam-fill text-xl opacity-70"></i> Inventario
            </a>
            <a href="{{ route('tables.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('tables.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-grid-3x3-gap-fill text-xl opacity-70"></i> Mesas y Salones
            </a>
            <a href="{{ route('users.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('users.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-person-badge-fill text-xl opacity-70"></i> Personal / Usuarios
            </a>
            <a href="{{ route('settings.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('settings.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-slate-300 hover:bg-white/10 hover:text-white hover:translate-x-1' }}">
                <i class="bi bi-gear-fill text-xl opacity-70"></i> Configuración
            </a>
            <a href="{{ route('system.index') }}" class="flex items-center gap-3 px-4 py-3.5 rounded-xl mb-1.5 font-medium transition-all {{ request()->routeIs('system.*') ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/30' : 'text-red-400 hover:bg-red-500/10 hover:text-red-300 hover:translate-x-1' }}">
                <i class="bi bi-exclamation-octagon-fill text-xl opacity-70"></i> Reset Sistema
            </a>
            @endif
        </nav>
    </aside>

    <main class="lg:ml-72 min-h-screen flex flex-col p-4 sm:p-8 transition-all duration-300">
        @if(!request()->routeIs('pos.order'))
            <header class="bg-white rounded-2xl shadow-sm border border-slate-100 px-5 py-4 mb-8 flex items-center justify-between">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 rounded-xl border border-slate-200 text-slate-600 hover:bg-slate-50 transition-colors">
                        <i class="bi bi-list text-2xl"></i>
                    </button>
                    <h1 class="font-extrabold text-slate-800 text-xl hidden sm:block">
                        @if(request()->routeIs('dashboard')) Panel de Control
                        @elseif(request()->routeIs('pos.*')) Punto de Venta
                        @elseif(request()->routeIs('products.*')) Inventario
                        @elseif(request()->routeIs('sales.*')) Caja y Movimientos
                        @elseif(request()->routeIs('users.*')) Gestión de Personal
                        @else Sistema de Restaurante @endif
                    </h1>
                </div>

                <div class="relative" x-data="{ dropdownOpen: false }">
                    <button @click="dropdownOpen = !dropdownOpen" @click.outside="dropdownOpen = false" class="flex items-center gap-3 p-1 pr-3 rounded-full hover:bg-slate-50 border border-transparent hover:border-slate-200 transition-all">
                        <div class="text-right hidden sm:block">
                            <div class="font-bold text-slate-800 text-sm leading-tight">{{ Auth::user()->name }}</div>
                            <div class="text-slate-500 text-[11px] font-bold uppercase tracking-wider">{{ ucfirst(Auth::user()->role) }}</div>
                        </div>
                        <div class="w-11 h-11 rounded-full bg-gradient-to-br from-indigo-400 to-indigo-600 text-white flex items-center justify-center font-bold text-lg shadow-md">
                            {{ substr(Auth::user()->name, 0, 1) }}
                        </div>
                        <i class="bi bi-chevron-down text-slate-400 text-xs ml-1"></i>
                    </button>

                    <!-- Dropdown Menu -->
                    <div x-show="dropdownOpen" x-transition.opacity.duration.200ms
                         class="absolute right-0 mt-3 w-56 bg-white rounded-2xl shadow-xl border border-slate-100 py-2 z-50" style="display: none;">
                        <div class="px-5 py-2 text-[10px] font-bold text-slate-400 uppercase tracking-widest">Mi Cuenta</div>
                        <button @click="profileModalOpen = true; dropdownOpen = false" class="w-full text-left px-5 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 hover:text-indigo-600 flex items-center gap-3 transition-colors">
                            <i class="bi bi-person-gear text-lg"></i> Editar Perfil
                        </button>
                        <div class="h-px bg-slate-100 my-1"></div>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full text-left px-5 py-2.5 text-sm font-semibold text-red-600 hover:bg-red-50 flex items-center gap-3 transition-colors">
                                <i class="bi bi-box-arrow-right text-lg"></i> Cerrar Sesión
                            </button>
                        </form>
                    </div>
                </div>
            </header>
        @endif

        <!-- Global Alerts -->
        @if(session('success'))
            <script>document.addEventListener('DOMContentLoaded', () => window.toastSuccess("{!! addslashes(session('success')) !!}"));</script>
        @endif
        
        @if(session('error'))
            <script>document.addEventListener('DOMContentLoaded', () => window.toastError("{!! addslashes(session('error')) !!}"));</script>
        @endif

        @if($errors->any())
            <script>
                document.addEventListener('DOMContentLoaded', () => {
                    @foreach($errors->all() as $error)
                        window.toastError("{!! addslashes($error) !!}");
                    @endforeach
                });
            </script>
        @endif

        <!-- Main Content Injection -->
        <div class="flex-1">
            @yield('content')
        </div>
    </main>

    <!-- Profile Modal (Alpine) -->
    <div x-show="profileModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4 sm:p-0">
        <div x-show="profileModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="profileModalOpen = false"></div>
        
        <div x-show="profileModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-2xl shadow-2xl w-full max-w-md overflow-hidden">
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h5 class="font-extrabold text-lg text-slate-800">Mi Perfil</h5>
                <button @click="profileModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-white rounded-lg p-1.5 shadow-sm border border-slate-200 transition-colors">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="p-6">
                <p class="text-slate-500 text-sm mb-6">Actualiza tus datos de acceso a la plataforma.</p>
                <form action="{{ route('users.update', Auth::user()->id) }}" method="POST" class="space-y-4">
                    @csrf
                    @method('PUT')
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Nombre Completo</label>
                        <input type="text" name="name" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-2.5 text-sm transition-all outline-none" value="{{ Auth::user()->name }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Correo Electrónico (Solo lectura)</label>
                        <input type="email" class="w-full rounded-xl border border-slate-200 bg-slate-50 text-slate-500 shadow-inner px-4 py-2.5 text-sm cursor-not-allowed outline-none" value="{{ Auth::user()->email }}" readonly>
                    </div>
                    
                    <div class="my-5 border-t border-dashed border-slate-200"></div>
                    
                    <div>
                        <label class="block text-sm font-bold text-slate-700 mb-1.5">Nueva Contraseña <span class="text-slate-400 font-normal">(Opcional)</span></label>
                        <input type="password" name="password" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-2.5 text-sm transition-all outline-none" placeholder="••••••••">
                    </div>
                    <div class="pt-4">
                        <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-4 rounded-xl shadow-md shadow-indigo-500/30 transition-all active:scale-[0.98]">
                            Guardar Cambios
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</body>
</html>