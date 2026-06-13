<?php

namespace App\Filament\Resources\PurchaseInvoices\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class PurchaseInvoiceForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                \Filament\Schemas\Components\Group::make()
                    ->columns(2)
                    ->columnSpanFull()
                    ->disabled(fn (?\App\Models\PurchaseInvoice $record) => $record !== null && $record->status !== 'DRAFT')
                    ->schema([
                \Filament\Forms\Components\Select::make('supplier_id')
                    ->label('Proveedor')
                    ->relationship('supplier', 'name')
                    ->required(),
                \Filament\Forms\Components\Select::make('warehouse_id')
                    ->label('Almacén de Recepción')
                    ->relationship('warehouse', 'name')
                    ->required(),
                \Filament\Forms\Components\Select::make('purchase_order_id')
                    ->label('Orden de Compra Asociada (Opcional)')
                    ->relationship('purchaseOrder', 'order_number', modifyQueryUsing: function ($query, $livewire) {
                        if ($livewire instanceof \Filament\Resources\Pages\CreateRecord) {
                            return $query->whereIn('status', ['sent', 'partial']);
                        }
                        return $query;
                    })
                    ->searchable()
                    ->preload()
                    ->live()
                    ->afterStateUpdated(function ($state, callable $set) {
                        if (!$state) return;
                        
                        $order = \App\Models\PurchaseOrder::with('items')->find($state);
                        if (!$order) return;
                        
                        $set('supplier_id', $order->supplier_id);
                        
                        $receivedQuantities = \Illuminate\Support\Facades\DB::table('purchase_invoice_lines')
                            ->join('purchase_invoices', 'purchase_invoice_lines.purchase_invoice_id', '=', 'purchase_invoices.id')
                            ->where('purchase_invoices.purchase_order_id', $order->id)
                            ->where('purchase_invoices.status', '!=', 'CANCELLED')
                            ->groupBy('purchase_invoice_lines.product_id')
                            ->select('purchase_invoice_lines.product_id', \Illuminate\Support\Facades\DB::raw('SUM(purchase_invoice_lines.quantity) as total_received'))
                            ->pluck('total_received', 'product_id');

                        $lines = [];
                        $totalAmount = 0;
                        $receivedConsumed = [];

                        foreach ($order->items as $item) {
                            $totalReceivedForProduct = $receivedQuantities->get($item->product_id, 0);
                            $consumedForProduct = $receivedConsumed[$item->product_id] ?? 0;
                            
                            $availableReceived = max(0, $totalReceivedForProduct - $consumedForProduct);
                            
                            $consumedFromThisLine = min($availableReceived, $item->quantity);
                            $remaining = $item->quantity - $consumedFromThisLine;
                            
                            $receivedConsumed[$item->product_id] = $consumedForProduct + $consumedFromThisLine;

                            if ($remaining > 0) {
                                $subtotal = $remaining * $item->unit_cost;
                                $totalAmount += $subtotal;
                                
                                $lines[\Illuminate\Support\Str::uuid()->toString()] = [
                                    'product_id' => $item->product_id,
                                    'quantity' => $remaining,
                                    'unit_cost' => $item->unit_cost,
                                    'subtotal' => $subtotal,
                                ];
                            }
                        }
                        
                        $set('lines', $lines);
                        $set('total_amount', $totalAmount);
                    }),
                TextInput::make('document_number')
                    ->label('Número de Factura / Guía')
                    ->required(),
                DatePicker::make('issue_date')
                    ->label('Fecha de Emisión')
                    ->default(now())
                    ->required(),
                TextInput::make('shipping_cost')
                    ->label('Flete / Gastos de Envío (S/)')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $lines = $get('lines') ?? [];
                        $total = 0;
                        foreach ($lines as $line) {
                            $total += ((float) ($line['quantity'] ?? 0)) * ((float) ($line['unit_cost'] ?? 0));
                        }
                        $shipping = (float) $get('shipping_cost');
                        $discount = (float) $get('discount');
                        $set('total_amount', $total + $shipping - $discount);
                    }),
                TextInput::make('discount')
                    ->label('Descuento Global (S/)')
                    ->numeric()
                    ->default(0)
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $lines = $get('lines') ?? [];
                        $total = 0;
                        foreach ($lines as $line) {
                            $total += ((float) ($line['quantity'] ?? 0)) * ((float) ($line['unit_cost'] ?? 0));
                        }
                        $shipping = (float) $get('shipping_cost');
                        $discount = (float) $get('discount');
                        $set('total_amount', $total + $shipping - $discount);
                    }),
                TextInput::make('total_amount')
                    ->label('Monto Total (Con Flete y Dscto)')
                    ->required()
                    ->numeric()
                    ->prefix('S/')
                    ->default(0)
                    ->readOnly(),
                \Filament\Forms\Components\Select::make('status')
                    ->label('Estado')
                    ->options([
                        'DRAFT' => 'Borrador',
                        'VALID' => 'Confirmado (Ingresa a Almacén)',
                        'CANCELLED' => 'Anulado',
                    ])
                    ->required()
                    ->default('DRAFT')
                    ->disabled()
                    ->dehydrated(),
                \Filament\Forms\Components\Repeater::make('lines')
                    ->label('Detalle de Productos y Lotes')
                    ->relationship()
                    ->live(onBlur: true)
                    ->afterStateUpdated(function ($get, $set) {
                        $lines = $get('lines') ?? [];
                        $total = 0;
                        foreach ($lines as $line) {
                            $total += (float) ($line['subtotal'] ?? 0);
                        }
                        $shipping = (float) $get('shipping_cost');
                        $discount = (float) $get('discount');
                        $set('total_amount', $total + $shipping - $discount);
                    })
                    ->schema([
                        \Filament\Forms\Components\Select::make('product_id')
                            ->label('Producto')
                            ->relationship('product', 'name')
                            ->required()
                            ->columnSpan(2),
                        TextInput::make('batch_number')
                            ->label('N° de Lote')
                            ->required()
                            ->columnSpan(1),
                        DatePicker::make('expiration_date')
                            ->label('Fecha de Caducidad')
                            ->required()
                            ->columnSpan(1),
                        TextInput::make('quantity')
                            ->label('Cantidad')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, $state) {
                                $subtotal = $state * ((float) $get('unit_cost'));
                                $set('subtotal', $subtotal);
                                
                                $lines = $get('../../lines') ?? [];
                                $total = 0;
                                foreach ($lines as $line) {
                                    $total += ((float) ($line['quantity'] ?? 0)) * ((float) ($line['unit_cost'] ?? 0));
                                }
                                $shipping = (float) $get('../../shipping_cost');
                                $discount = (float) $get('../../discount');
                                $set('../../total_amount', $total + $shipping - $discount);
                            })
                            ->columnSpan(1),
                        TextInput::make('unit_cost')
                            ->label('Costo Unitario')
                            ->numeric()
                            ->required()
                            ->live(onBlur: true)
                            ->afterStateUpdated(function ($get, $set, $state) {
                                $subtotal = $state * ((float) $get('quantity'));
                                $set('subtotal', $subtotal);
                                
                                $lines = $get('../../lines') ?? [];
                                $total = 0;
                                foreach ($lines as $line) {
                                    $total += ((float) ($line['quantity'] ?? 0)) * ((float) ($line['unit_cost'] ?? 0));
                                }
                                $shipping = (float) $get('../../shipping_cost');
                                $discount = (float) $get('../../discount');
                                $set('../../total_amount', $total + $shipping - $discount);
                            })
                            ->columnSpan(1),
                        TextInput::make('subtotal')
                            ->label('Subtotal')
                            ->numeric()
                            ->required()
                            ->readOnly()
                            ->columnSpan(2),
                    ])
                    ->columns(8)
                    ->columnSpanFull()
                    ->required(),
                ])
            ]);
    }
}
