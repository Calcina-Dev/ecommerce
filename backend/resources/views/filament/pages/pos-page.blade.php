<x-filament-panels::page>
    <style>
        .pos-container {
            display: flex;
            flex-direction: column;
            gap: 1.5rem;
            height: calc(100vh - 8rem);
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
            gap: 1rem;
            overflow-y: auto;
            padding-bottom: 1rem;
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
            min-height: 220px; /* Asegurar suficiente altura */
        }
        .dark .pos-card {
            background: var(--gray-900);
            border-color: var(--gray-800);
        }
        .pos-card:hover {
            border-color: var(--primary-500);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1);
        }
        .pos-image {
            width: 100%;
            height: 140px; /* Un poco más grande para que la foto luzca */
            object-fit: cover;
            background: var(--gray-100);
        }
        .dark .pos-image {
            background: var(--gray-800);
        }
        .pos-card-body {
            padding: 0.75rem;
            display: flex;
            flex-direction: column;
            flex: 1;
            justify-content: space-between;
            gap: 0.5rem; /* Espacio entre el título y el precio */
        }
        .pos-title {
            font-size: 0.875rem;
            font-weight: 600; /* Un poco más gordito para mejor legibilidad */
            line-height: 1.25;
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
            margin-top: auto; /* Empujar hacia abajo siempre */
        }
        .pos-stock {
            font-size: 0.75rem; 
            color: var(--gray-500);
        }
        .pos-price {
            font-size: 1.125rem;
            font-weight: 700;
            color: var(--primary-600);
            line-height: 1; /* Prevenir cutoff */
        }
        .dark .pos-price {
            color: var(--primary-400);
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
    </style>

    <div class="pos-container">
        
        {{-- Lado Izquierdo: Catálogo de Productos --}}
        <div class="pos-left">
            {{-- Buscador y Almacén --}}
            <div style="background: white; padding: 1rem; border-radius: 0.75rem; border: 1px solid var(--gray-200); display: flex; gap: 1rem;" class="dark:bg-gray-900 dark:border-gray-800">
                <input wire:model.live.debounce.300ms="searchQuery" type="text" style="flex: 1; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 0.5rem; background: transparent;" class="dark:border-gray-700 dark:text-white" placeholder="Buscar productos por nombre o SKU...">
                
                <select wire:model.live="selectedWarehouseId" style="width: 250px; padding: 0.5rem; border: 1px solid var(--gray-300); border-radius: 0.5rem; background: transparent;" class="dark:border-gray-700 dark:text-white">
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
            <div style="padding: 1rem; border-bottom: 1px solid var(--gray-200); display: flex; justify-content: space-between; align-items: center;" class="dark:border-gray-800">
                <h2 style="font-weight: bold; font-size: 1.125rem;" class="dark:text-white">Ticket de Venta</h2>
                <span style="background: var(--primary-600); color: white; padding: 0.25rem 0.5rem; border-radius: 9999px; font-size: 0.75rem; font-weight: bold;">
                    {{ count($cart) }} items
                </span>
            </div>

            {{-- Items del Carrito --}}
            <div style="flex: 1; overflow-y: auto; padding: 0.5rem;">
                @forelse($cart as $id => $item)
                    <div class="pos-cart-item">
                        <div style="flex: 1; padding-right: 0.5rem;">
                            <div style="font-size: 0.875rem; font-weight: 500;" class="dark:text-white line-clamp-1">{{ $item['name'] }}</div>
                            <div class="pos-price" style="font-size: 0.875rem;">S/ {{ number_format($item['price'], 2) }}</div>
                        </div>
                        
                        <div style="display: flex; align-items: center; gap: 0.5rem;">
                            <button wire:click="decreaseQuantity({{ $id }})" class="pos-qty-btn dark:text-white">-</button>
                            <span style="width: 1.5rem; text-align: center; font-size: 0.875rem;" class="dark:text-white">{{ $item['quantity'] }}</span>
                            <button wire:click="increaseQuantity({{ $id }})" class="pos-qty-btn dark:text-white">+</button>
                            
                            <button wire:click="removeFromCart({{ $id }})" style="color: red; padding: 0.25rem; margin-left: 0.5rem;">
                                <svg class="icon-sm" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                @empty
                    <div style="height: 100%; display: flex; flex-direction: column; align-items: center; justify-content: center; color: var(--gray-400);">
                        <svg class="icon-lg" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        <p style="margin-top: 0.5rem;">Agrega productos</p>
                    </div>
                @endforelse
            </div>

            {{-- Formulario de Pago y Totales --}}
            <div>
                <div style="padding: 1rem; max-height: 250px; overflow-y: auto; border-top: 1px solid var(--gray-200);" class="dark:border-gray-800">
                    {{ $this->form }}
                </div>
                
                <div style="padding: 1rem; background: white; border-top: 1px solid var(--gray-200);" class="dark:bg-gray-950 dark:border-gray-800">
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.25rem; font-size: 0.875rem; color: var(--gray-500);">
                        <span>Subtotal</span>
                        <span class="dark:text-white">S/ {{ number_format($subtotal, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 0.5rem; font-size: 0.875rem; color: var(--gray-500);">
                        <span>IGV (18%)</span>
                        <span class="dark:text-white">S/ {{ number_format($total_tax, 2) }}</span>
                    </div>
                    <div style="display: flex; justify-content: space-between; margin-bottom: 1rem; font-size: 1.25rem; font-weight: bold;">
                        <span class="dark:text-white">TOTAL</span>
                        <span class="pos-price" style="font-size: 1.5rem;">S/ {{ number_format($total_amount, 2) }}</span>
                    </div>
                    
                    <button 
                        wire:click="checkout" 
                        wire:loading.attr="disabled"
                        style="width: 100%; padding: 0.75rem; background: var(--primary-600); color: white; font-weight: bold; border-radius: 0.5rem; display: flex; justify-content: center; align-items: center; gap: 0.5rem;"
                    >
                        <span wire:loading.remove>PROCESAR VENTA</span>
                        <span wire:loading>Procesando...</span>
                    </button>
                </div>
            </div>

        </div>
    </div>
</x-filament-panels::page>
