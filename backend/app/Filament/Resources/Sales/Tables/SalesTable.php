<?php

namespace App\Filament\Resources\Sales\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\Action;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class SalesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                \Filament\Tables\Columns\TextColumn::make('document_type')
                    ->label('Tipo Doc.')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'FACTURA' => 'primary',
                        'BOLETA' => 'info',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('document_number')
                    ->label('N°')
                    ->getStateUsing(fn ($record) => ($record->document_series ? $record->document_series . '-' : '') . $record->document_number)
                    ->searchable(['document_number', 'document_series']),
                \Filament\Tables\Columns\TextColumn::make('customer.name')
                    ->label('Cliente')
                    ->getStateUsing(fn ($record) => $record->customer_id ? $record->customer->name : ($record->customer_email ?? 'Anónimo'))
                    ->searchable(['customer_email'])
                    ->sortable(),
                \Filament\Tables\Columns\TextColumn::make('warehouse.name')
                    ->label('Almacén'),
                \Filament\Tables\Columns\TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable()
                    ->weight('bold'),
                \Filament\Tables\Columns\TextColumn::make('payment_status')
                    ->label('Pago')
                    ->getStateUsing(function ($record) {
                        if ($record->status === 'CANCELLED') return 'Anulado';
                        $paid = $record->payments()->sum('amount');
                        if ($paid >= $record->total_amount) return 'Pagado';
                        if ($paid > 0) return 'Parcial';
                        return 'Deuda';
                    })
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'Pagado' => 'success',
                        'Parcial' => 'warning',
                        'Deuda' => 'danger',
                        default => 'gray',
                    }),
                \Filament\Tables\Columns\TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'CONFIRMED' => 'success',
                        'CANCELLED' => 'danger',
                        default => 'warning',
                    }),
                \Filament\Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('warehouse_id')
                    ->label('Almacén')
                    ->relationship('warehouse', 'name'),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'CONFIRMED' => 'Confirmado',
                        'CANCELLED' => 'Anulado',
                    ]),
                TrashedFilter::make(),
            ])
            ->recordActions([
                Action::make('cancel')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (\App\Models\Sale $record) => $record->status === 'CONFIRMED')
                    ->action(fn (\App\Models\Sale $record) => $record->update(['status' => 'CANCELLED'])),
                Action::make('print')
                    ->label('Ticket')
                    ->icon('heroicon-o-printer')
                    ->color('info')
                    ->url(fn (\App\Models\Sale $record) => route('sale.ticket', $record->id))
                    ->openUrlInNewTab(),
                ViewAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }
}
