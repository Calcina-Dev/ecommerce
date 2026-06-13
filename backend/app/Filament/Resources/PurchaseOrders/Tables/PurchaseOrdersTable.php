<?php

namespace App\Filament\Resources\PurchaseOrders\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Table;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Actions\Action;
use App\Models\Product;
use App\Models\InventoryMovement;

class PurchaseOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')->searchable()->sortable(),
                TextColumn::make('supplier.name')->searchable()->sortable(),
                TextColumn::make('status')
                    ->badge()
                    ->colors([
                        'danger' => 'cancelled',
                        'warning' => 'draft',
                        'primary' => 'sent',
                        'info' => 'partial',
                        'success' => 'completed',
                    ]),
                TextColumn::make('total_amount')->money('PEN')->sortable(),
                TextColumn::make('expected_delivery_date')->date()->sortable(),
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
                        'draft' => 'Borrador',
                        'sent' => 'Enviado',
                        'partial' => 'Parcial',
                        'completed' => 'Completado',
                        'cancelled' => 'Cancelado',
                    ]),
                \Filament\Tables\Filters\Filter::make('expected_delivery_date')
                    ->form([
                        \Filament\Forms\Components\DatePicker::make('delivery_from')->label('Entrega Desde'),
                        \Filament\Forms\Components\DatePicker::make('delivery_until')->label('Entrega Hasta'),
                    ])
                    ->query(function (\Illuminate\Database\Eloquent\Builder $query, array $data): \Illuminate\Database\Eloquent\Builder {
                        return $query
                            ->when(
                                $data['delivery_from'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('expected_delivery_date', '>=', $date),
                            )
                            ->when(
                                $data['delivery_until'],
                                fn (\Illuminate\Database\Eloquent\Builder $query, $date): \Illuminate\Database\Eloquent\Builder => $query->whereDate('expected_delivery_date', '<=', $date),
                            );
                    }),
            ])
            ->actions([
                \Filament\Actions\Action::make('generate_invoice')
                    ->label('Generar Factura')
                    ->icon('heroicon-o-document-plus')
                    ->color('success')
                    ->visible(fn (\App\Models\PurchaseOrder $record) => in_array($record->status, ['draft', 'sent', 'partial']))
                    ->form([
                        \Filament\Forms\Components\Select::make('warehouse_id')
                            ->label('Almacén de Recepción')
                            ->options(\App\Models\Warehouse::pluck('name', 'id'))
                            ->required(),
                        \Filament\Forms\Components\TextInput::make('document_number')
                            ->label('Número de Factura / Guía')
                            ->required(),
                        \Filament\Forms\Components\DatePicker::make('issue_date')
                            ->label('Fecha de Emisión')
                            ->default(now())
                            ->required(),
                    ])
                    ->action(function (\App\Models\PurchaseOrder $record, array $data) {
                        $invoice = \App\Models\PurchaseInvoice::create([
                            'purchase_order_id' => $record->id,
                            'supplier_id' => $record->supplier_id,
                            'warehouse_id' => $data['warehouse_id'],
                            'document_number' => $data['document_number'],
                            'issue_date' => $data['issue_date'],
                            'total_amount' => 0,
                            'status' => 'DRAFT',
                        ]);

                        $receivedQuantities = \Illuminate\Support\Facades\DB::table('purchase_invoice_lines')
                            ->join('purchase_invoices', 'purchase_invoice_lines.purchase_invoice_id', '=', 'purchase_invoices.id')
                            ->where('purchase_invoices.purchase_order_id', $record->id)
                            ->where('purchase_invoices.status', '!=', 'CANCELLED')
                            ->groupBy('purchase_invoice_lines.product_id')
                            ->select('purchase_invoice_lines.product_id', \Illuminate\Support\Facades\DB::raw('SUM(purchase_invoice_lines.quantity) as total_received'))
                            ->pluck('total_received', 'product_id');

                        $totalAmount = 0;
                        $receivedConsumed = [];

                        foreach ($record->items as $item) {
                            $totalReceivedForProduct = $receivedQuantities->get($item->product_id, 0);
                            $consumedForProduct = $receivedConsumed[$item->product_id] ?? 0;
                            
                            $availableReceived = max(0, $totalReceivedForProduct - $consumedForProduct);
                            
                            $consumedFromThisLine = min($availableReceived, $item->quantity);
                            $remaining = $item->quantity - $consumedFromThisLine;
                            
                            $receivedConsumed[$item->product_id] = $consumedForProduct + $consumedFromThisLine;

                            if ($remaining > 0) {
                                $subtotal = $remaining * $item->unit_cost;
                                $totalAmount += $subtotal;

                                \App\Models\PurchaseInvoiceLine::create([
                                    'purchase_invoice_id' => $invoice->id,
                                    'product_id' => $item->product_id,
                                    'quantity' => $remaining,
                                    'unit_cost' => $item->unit_cost,
                                    'subtotal' => $subtotal,
                                ]);
                            }
                        }

                        $invoice->update(['total_amount' => $totalAmount]);

                        \Filament\Notifications\Notification::make()->title('Factura en Borrador creada')->success()->send();
                        
                        return redirect(\App\Filament\Resources\PurchaseInvoices\PurchaseInvoiceResource::getUrl('edit', ['record' => $invoice]));
                    }),
                \Filament\Actions\ViewAction::make(),
                EditAction::make()
                    ->visible(fn (\App\Models\PurchaseOrder $record) => !in_array($record->status, ['cancelled', 'completed', 'partial'])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
