<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class InventoryHealthReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-heart';
    protected static ?string $navigationLabel = 'Salud de Inventario';
    protected static ?string $title = 'Salud y Optimización de Inventario';
    protected static ?string $slug = 'inventory-health';
    protected string $view = 'filament.pages.inventory-health-report';
    protected static UnitEnum|string|null $navigationGroup = 'Analítica y Reportes';
    protected static ?int $navigationSort = 5;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\InventoryHealthOverviewWidget::class,
            \App\Filament\Widgets\StockRotationChart::class,
            \App\Filament\Widgets\DeadStockTable::class,
        ];
    }
}
