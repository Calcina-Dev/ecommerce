<x-filament-panels::page>
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 mb-6">
        @livewire(\App\Filament\Widgets\CustomerInsightsOverviewWidget::class)
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
        <div class="md:col-span-1">
            @livewire(\App\Filament\Widgets\CustomerLtvChart::class)
        </div>
        <div class="md:col-span-1">
            @livewire(\App\Filament\Widgets\PurchaseFrequencyChart::class)
        </div>
    </div>

    <div class="grid grid-cols-1 gap-6">
        @livewire(\App\Filament\Widgets\TopCustomersTable::class)
    </div>
</x-filament-panels::page>
