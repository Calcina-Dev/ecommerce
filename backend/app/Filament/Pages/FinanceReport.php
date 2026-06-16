<?php

namespace App\Filament\Pages;

use Filament\Schemas\Schema;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class FinanceReport extends BaseDashboard
{
    use HasFiltersForm;

    protected static ?string $title = 'Finanzas y Tesorería';
    protected static ?int $navigationSort = 4;
    protected static string $routePath = 'finance-report';

    public static function getNavigationIcon(): string | \Illuminate\Contracts\Support\Htmlable | null
    {
        return 'heroicon-o-banknotes';
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
                        'today' => 'Hoy',
                        'week' => 'Esta Semana',
                        'month' => 'Este Mes',
                        'year' => 'Este Año',
                        'custom' => 'Personalizado',
                    ])
                    ->default('today')
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
            \App\Filament\Widgets\CashFlowOverview::class,
            \App\Filament\Widgets\PaymentMethodsBreakdownChart::class,
            \App\Filament\Widgets\DiscrepanciesTable::class,
        ];
    }
}
