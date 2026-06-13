<?php

namespace App\Filament\Resources\StockMovements\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class StockMovementsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->defaultSort('created_at', 'desc')
            ->columns([
                TextColumn::make('document_number')
                    ->label('N° Kardex')
                    ->getStateUsing(fn ($record) => ($record->document_series ? $record->document_series . '-' : '') . $record->document_number)
                    ->searchable(['document_number', 'document_series']),
                TextColumn::make('related_document')
                    ->label('Doc. Origen')
                    ->getStateUsing(function ($record) {
                        $ref = $record->reference;
                        if (!$ref) return $record->reference_type . ' ' . $record->reference_id;
                        
                        if ($record->reference_type === 'ORDER') {
                            return ($ref->document_series ? "{$ref->document_series}-{$ref->document_number} " : '') . "({$ref->order_number})";
                        }
                        if ($record->reference_type === 'SALE' || $record->reference_type === 'PURCHASE') {
                            return $ref->document_series ? "{$ref->document_series}-{$ref->document_number}" : $ref->id;
                        }
                        if ($record->reference_type === \App\Models\PurchaseInvoice::class) {
                            return 'Recepcion: ' . $ref->document_number;
                        }
                        return $record->reference_type . ' ' . $record->reference_id;
                    })
                    ->searchable(query: function (\Illuminate\Database\Eloquent\Builder $query, string $search): \Illuminate\Database\Eloquent\Builder {
                        return $query->where('notes', 'ilike', "%{$search}%")
                            ->orWhereHasMorph('reference', [\App\Models\Order::class], function (\Illuminate\Database\Eloquent\Builder $query) use ($search) {
                                $query->where('order_number', 'ilike', "%{$search}%")
                                      ->orWhere('document_number', 'ilike', "%{$search}%");
                            });
                    }),
                TextColumn::make('created_at')
                    ->label('Fecha')
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('product.name')
                    ->label('Producto')
                    ->searchable(),
                TextColumn::make('warehouse.name')
                    ->label('Almacén')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('batch.batch_number')
                    ->label('Lote')
                    ->searchable()
                    ->badge(),
                TextColumn::make('type')
                    ->label('Tipo')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'IN' => 'success',
                        'OUT' => 'danger',
                        'ADJUSTMENT' => 'warning',
                        default => 'gray',
                    }),
                TextColumn::make('quantity')
                    ->label('Cant.')
                    ->numeric()
                    ->sortable(),
                TextColumn::make('unit_cost')
                    ->label('Costo Unit.')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label('Costo Total')
                    ->money('PEN')
                    ->sortable(),
                TextColumn::make('reason')
                    ->label('Motivo')
                    ->searchable(),
                TextColumn::make('user.name')
                    ->label('Usuario')
                    ->searchable()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                \Filament\Actions\ViewAction::make(),
            ])
            ->bulkActions([
                //
            ]);
    }
}
