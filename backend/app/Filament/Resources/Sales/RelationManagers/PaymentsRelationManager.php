<?php

namespace App\Filament\Resources\Sales\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $title = 'Pagos Realizados (Abonos)';

    public function isReadOnly(): bool
    {
        return false;
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('payment_method_id')
                    ->label('Método de Pago')
                    ->relationship('paymentMethod', 'name')
                    ->required(),
                TextInput::make('amount')
                    ->label('Monto (S/)')
                    ->numeric()
                    ->required()
                    ->maxValue(function () {
                        $sale = $this->getOwnerRecord();
                        $paid = $sale->payments()->sum('amount');
                        return max(0, round($sale->total_amount - $paid, 2));
                    })
                    ->default(function () {
                        $sale = $this->getOwnerRecord();
                        $paid = $sale->payments()->sum('amount');
                        $balance = max(0, round($sale->total_amount - $paid, 2));
                        return $balance > 0 ? $balance : 0;
                    }),
                TextInput::make('reference')
                    ->label('Referencia (Opcional)')
                    ->maxLength(255),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('paymentMethod.name')
            ->columns([
                TextColumn::make('paymentMethod.name')
                    ->label('Método de Pago')
                    ->searchable(),
                TextColumn::make('amount')
                    ->label('Monto Abonado')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('reference')
                    ->label('Referencia'),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime('d/m/Y H:i'),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make()
                    ->label('Abonar Saldo')
                    ->icon('heroicon-o-plus')
                    ->visible(function () {
                        $sale = $this->getOwnerRecord();
                        if ($sale->status !== 'CONFIRMED') return false;
                        $paid = $sale->payments()->sum('amount');
                        return round($paid, 2) < round($sale->total_amount, 2);
                    }),
            ])
            ->recordActions([
                DeleteAction::make()
                    ->label('Anular Pago')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn () => $this->getOwnerRecord()->status === 'CONFIRMED'),
            ])
            ->toolbarActions([]);
    }
}
