<?php

namespace App\Filament\Pages;

use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Schemas\Schema;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;

class Dashboard extends \Filament\Pages\Dashboard
{
    use HasFiltersForm;

    protected static string|\BackedEnum|null $navigationIcon = \Filament\Support\Icons\Heroicon::OutlinedPresentationChartLine;
    protected static ?string $title = 'Panel de Control';

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
                    ->native(false)
                    ->selectablePlaceholder(false)
                    ->live(),
                \Filament\Forms\Components\DatePicker::make('date_from')
                    ->label('Desde')
                    ->visible(fn ($get) => $get('period') === 'custom')
                    ->maxDate(fn ($get) => $get('date_to') ?: now()),
                \Filament\Forms\Components\DatePicker::make('date_to')
                    ->label('Hasta')
                    ->visible(fn ($get) => $get('period') === 'custom')
                    ->minDate(fn ($get) => $get('date_from'))
                    ->maxDate(now()),
            ])
            ->columns(3);
    }
}
