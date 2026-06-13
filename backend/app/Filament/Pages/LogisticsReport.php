<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class LogisticsReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Logística y Operaciones';
    protected static ?int $navigationSort = 3;
    protected static string $routePath = 'logistics-report';

    public static function getNavigationIcon(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'heroicon-o-truck';
    }

    public static function getNavigationGroup(): ?string
    {
        return 'Analítica y Reportes';
    }

    public function filtersForm(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\Select::make('period')
                    ->label('Período')
                    ->options([
                        'week' => 'Esta Semana',
                        'month' => 'Este Mes',
                        'year' => 'Este Año',
                        'custom' => 'Personalizado',
                    ])
                    ->default('month')
                    ->live(),
                \Filament\Forms\Components\DatePicker::make('date_from')
                    ->label('Desde')
                    ->visible(fn ($get) => $get('period') === 'custom')
                    ->live(),
                \Filament\Forms\Components\DatePicker::make('date_to')
                    ->label('Hasta')
                    ->visible(fn ($get) => $get('period') === 'custom')
                    ->live(),
            ])
            ->columns(3);
    }

    public function getColumns(): array | int
    {
        return 2;
    }

    public function getWidgets(): array
    {
        return [
            \App\Filament\Widgets\ShippingStatusChart::class,
            \App\Filament\Widgets\OrdersMapWidget::class,
            \App\Filament\Widgets\LogisticsReportTable::class,
        ];
    }
}
