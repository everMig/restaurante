@extends('layouts.app')

@section('content')
<div class="w-full" x-data="{ areaModalOpen: false, tableModalOpen: false, activeTab: '{{ count($areas) > 0 ? $areas[0]->id : 0 }}' }">
    
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center mb-8 gap-4">
        <div>
            <h2 class="text-2xl font-extrabold text-slate-800 tracking-tight"><i class="bi bi-grid-3x3-gap-fill me-2 text-indigo-500"></i> Diseño de Salón</h2>
            <p class="text-slate-500 text-sm mt-1 font-medium">Arrastra las mesas para configurar la distribución física</p>
        </div>
        <div class="flex flex-wrap gap-3">
            <button @click="areaModalOpen = true" class="bg-white hover:bg-slate-50 text-slate-700 font-bold py-2.5 px-5 rounded-xl shadow-sm border border-slate-200 transition-colors flex items-center gap-2">
                <i class="bi bi-plus-circle"></i> Nueva Zona
            </button>
            <button @click="tableModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-2.5 px-5 rounded-xl shadow-md shadow-indigo-500/30 transition-all flex items-center gap-2 active:scale-95">
                <i class="bi bi-plus-lg"></i> Nueva Mesa
            </button>
            <button onclick="savePositions()" id="btnSave" class="bg-emerald-500 hover:bg-emerald-600 text-white font-bold py-2.5 px-6 rounded-xl shadow-md shadow-emerald-500/30 transition-all flex items-center gap-2 active:scale-95">
                <i class="bi bi-save"></i> Guardar Diseño
            </button>
        </div>
    </div>

    @if(count($areas) > 0)
        <!-- Tabs Nav -->
        <div class="flex flex-wrap gap-2 mb-6 p-1 bg-white border border-slate-200 rounded-2xl w-max shadow-sm">
            @foreach($areas as $area)
                <button @click="activeTab = '{{ $area->id }}'" 
                        :class="activeTab === '{{ $area->id }}' ? 'bg-indigo-50 text-indigo-700 font-extrabold shadow-sm' : 'text-slate-500 hover:text-slate-700 hover:bg-slate-50 font-bold'"
                        class="px-6 py-2.5 rounded-xl text-sm transition-all">
                    {{ $area->name }}
                </button>
            @endforeach
        </div>

        <!-- Tab Content -->
        <div>
            @foreach($areas as $area)
                <div x-show="activeTab === '{{ $area->id }}'" style="display: none;" class="animate-fade-in">
                    
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center mb-4 bg-white p-4 border border-slate-200 rounded-2xl shadow-sm gap-4">
                        <div class="text-slate-500 text-sm font-medium flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-indigo-50 flex items-center justify-center text-indigo-500">
                                <i class="bi bi-info-lg"></i>
                            </div>
                            <span>Arrastra las mesas a su posición real y presiona <strong class="text-slate-800">Guardar Diseño</strong>.</span>
                        </div>
                        <form action="{{ route('tables.destroyArea', $area->id) }}" method="POST" @submit="window.confirmAction($el, '¿Eliminar esta zona y TODAS sus mesas permanentemente?', $event)">
                            @csrf @method('DELETE')
                            <button class="text-rose-500 hover:text-white bg-rose-50 hover:bg-rose-500 text-sm font-bold px-4 py-2 rounded-xl transition-all border border-rose-100 shadow-sm">
                                <i class="bi bi-trash"></i> Eliminar Zona
                            </button>
                        </form>
                    </div>

                    <!-- Canvas -->
                    <div class="salon-canvas relative w-full h-[600px] bg-slate-50/50 border-2 border-slate-200 rounded-3xl shadow-inner overflow-hidden" 
                         style="background-image: radial-gradient(#cbd5e1 1.5px, transparent 1.5px); background-size: 30px 30px;">
                        
                        @foreach($area->tables as $table)
                            <div class="draggable-table absolute flex flex-col items-center justify-center bg-white border-2 border-slate-200 shadow-sm rounded-2xl transition-shadow duration-200 hover:shadow-md cursor-grab select-none z-10"
                                 id="table-{{ $table->id }}"
                                 data-id="{{ $table->id }}"
                                 style="width: 110px; height: 110px; left: {{ $table->x_pos }}px; top: {{ $table->y_pos }}px;">
                                
                                <i class="bi bi-display text-4xl mb-1 {{ $table->status == 'available' ? 'text-emerald-500' : 'text-rose-500' }}"></i>
                                <span class="font-extrabold text-slate-700 text-xs text-center truncate w-full px-2">{{ $table->name }}</span>
                                
                                <form action="{{ route('tables.destroyTable', $table->id) }}" method="POST" class="absolute top-1 right-1">
                                    @csrf @method('DELETE')
                                    <button type="button" @click.stop="window.confirmAction($el, '¿Borrar esta mesa?')" class="text-rose-400 hover:text-rose-600 bg-white rounded-full opacity-0 group-hover:opacity-100 transition-opacity p-1">
                                        <i class="bi bi-x-circle-fill"></i>
                                    </button>
                                </form>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="text-center py-24 bg-white border border-slate-200 rounded-3xl shadow-sm">
            <i class="bi bi-grid-3x3-gap text-6xl text-slate-200 mb-5 block"></i>
            <h4 class="text-slate-800 font-extrabold text-2xl mb-2">Comienza tu diseño</h4>
            <p class="text-slate-500 mb-8 max-w-md mx-auto">Crea tu primera zona (ej. Salón Principal, Terraza) para empezar a distribuir las mesas gráficamente.</p>
            <button @click="areaModalOpen = true" class="bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 px-8 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95">
                Crear Primera Zona
            </button>
        </div>
    @endif

    <!-- Modal Zona -->
    <div x-show="areaModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="areaModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="areaModalOpen = false"></div>
        <form action="{{ route('tables.storeArea') }}" method="POST" x-show="areaModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h5 class="font-extrabold text-lg text-slate-800">Nueva Zona</h5>
                <button type="button" @click="areaModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-white rounded-xl p-1.5 shadow-sm border border-slate-200"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6">
                <label class="block text-sm font-bold text-slate-700 mb-2">Nombre de Zona</label>
                <input type="text" name="name" required placeholder="Ej: Terraza Norte" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95">Crear Zona</button>
            </div>
        </form>
    </div>

    <!-- Modal Mesa -->
    <div x-show="tableModalOpen" style="display: none;" class="fixed inset-0 z-[100] flex items-center justify-center p-4">
        <div x-show="tableModalOpen" x-transition.opacity class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm" @click="tableModalOpen = false"></div>
        <form action="{{ route('tables.storeTable') }}" method="POST" x-show="tableModalOpen" x-transition.scale.origin.bottom class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm overflow-hidden">
            @csrf
            <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center bg-slate-50/50">
                <h5 class="font-extrabold text-lg text-slate-800">Nueva Mesa</h5>
                <button type="button" @click="tableModalOpen = false" class="text-slate-400 hover:text-slate-600 bg-white rounded-xl p-1.5 shadow-sm border border-slate-200"><i class="bi bi-x-lg"></i></button>
            </div>
            <div class="p-6 space-y-5">
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Nombre de la Mesa</label>
                    <input type="text" name="name" required placeholder="Ej: Mesa 1" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none transition-all">
                </div>
                <div>
                    <label class="block text-sm font-bold text-slate-700 mb-2">Asignar a Zona</label>
                    <select name="area_id" class="w-full rounded-xl border border-slate-300 focus:border-indigo-500 focus:ring-2 focus:ring-indigo-500/20 shadow-sm px-4 py-3 outline-none bg-white transition-all">
                        @foreach($areas as $area) 
                            <option value="{{ $area->id }}">{{ $area->name }}</option> 
                        @endforeach
                    </select>
                </div>
            </div>
            <div class="px-6 py-5 bg-slate-50 border-t border-slate-100">
                <button type="submit" class="w-full bg-indigo-600 hover:bg-indigo-700 text-white font-bold py-3 rounded-xl shadow-lg shadow-indigo-500/30 transition-all active:scale-95">Añadir Mesa</button>
            </div>
        </form>
    </div>
</div>

<style>
    .draggable-table:hover form button { opacity: 1; }
    .animate-fade-in { animation: fadeIn 0.3s ease-in-out; }
    @keyframes fadeIn { from { opacity: 0; transform: translateY(5px); } to { opacity: 1; transform: translateY(0); } }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        const draggables = document.querySelectorAll('.draggable-table');
        let activeDrag = null;
        let initialX, initialY, currentX, currentY;

        draggables.forEach(el => el.addEventListener('mousedown', dragStart));
        document.addEventListener('mouseup', dragEnd);
        document.addEventListener('mousemove', drag);

        function dragStart(e) {
            if (e.target.closest('form') || e.target.closest('button')) return; 
            activeDrag = e.currentTarget;
            
            let styleLeft = activeDrag.offsetLeft;
            let styleTop = activeDrag.offsetTop;

            initialX = e.clientX - styleLeft;
            initialY = e.clientY - styleTop;

            activeDrag.style.cursor = 'grabbing';
            activeDrag.style.zIndex = 100;
            activeDrag.classList.add('shadow-2xl', 'border-indigo-400', 'ring-4', 'ring-indigo-100', 'scale-105');
            activeDrag.classList.remove('border-slate-200', 'shadow-sm');
        }

        function dragEnd() {
            if(!activeDrag) return;
            activeDrag.style.cursor = 'grab';
            activeDrag.style.zIndex = 10;
            activeDrag.classList.remove('shadow-2xl', 'border-indigo-400', 'ring-4', 'ring-indigo-100', 'scale-105');
            activeDrag.classList.add('border-slate-200', 'shadow-sm');
            activeDrag = null;
        }

        function drag(e) {
            if (activeDrag) {
                e.preventDefault();
                currentX = e.clientX - initialX;
                currentY = e.clientY - initialY;

                if(currentX < 0) currentX = 0;
                if(currentY < 0) currentY = 0;

                activeDrag.style.left = currentX + "px";
                activeDrag.style.top = currentY + "px";
            }
        }
    });

    function savePositions() {
        let btn = document.getElementById('btnSave');
        let originalText = btn.innerHTML;
        btn.innerHTML = '<i class="bi bi-hourglass-split"></i> Guardando...';
        btn.disabled = true;

        let positions = [];
        document.querySelectorAll('.draggable-table').forEach(el => {
            positions.push({
                id: el.getAttribute('data-id'),
                x: parseInt(el.style.left.replace('px', '') || 0),
                y: parseInt(el.style.top.replace('px', '') || 0)
            });
        });

        fetch("{{ route('tables.updatePositions') }}", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-CSRF-TOKEN": "{{ csrf_token() }}"
            },
            body: JSON.stringify({ positions: positions })
        })
        .then(response => {
            if (!response.ok) throw new Error('Error en el servidor: ' + response.statusText);
            return response.json();
        })
        .then(data => {
            if(data.status === 'success') {
                window.toastSuccess('¡Diseño guardado con éxito!');
            } else {
                window.toastError('Error al guardar: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            window.toastError('Ocurrió un error al guardar');
        })
        .finally(() => {
            btn.innerHTML = originalText;
            btn.disabled = false;
        });
    }
</script>
@endsection