<x-filament-panels::page>
    <style>
        .pos-container {
            display: flex;
            flex-direction: column;
            gap: 1.25rem;
            height: calc(100vh - 5rem);
            min-height: 650px;
        }
        @media (min-width: 1024px) {
            .pos-container {
                flex-direction: row;
            }
        }
        .pos-left {
            flex: 2;
            display: flex;
            flex-direction: column;
            gap: 1rem;
            overflow: hidden;
        }
        .pos-right {
            flex: 1;
            display: flex;
            flex-direction: column;
            background: var(--gray-50);
            border-radius: 0.75rem;
            box-shadow: 0 1px 3px 0 rgba(0, 0, 0, 0.1);
            border: 1px solid var(--gray-200);
            overflow: hidden;
        }
        .dark .pos-right {
            background: var(--gray-900);
            border-color: var(--gray-800);
        }
        .pos-grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 0.85rem;
            overflow-y: auto;
            padding-bottom: 1.5rem;
            flex: 1;
        }
        .pos-card {
            background: white;
            border-radius: 0.75rem;
            border: 1px solid var(--gray-200);
            overflow: hidden;
            cursor: pointer;
            transition: all 0.2s;
            display: flex;
            flex-direction: column;
            color: #111827;
            height: 100%;
        }
        .dark .pos-card {
            background: #ffffff !important;
            border-color: rgba(255, 255, 255, 0.2) !important;
            color: #111827 !important;
            box-shadow: 0 4px 15px rgba(0,0,0,0.3);
        }
        .pos-card:hover {
            border-color: var(--primary-500) !important;
            box-shadow: 0 6px 16px rgba(0, 0, 0, 0.15);
            transform: translateY(-2px);
        }
        .pos-image {
            width: 100%;
            height: 110px;
            object-fit: contain;
            padding: 8px;
            background: #ffffff;
            flex-shrink: 0;
        }
        .dark .pos-image {
            background: #ffffff;
        }
        .pos-card-body {
            padding: 0.6rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
            gap: 0.35rem;
        }
        .pos-title {
            font-size: 0.8rem;
            font-weight: 600;
            line-height: 1.2;
            margin: 0;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        .pos-price-container {
            display: flex; 
            justify-content: space-between; 
            align-items: flex-end;
            margin-top: auto;
        }
        .pos-stock {
            font-size: 0.7rem; 
            color: var(--gray-500);
            font-weight: 500;
        }
        .pos-price {
            font-size: 0.95rem;
            font-weight: 700;
            color: var(--primary-600);
            line-height: 1.1;
        }
        .dark .pos-price {
            color: var(--primary-600) !important;
        }
        .pos-cart-item {
            display: flex;
            align-items: center;
            padding: 0.5rem;
            border-bottom: 1px solid var(--gray-200);
        }
        .dark .pos-cart-item {
            border-color: var(--gray-800);
        }
        .pos-qty-btn {
            padding: 0.25rem 0.5rem;
            background: var(--gray-100);
            border-radius: 0.25rem;
            cursor: pointer;
        }
        .dark .pos-qty-btn {
            background: var(--gray-800);
        }
        .icon-sm { width: 1.25rem; height: 1.25rem; }
        .icon-md { width: 1.5rem; height: 1.5rem; }
        .icon-lg { width: 3rem; height: 3rem; }
        .pos-search-box {
            background: white;
            padding: 1rem;
            border-radius: 0.75rem;
            border: 1px solid var(--gray-200);
            display: flex;
            gap: 1rem;
        }
        .dark .pos-search-box {
            background: var(--gray-900) !important;
            border-color: var(--gray-800) !important;
        }
        .pos-input, .pos-select {
            padding: 0.5rem 0.75rem;
            border: 1px solid var(--gray-300);
            border-radius: 0.5rem;
            background: white;
            color: #111827;
        }
        .dark .pos-input, .dark .pos-select {
            background: var(--gray-800) !important;
            border-color: var(--gray-700) !important;
            color: white !important;
        }
        .pos-checkout-box {
            padding: 1rem;
            background: white;
            border-top: 1px solid var(--gray-200);
        }
        .dark .pos-checkout-box {
            background: var(--gray-900) !important;
            border-color: var(--gray-800) !important;
        }
    </style>

    @if(!$this->activeSession)
        <div class="pos-container" style="justify-content: center; align-items: center; min-height: 550px;">
            <div style="background: white; padding: 2.5rem; border-radius: 1rem; box-shadow: 0 10px 25px rgba(0,0,0,0.05); border: 1px solid var(--gray-200); max-width: 480px; width: 100%; text-align: center;" class="dark:bg-gray-900 dark:border-gray-800">
                <div style="width: 4.5rem; height: 4.5rem; background: var(--primary-100); color: var(--primary-600); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 1.5rem auto;" class="dark:bg-primary-950 dark:text-primary-400">
                    <svg style="width: 2.25rem; height: 2.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"></path></svg>
                </div>
                <h2 style="font-size: 1.5rem; font-weight: 700; margin-bottom: 0.5rem; color: #111827;" class="dark:text-white">Apertura de Turno de Caja</h2>
                <p style="color: var(--gray-500); font-size: 0.9rem; margin-bottom: 1.75rem; line-height: 1.5;">
                    No tienes una sesión de caja abierta en este momento. Ingresa el fondo o monto inicial en efectivo con el que abres la caja para comenzar a cobrar ventas.
                </p>
                <form wire:submit.prevent="openSession" style="display: flex; flex-direction: column; gap: 1.25rem; text-align: left;">
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;" class="dark:text-gray-300">Caja Registradora</label>
                        <select wire:model="selectedRegisterId" class="pos-select" style="width: 100%;">
                            @foreach(\App\Models\CashRegister::where('is_active', true)->get() as $reg)
                                <option value="{{ $reg->id }}">{{ $reg->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-size: 0.85rem; font-weight: 600; margin-bottom: 0.5rem; color: #374151;" class="dark:text-gray-300">Fondo Inicial en Efectivo (S/)</label>
                        <input wire:model="openingBalance" type="number" step="0.01" min="0" class="pos-input" style="width: 100%; font-size: 1.15rem; font-weight: 600;" placeholder="0.00" required>
                        @error('openingBalance') <span style="color: #ef4444; font-size: 0.8rem; margin-top: 0.25rem; display: block;">{{ $message }}</span> @enderror
                    </div>
                    <button type="submit" style="background: var(--primary-600); color: white; padding: 0.9rem; border-radius: 0.75rem; font-weight: 600; font-size: 1rem; border: none; cursor: pointer; transition: background 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); margin-top: 0.5rem; display: flex; align-items: center; justify-content: center; gap: 0.5rem;" class="hover:bg-primary-700">
                        <span>Abrir Caja y Comenzar →</span>
                    </button>
                </form>
            </div>
        </div>
    @else
        <div class="pos-container"
             x-data="{
                handleKeydown(e) {
                    if (e.key === 'F1' || (e.altKey && (e.key === 's' || e.key === 'S' || e.key === 'p' || e.key === 'P'))) {
                        e.preventDefault();
                        const searchInput = document.getElementById('pos-search-input');
                        if (searchInput) {
                            searchInput.focus();
                            searchInput.select();
                        }
                    }
                    else if (e.key === 'F2' || (e.altKey && (e.key === 'c' || e.key === 'C'))) {
                        e.preventDefault();
                        const customerEl = document.querySelector('[id*=\"customer_id\"] input, input[id*=\"customer_id\"], [id*=\"customer_id\"] button, button[role=\"combobox\"][aria-controls*=\"customer_id\"], [id*=\"customer_id\"]');
                        if (customerEl) {
                            customerEl.focus();
                            customerEl.click();
                        }
                    }
                    else if (e.key === 'F9' || ((e.ctrlKey || e.metaKey) && e.key === 'Enter')) {
                        e.preventDefault();
                        const btn = document.getElementById('pos-checkout-btn');
                        if (btn && !btn.disabled) {
                            btn.click();
                        }
                    }
                    else if (e.key === 'F3' || (e.altKey && (e.key === 'l' || e.key === 'L'))) {
                        e.preventDefault();
                        $wire.clearCart();
                    }
                }
             }"
             @keydown.window="handleKeydown($event)"
        >
            
            {{-- Lado Izquierdo: Catálogo de Productos --}}
            <div class="pos-left">
                @if($this->activeSession && !$this->activeSession->opened_at->isToday())
                    <div style="background: #fef2f2; border: 1px solid #f87171; color: #991b1b; padding: 0.75rem 1rem; border-radius: 0.75rem; display: flex; align-items: center; justify-content: space-between; gap: 1rem;" class="dark:bg-red-950/50 dark:border-red-800 dark:text-red-300">
                        <div style="display: flex; align-items: center; gap: 0.75rem;">
                            <svg style="width: 1.5rem; height: 1.5rem; color: #ef4444; flex-shrink: 0;" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <strong style="font-weight: 700;">Turno Abierto de Fecha Anterior:</strong>
                                <span style="font-size: 0.85rem;">Abierta el {{ $this->activeSession->opened_at->format('d/m/Y a las H:i') }}. No olvides realizar el cierre contable.</span>
                            </div>
                        </div>
                        <a href="{{ \App\Filament\Resources\CashSessions\CashSessionResource::getUrl('index') }}" style="background: #ef4444; color: white; padding: 0.4rem 0.8rem; border-radius: 0.5rem; font-size: 0.8rem; font-weight: 600; text-decoration: none; white-space: nowrap;">
                            Cerrar Caja →
                        </a>
                    </div>
                @endif

                {{-- Barra de Atajos Rápida --}}
                <div style="background: var(--gray-100); padding: 0.5rem 0.85rem; border-radius: 0.5rem; display: flex; flex-wrap: wrap; gap: 1rem; align-items: center; justify-content: space-between; font-size: 0.75rem; border: 1px solid var(--gray-200);" class="dark:bg-gray-800 dark:border-gray-700 dark:text-gray-300">
                    <span style="font-weight: 600; color: var(--primary-600);" class="dark:text-primary-400">⌨️ Atajos Rápidos:</span>
                    <div style="display: flex; gap: 0.8rem; flex-wrap: wrap;">
                        <span><kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">F1</kbd> / <kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">Alt+S</kbd> Buscar Prod.</span>
                        <span><kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">F2</kbd> / <kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">Alt+C</kbd> Cliente</span>
                        <span><kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">F3</kbd> Limpiar</span>
                        <span><kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">F9</kbd> / <kbd style="background: white; padding: 0.15rem 0.4rem; border-radius: 0.25rem; border: 1px solid var(--gray-300); font-family: monospace; font-weight: bold;" class="dark:bg-gray-900 dark:border-gray-600">Ctrl+Enter</kbd> Cobrar</span>
                    </div>
                </div>

                {{-- Buscador y Almacén --}}
                <div class="pos-search-box">
                    <input id="pos-search-input" wire:model.live.debounce.300ms="searchQuery" type="text" style="flex: 1;" class="pos-input" placeholder="Buscar productos por nombre o SKU... (F1 / Alt+S)">
                    
                    <select wire:model.live="selectedWarehouseId" style="width: 250px;" class="pos-select">
                        @foreach(\App\Models\Warehouse::where('is_active', true)->get() as $wh)
                            <option value="{{ $wh->id }}" class="dark:bg-gray-800">{{ $wh->name }}</option>
                        @endforeach
                    </select>
                </div>

                {{-- Grid de Productos --}}
                <div class="pos-grid">
                    @foreach($this->products as $product)
                        <div wire:click="addToCart({{ $product->id }})" class="pos-card">
                            @if($product->images && count($product->images) > 0)
                                @php
                                    $imageUrl = $product->images->first()->image_url;
                                    $isExternal = str_starts_with($imageUrl, 'http');
                                    $finalUrl = $isExternal ? $imageUrl : Storage::url($imageUrl);
                                @endphp
                                <img src="{{ $finalUrl }}" alt="{{ $product->name }}" class="pos-image">
                            @else
                                <div class="pos-image" style="display: flex; align-items: center; justify-content: center; color: var(--gray-400);">
                                    <svg class="icon-lg opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                                </div>
                            @endif
                            
                            <div class="pos-card-body">
                                <h3 class="pos-title dark:text-white" title="{{ $product->name }}">{{ $product->name }}</h3>
                                <div class="pos-price-container">
                                    <span class="pos-stock">Stock: {{ $product->warehouse_stock ?? 0 }}</span>
                                    <span class="pos-price">S/ {{ number_format($product->price, 2) }}</span>
                                </div>
                            </div>
                        </div>
                    @endforeach

                    @if($this->products->isEmpty())
                        <div style="grid-column: 1 / -1; padding: 3rem; text-align: center; color: var(--gray-500);">
                            <p>No se encontraron productos.</p>
                        </div>
                    @endif
                </div>
                
                {{-- Paginación --}}
                <div style="margin-top: 1rem;">
                    {{ $this->products->links('components.pos-pagination') }}
                </div>
            </div>

            {{-- Lado Derecho: Carrito y Checkout --}}
            <div class="pos-right">
                
                {{-- Header Carrito --}}
                <div style="padding: 1rem; background: white; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;" class="dark:bg-gray-900 dark:border-gray-800">
                    <h3 style="font-weight: bold; font-size: 1.1rem; margin: 0;" class="dark:text-white">Orden Actual</h3>
                    @if(!empty($cart))
                        <span wire:click="clearCart" style="color: #ef4444; font-size: 0.8rem; cursor: pointer; font-weight: 600;">Limpiar todo</span>
                    @endif
                </div>

                {{-- Lista de Items en Carrito --}}
                <div style="flex: 1; overflow-y: auto; padding: 0.5rem;">
                    @forelse($cart as $id => $item)
                        <div class="pos-cart-item">
                            <div style="flex: 1;">
                                <h4 style="font-size: 0.85rem; font-weight: 600; margin: 0; line-height: 1.2;" class="dark:text-white">{{ $item['name'] }}</h4>
                                <span style="font-size: 0.75rem; color: var(--gray-500);">S/ {{ number_format($item['price'], 2) }} c/u</span>
                            </div>
                            
                            <div style="display: flex; align-items: center; gap: 0.5rem;">
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] - 1 }})" class="pos-qty-btn dark:text-white">-</button>
                                <span style="font-weight: bold; font-size: 0.9rem; min-width: 1.5rem; text-align: center;" class="dark:text-white">{{ $item['quantity'] }}</span>
                                <button wire:click="updateQuantity({{ $id }}, {{ $item['quantity'] + 1 }})" class="pos-qty-btn dark:text-white">+</button>
                                
                                <span style="font-weight: bold; font-size: 0.9rem; margin-left: 0.5rem; width: 4.5rem; text-align: right;" class="dark:text-white">
                                    S/ {{ number_format($item['price'] * $item['quantity'], 2) }}
                                </span>
                            </div>
                        </div>
                    @empty
                        <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--gray-400); gap: 1rem;">
                            <svg class="icon-lg opacity-40" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            <p style="font-size: 0.9rem;">El carrito está vacío</p>
                        </div>
                    @endforelse
                </div>

                {{-- Sección de Pago / Checkout --}}
                <div class="pos-checkout-box">
                    
                    {{-- Selector de Documento (Boleta/Factura) --}}
                    <div style="margin-bottom: 1rem;">
                        <label style="display: block; font-size: 0.75rem; font-weight: 600; color: var(--gray-600); margin-bottom: 0.25rem;" class="dark:text-gray-300">Tipo de Comprobante</label>
                        {{ $this->form }}
                    </div>

                    {{-- Resumen Totales --}}
                    <div style="border-top: 1px dashed var(--gray-300); padding-top: 0.75rem; margin-bottom: 1rem; font-size: 0.85rem;" class="dark:border-gray-700">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.35rem;">
                            <span style="color: var(--gray-600);" class="dark:text-gray-400">Descuento (S/)</span>
                            <input type="number" step="0.50" min="0" wire:model.live="discount_amount" style="width: 5rem; padding: 0.2rem 0.4rem; text-align: right; border: 1px solid var(--gray-300); border-radius: 0.375rem; font-size: 0.85rem;" class="dark:bg-gray-800 dark:border-gray-700 dark:text-white" placeholder="0.00">
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem;">
                            <span style="color: var(--gray-600);" class="dark:text-gray-400">Subtotal</span>
                            <span class="dark:text-white">S/ {{ number_format($subtotal, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem;">
                            <span style="color: var(--gray-600);" class="dark:text-gray-400">IGV (18%)</span>
                            <span class="dark:text-white">S/ {{ number_format($total_tax, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; font-size: 1.15rem; font-weight: bold; border-top: 1px solid var(--gray-200); padding-top: 0.5rem;" class="dark:border-gray-700">
                            <span class="dark:text-white">TOTAL A PAGAR</span>
                            <span class="pos-price" style="font-size: 1.3rem;">S/ {{ number_format($total_amount, 2) }}</span>
                        </div>
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-top: 0.5rem; padding-top: 0.5rem; border-top: 1px dashed var(--gray-300);" class="dark:border-gray-700">
                            <span style="color: var(--gray-600);" class="dark:text-gray-400">Efectivo Recibido</span>
                            <input type="number" step="1.00" min="0" wire:model.live="cash_received" style="width: 6rem; padding: 0.2rem 0.4rem; text-align: right; border: 1px solid var(--gray-300); border-radius: 0.375rem; font-size: 0.85rem;" class="dark:bg-gray-800 dark:border-gray-700 dark:text-white" placeholder="0.00">
                        </div>
                        @if($cash_received > 0)
                        <div style="display: flex; justify-content: space-between; margin-top: 0.35rem; font-weight: 600; color: #10b981;">
                            <span>Vuelto / Cambio</span>
                            <span>S/ {{ number_format($change_amount, 2) }}</span>
                        </div>
                        @endif
                    </div>

                    {{-- Botón de Cobrar --}}
                    <button 
                        id="pos-checkout-btn"
                        wire:click="checkout" 
                        wire:loading.attr="disabled"
                        @if(empty($cart)) disabled style="opacity: 0.5; cursor: not-allowed;" @endif
                        style="width: 100%; padding: 0.85rem; background: var(--primary-600); color: white; border-radius: 0.75rem; font-weight: 700; font-size: 1rem; border: none; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.25); display: flex; justify-content: center; align-items: center; gap: 0.5rem;" class="hover:bg-primary-700">
                        <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                        <span wire:loading.remove>COBRAR Y EMITIR COMPROBANTE</span>
                        <span wire:loading>Procesando Venta...</span>
                    </button>
                    
                </div>
            </div>

        </div>
    @endif
</x-filament-panels::page>
