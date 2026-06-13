<?php

namespace App\Filament\Resources\PurchaseOrders\Pages;

use App\Filament\Resources\PurchaseOrders\PurchaseOrderResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewPurchaseOrder extends ViewRecord
{
    protected static string $resource = PurchaseOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\Action::make('generate_invoice')
                ->label('Generar Recepción (Factura)')
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
                        'total_amount' => 0, // We will calculate this based on remaining lines
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

                    \Filament\Notifications\Notification::make()->title('Factura en Borrador creada con cantidades pendientes')->success()->send();
                    
                    return redirect(\App\Filament\Resources\PurchaseInvoices\PurchaseInvoiceResource::getUrl('edit', ['record' => $invoice]));
                }),
            EditAction::make()
                ->visible(fn (\App\Models\PurchaseOrder $record) => !in_array($record->status, ['cancelled', 'completed', 'partial'])),
        ];
    }

    public function getFooter(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.components.traceability-map', ['record' => $this->getRecord()]);
    }
}
