<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class MarketingReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Marketing y Ventas';
    protected static ?int $navigationSort = 2;
    protected static string $routePath = 'marketing-report';

    public static function getNavigationIcon(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'heroicon-o-presentation-chart-line';
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
            \App\Filament\Widgets\ChannelEfficiencyChart::class,
            \App\Filament\Widgets\TopCategoriesChart::class,
            \App\Filament\Widgets\CouponsUsageTable::class,
        ];
    }
}
