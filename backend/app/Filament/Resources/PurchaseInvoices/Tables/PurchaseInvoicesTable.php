<?php

namespace App\Filament\Resources\PurchaseInvoices\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\Action;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class PurchaseInvoicesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('document_number')
                    ->label('Factura / Guía')
                    ->searchable(),
                TextColumn::make('supplier.name')
                    ->label('Proveedor')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('issue_date')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                TextColumn::make('total_amount')
                    ->label('Total')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('status')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VALID' => 'success',
                        'DRAFT' => 'warning',
                        'CANCELLED' => 'danger',
                        default => 'gray',
                    }),
                TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                \Filament\Tables\Filters\SelectFilter::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->searchable()
                    ->preload(),
                \Filament\Tables\Filters\SelectFilter::make('status')
                    ->label('Estado')
                    ->options([
                        'VALID' => 'Válido',
                        'DRAFT' => 'Borrador',
                        'CANCELLED' => 'Anulado',
                    ]),
                \Filament\Tables\Filters\Filter::make('issue_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('date_from')->label('Emitido Desde'),
                        \Filament\Forms\Components\DatePicker::make('date_until')->label('Emitido Hasta'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['date_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('issue_date', '>=', $date),
                            )
                            ->when(
                                $data['date_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('issue_date', '<=', $date),
                            );
                    }),
            ])
            ->recordActions([
                Action::make('confirm')
                    ->label('Confirmar Recepción')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->visible(fn (\App\Models\PurchaseInvoice $record) => $record->status === 'DRAFT')
                    ->action(function (\App\Models\PurchaseInvoice $record) {
                        foreach ($record->lines as $line) {
                            if (empty($line->batch_number) || empty($line->expiration_date)) {
                                \Filament\Notifications\Notification::make()
                                    ->title('Faltan Lotes o Fechas')
                                    ->body('Debes editar la factura e ingresar el N° de Lote y Fecha de Caducidad para todos los productos antes de poder ingresar al inventario.')
                                    ->danger()
                                    ->send();
                                return;
                            }
                        }
                        $record->update(['status' => 'VALID']);
                    }),
                Action::make('cancel')
                    ->label('Anular')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->visible(fn (\App\Models\PurchaseInvoice $record) => $record->status === 'VALID')
                    ->action(fn (\App\Models\PurchaseInvoice $record) => clone $record->update(['status' => 'CANCELLED']) ? null : null),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
