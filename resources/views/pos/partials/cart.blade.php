<div class="flex-1 overflow-auto p-3 no-scrollbar">
    @if($order && $order->details->count() > 0)
        <div class="w-full">
            <table class="w-full text-left border-collapse table-fixed">
                <thead class="text-slate-400 text-[10px] uppercase tracking-widest border-b border-slate-100">
                    <tr>
                        <th class="w-8 pb-2"></th>
                        <th class="pb-2">PROD.</th>
                        <th class="text-center w-24 pb-2">CANT.</th>
                        <th class="text-right w-16 pb-2">TOT.</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-slate-100/50">
                    @foreach($order->details as $detail)
                        <tr class="group hover:bg-white transition-colors">
                            
                            <td class="py-3">
                                <button class="text-rose-300 hover:text-rose-500 hover:bg-rose-50 rounded-lg p-1 transition-all" onclick="removeItem({{ $detail->id }})" title="Eliminar">
                                    <i class="bi bi-x-lg text-sm font-extrabold"></i>
                                </button>
                            </td>

                            <td class="py-3 px-1 overflow-hidden">
                                <div class="font-extrabold text-slate-800 text-sm truncate w-full" title="{{ $detail->product->name }}">
                                    {{ $detail->product->name }}
                                </div>
                                <div class="flex items-center mt-0.5">
                                    <small class="text-slate-400 font-bold text-[10px] mr-1">
                                        {{ $currency ?? 'S/' }}{{ number_format($detail->price, 2) }}
                                    </small>
                                    @if($detail->note)
                                        <i class="bi bi-chat-square-text-fill text-amber-500 text-[10px] mr-1" title="{{ $detail->note }}"></i>
                                    @endif
                                    <a href="javascript:void(0)" class="text-indigo-400 hover:text-indigo-600 bg-indigo-50 hover:bg-indigo-100 rounded px-1.5 py-0.5 transition-colors" 
                                       data-bs-toggle="modal" data-bs-target="#noteModal"
                                       data-detail-id="{{ $detail->id }}" 
                                       data-note-content="{{ $detail->note }}">
                                       <i class="bi bi-pencil-fill text-[10px]"></i>
                                    </a>
                                </div>
                            </td>

                            <td class="py-3 px-0 text-center">
                                <div class="flex items-center justify-center bg-white border border-slate-200 rounded-lg shadow-sm overflow-hidden w-[80px] mx-auto">
                                    <button class="w-7 h-7 flex items-center justify-center bg-slate-50 text-slate-600 hover:bg-slate-200 hover:text-slate-800 font-black transition-colors" onclick="updateQty({{ $detail->id }}, {{ $detail->quantity - 1 }})">-</button>
                                    <input type="text" class="w-7 text-center font-black text-sm text-slate-800 bg-transparent border-none outline-none p-0" 
                                           value="{{ $detail->quantity }}" readonly>
                                    <button class="w-7 h-7 flex items-center justify-center bg-slate-50 text-slate-600 hover:bg-slate-200 hover:text-slate-800 font-black transition-colors" onclick="updateQty({{ $detail->id }}, {{ $detail->quantity + 1 }})">+</button>
                                </div>
                            </td>

                            <td class="text-right py-3 pr-1 font-black text-slate-800 text-sm">
                                {{ number_format($detail->quantity * $detail->price, 2) }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="h-full flex flex-col items-center justify-center text-slate-300">
            <div class="w-20 h-20 bg-white border border-slate-100 shadow-sm rounded-full flex items-center justify-center mb-4">
                <i class="bi bi-basket3-fill text-4xl text-slate-200"></i>
            </div>
            <p class="text-sm font-bold text-slate-400">Cuenta vacía</p>
        </div>
    @endif
</div>

<div class="bg-white border-t border-slate-200 p-5 shrink-0 shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.02)] z-10 relative">
    @if($order)
        <input type="hidden" id="cartTotalValue" value="{{ number_format($order->total + ($order->tip ?? 0) - ($order->discount ?? 0), 2, '.', '') }}">

        <div class="flex justify-between items-center mb-2 text-xs font-bold text-slate-500">
            <span class="uppercase tracking-widest">Subtotal</span>
            <span class="text-slate-700">{{ number_format($order->total, 2) }}</span>
        </div>
        
        @if($order->discount > 0)
            <div class="flex justify-between items-center mb-2 text-xs font-bold text-rose-500">
                <span class="uppercase tracking-widest">Descuento</span>
                <span>-{{ number_format($order->discount, 2) }}</span>
            </div>
        @endif

        @if($order->tip > 0)
            <div class="flex justify-between items-center mb-2 text-xs font-bold text-emerald-500">
                <span class="uppercase tracking-widest">Propina</span>
                <span>+{{ number_format($order->tip, 2) }}</span>
            </div>
        @endif

        <div class="flex justify-between items-end mb-4 mt-3 pt-3 border-t border-slate-100">
            <h5 class="m-0 font-extrabold text-slate-800 text-sm uppercase tracking-widest">TOTAL</h5>
            <h3 class="m-0 font-black text-indigo-600 text-3xl tracking-tight leading-none">
                {{ $currency ?? 'S/' }}{{ number_format($order->total + ($order->tip ?? 0) - ($order->discount ?? 0), 2) }}
            </h3>
        </div>

        <div class="grid grid-cols-3 gap-3 mb-4">
            <button class="bg-white border border-slate-200 hover:border-indigo-300 hover:bg-indigo-50 text-slate-600 hover:text-indigo-600 rounded-xl py-2.5 flex flex-col items-center justify-center transition-all shadow-sm group" data-bs-toggle="modal" data-bs-target="#optionsModal" title="Opciones">
                <i class="bi bi-sliders text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-extrabold uppercase tracking-widest">Ajustes</span>
            </button>
            <a href="{{ route('pos.split.content', $order->id) }}" class="bg-white border border-slate-200 hover:border-amber-300 hover:bg-amber-50 text-slate-600 hover:text-amber-600 rounded-xl py-2.5 flex flex-col items-center justify-center transition-all shadow-sm group" title="Dividir Cuenta">
                <i class="bi bi-layout-split text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-extrabold uppercase tracking-widest">Dividir</span>
            </a>
            <a href="{{ route('pos.precheck', $order->id) }}" target="_blank" class="bg-white border border-slate-200 hover:border-sky-300 hover:bg-sky-50 text-slate-600 hover:text-sky-600 rounded-xl py-2.5 flex flex-col items-center justify-center transition-all shadow-sm group" title="Pre-cuenta">
                <i class="bi bi-receipt text-xl mb-1 group-hover:scale-110 transition-transform"></i>
                <span class="text-[9px] font-extrabold uppercase tracking-widest">Ticket</span>
            </a>
        </div>

        <button class="w-full bg-emerald-500 hover:bg-emerald-600 text-white font-black py-4 rounded-xl shadow-lg shadow-emerald-500/30 transition-all active:scale-95 flex items-center justify-center gap-2 text-lg uppercase tracking-widest" data-bs-toggle="modal" data-bs-target="#checkoutModal">
            <i class="bi bi-cash-coin text-2xl"></i> COBRAR
        </button>
    @endif
</div>