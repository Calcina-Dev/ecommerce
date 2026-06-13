<?php

namespace App\Filament\Resources\CashRegisters;

use App\Filament\Resources\CashRegisters\Pages\ManageCashRegisters;
use App\Models\CashRegister;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CashRegisterResource extends Resource
{
    protected static ?string $model = CashRegister::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-briefcase';
    protected static ?string $navigationLabel = 'Cajas Físicas';
    protected static ?string $modelLabel = 'Caja Física';
    protected static ?string $pluralModelLabel = 'Cajas Físicas';
    protected static \UnitEnum|string|null $navigationGroup = 'Tesorería y Pagos';

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Forms\Components\TextInput::make('name')
                    ->label('Nombre de la Caja')
                    ->required()
                    ->maxLength(255),
                \Filament\Forms\Components\Toggle::make('is_active')
                    ->label('Caja Activa')
                    ->default(true),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\ToggleColumn::make('is_active')
                    ->label('Activa')
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha de Creación')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCashRegisters::route('/'),
        ];
    }
}
