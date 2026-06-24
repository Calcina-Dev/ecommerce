<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('name')
                    ->label('Nombre')
                    ->searchable()
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('dni')
                    ->label('DNI/RUC')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('email')
                    ->label('Correo')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('phone')
                    ->label('Teléfono')
                    ->searchable(),
                \Filament\Tables\Columns\TextColumn::make('total_spent')
                    ->label('Total Comprado')
                    ->money('PEN')
                    ->getStateUsing(function ($record) {
                        $posSales = $record->sales()->where('status', 'CONFIRMED')->sum('total_amount');
                        $onlineOrders = $record->orders()->whereNotIn('status', ['cancelled', 'failed'])->sum('total_amount');
                        return $posSales + $onlineOrders;
                    })
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
