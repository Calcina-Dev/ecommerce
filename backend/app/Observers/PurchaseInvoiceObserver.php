<?php

namespace App\Observers;

use App\Models\PurchaseInvoice;

class PurchaseInvoiceObserver
{
    public function saved(PurchaseInvoice $purchaseInvoice): void
    {
        if ($purchaseInvoice->status === 'VALID' && $purchaseInvoice->wasChanged('status')) {
            $this->processInventory($purchaseInvoice);
            $this->updatePurchaseOrderStatus($purchaseInvoice->purchase_order_id);
        }

        if ($purchaseInvoice->status === 'CANCELLED' && $purchaseInvoice->wasChanged('status')) {
            $this->reverseInventory($purchaseInvoice);
        }
    }

    protected function processInventory(PurchaseInvoice $purchaseInvoice)
    {
        $warehouseId = $purchaseInvoice->warehouse_id;
        
        $totalSubtotals = $purchaseInvoice->lines->sum('subtotal');
        $shipping = (float) $purchaseInvoice->shipping_cost;
        $discount = (float) $purchaseInvoice->discount;
        
        foreach ($purchaseInvoice->lines as $line) {
            // Prorrateo: distributing shipping and discount based on subtotal weight
            $weight = $totalSubtotals > 0 ? ($line->subtotal / $totalSubtotals) : 0;
            $lineShipping = $shipping * $weight;
            $lineDiscount = $discount * $weight;
            
            $realSubtotal = $line->subtotal + $lineShipping - $lineDiscount;
            $realUnitCost = $line->quantity > 0 ? ($realSubtotal / $line->quantity) : $line->unit_cost;

            // 1. Encontrar o crear el lote con el costo real
            $batch = \App\Models\Batch::firstOrCreate(
                [
                    'product_id' => $line->product_id,
                    'batch_number' => $line->batch_number,
                ],
                [
                    'expiration_date' => $line->expiration_date,
                    'status' => 'active',
                    'unit_cost' => $realUnitCost,
                ]
            );

            // Actualizar el batch_id en la línea
            $line->update(['batch_id' => $batch->id]);

            // 2. Crear Movimiento de Inventario
            \App\Models\StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $line->product_id,
                'batch_id' => $batch->id,
                'user_id' => auth()->id(),
                'type' => 'IN',
                'quantity' => $line->quantity,
                'reason' => 'Recepcion de Compra',
                'reference_type' => PurchaseInvoice::class,
                'reference_id' => $purchaseInvoice->id,
                'notes' => 'Recepción Factura: ' . $purchaseInvoice->document_number,
                'unit_cost' => $realUnitCost,
                'total_cost' => $realSubtotal,
            ]);

            // 3. Actualizar o crear StockBalance
            $balance = \App\Models\StockBalance::firstOrCreate(
                [
                    'warehouse_id' => $warehouseId,
                    'product_id' => $line->product_id,
                    'batch_id' => $batch->id,
                ],
                [
                    'on_hand' => 0,
                ]
            );

            $balance->increment('on_hand', $line->quantity);
            $line->product->increment('stock', $line->quantity);
        }
    }

    protected function reverseInventory(PurchaseInvoice $purchaseInvoice)
    {
        $warehouseId = $purchaseInvoice->warehouse_id;
        
        $totalSubtotals = $purchaseInvoice->lines->sum('subtotal');
        $shipping = (float) $purchaseInvoice->shipping_cost;
        $discount = (float) $purchaseInvoice->discount;

        foreach ($purchaseInvoice->lines as $line) {
            // Check if batch was created
            if (!$line->batch_id) continue;

            $weight = $totalSubtotals > 0 ? ($line->subtotal / $totalSubtotals) : 0;
            $lineShipping = $shipping * $weight;
            $lineDiscount = $discount * $weight;
            
            $realSubtotal = $line->subtotal + $lineShipping - $lineDiscount;
            $realUnitCost = $line->quantity > 0 ? ($realSubtotal / $line->quantity) : $line->unit_cost;

            // 1. Crear Movimiento Inverso (ADJUSTMENT o OUT)
            \App\Models\StockMovement::create([
                'warehouse_id' => $warehouseId,
                'product_id' => $line->product_id,
                'batch_id' => $line->batch_id,
                'user_id' => auth()->id(),
                'type' => 'OUT',
                'quantity' => abs($line->quantity),
                'reason' => 'Anulación de Recepción',
                'reference_type' => PurchaseInvoice::class,
                'reference_id' => $purchaseInvoice->id,
                'notes' => 'Anulación Factura: ' . $purchaseInvoice->document_number,
                'unit_cost' => $realUnitCost,
                'total_cost' => -$realSubtotal,
            ]);

            // 2. Descontar del StockBalance
            $balance = \App\Models\StockBalance::where([
                'warehouse_id' => $warehouseId,
                'product_id' => $line->product_id,
                'batch_id' => $line->batch_id,
            ])->first();

            if ($balance) {
                $balance->decrement('on_hand', abs($line->quantity));
            }
            $line->product->decrement('stock', abs($line->quantity));
        }

        $this->updatePurchaseOrderStatus($purchaseInvoice->purchase_order_id);
    }

    protected function updatePurchaseOrderStatus($purchaseOrderId)
    {
        if (!$purchaseOrderId) return;
        
        $order = \App\Models\PurchaseOrder::with('items')->find($purchaseOrderId);
        if (!$order) return;

        $receivedQuantities = \Illuminate\Support\Facades\DB::table('purchase_invoice_lines')
            ->join('purchase_invoices', 'purchase_invoice_lines.purchase_invoice_id', '=', 'purchase_invoices.id')
            ->where('purchase_invoices.purchase_order_id', $purchaseOrderId)
            ->where('purchase_invoices.status', 'VALID')
            ->groupBy('purchase_invoice_lines.product_id')
            ->select('purchase_invoice_lines.product_id', \Illuminate\Support\Facades\DB::raw('SUM(purchase_invoice_lines.quantity) as total_received'))
            ->pluck('total_received', 'product_id');

        $isCompleted = true;
        $hasAnyReceived = false;

        foreach ($order->items as $item) {
            $received = $receivedQuantities->get($item->product_id, 0);
            if ($received > 0) {
                $hasAnyReceived = true;
            }
            if ($received < $item->quantity) {
                $isCompleted = false;
            }
        }

        if ($isCompleted) {
            $order->update(['status' => 'completed']);
        } elseif ($hasAnyReceived) {
            $order->update(['status' => 'partial']);
        } else {
            $order->update(['status' => 'sent']);
        }
    }
}
