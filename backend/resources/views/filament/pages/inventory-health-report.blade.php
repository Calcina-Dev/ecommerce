<x-filament-panels::page>
    <div class="grid grid-cols-1 gap-6 mb-6">
        @livewire(\App\Filament\Widgets\InventoryHealthOverviewWidget::class)
    </div>

    <div class="grid grid-cols-1 gap-6 mb-6">
        @livewire(\App\Filament\Widgets\StockRotationChart::class)
    </div>

    <div class="grid grid-cols-1 gap-6">
        @livewire(\App\Filament\Widgets\DeadStockTable::class)
    </div>
</x-filament-panels::page>
