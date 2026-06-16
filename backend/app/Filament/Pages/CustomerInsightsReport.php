<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use BackedEnum;
use UnitEnum;

class CustomerInsightsReport extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';
    protected static ?string $navigationLabel = 'Customer Insights';
    protected static ?string $title = 'Inteligencia de Clientes';
    protected static ?string $slug = 'customer-insights';
    protected string $view = 'filament.pages.customer-insights-report';
    protected static UnitEnum|string|null $navigationGroup = 'Analítica y Reportes';
    protected static ?int $navigationSort = 4;

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\CustomerInsightsOverviewWidget::class,
            \App\Filament\Widgets\CustomerLtvChart::class,
            \App\Filament\Widgets\PurchaseFrequencyChart::class,
            \App\Filament\Widgets\TopCustomersTable::class,
        ];
    }
}
