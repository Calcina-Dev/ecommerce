<?php

namespace App\Observers;

use App\Models\WarehouseTransfer;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\Batch;

class WarehouseTransferObserver
{
    public function creating(WarehouseTransfer $warehouseTransfer): void
    {
        if (empty($warehouseTransfer->document_number)) {
            $series = \App\Models\DocumentSeries::where('document_type', 'TRANSFERENCIA')
                ->where('is_active', true)
                ->first();

            if ($series) {
                $series->increment('current_number');
                $warehouseTransfer->document_series = $series->series;
                $warehouseTransfer->document_number = str_pad($series->current_number, 6, '0', STR_PAD_LEFT);
                $warehouseTransfer->reference_number = $warehouseTransfer->document_series . '-' . $warehouseTransfer->document_number;
            }
        }
    }

    public function saved(WarehouseTransfer $warehouseTransfer): void
    {
        if ($warehouseTransfer->status === 'COMPLETED' && $warehouseTransfer->wasChanged('status')) {
            $this->processTransfer($warehouseTransfer);
        }
    }

    protected function processTransfer(WarehouseTransfer $warehouseTransfer)
    {
        $fromWarehouse = $warehouseTransfer->from_warehouse_id;
        $toWarehouse = $warehouseTransfer->to_warehouse_id;

        foreach ($warehouseTransfer->items as $item) {
            $remainingQty = $item->quantity;

            // FEFO: Find oldest expiring batches with stock in the source warehouse
            $balances = StockBalance::where('stock_balances.warehouse_id', $fromWarehouse)
                ->where('stock_balances.product_id', $item->product_id)
                ->where('stock_balances.on_hand', '>', 0)
                ->join('batches', 'stock_balances.batch_id', '=', 'batches.id')
                ->orderBy('batches.expiration_date', 'asc')
                ->select('stock_balances.*', 'batches.unit_cost')
                ->get();

            foreach ($balances as $balance) {
                if ($remainingQty <= 0) break;

                $takeQty = min($remainingQty, $balance->on_hand);

                // 1. OUT from source warehouse
                StockMovement::create([
                    'warehouse_id' => $fromWarehouse,
                    'product_id' => $item->product_id,
                    'batch_id' => $balance->batch_id,
                    'user_id' => auth()->id(),
                    'type' => 'OUT',
                    'quantity' => $takeQty,
                    'reason' => 'Transferencia Salida',
                    'reference_type' => WarehouseTransfer::class,
                    'reference_id' => $warehouseTransfer->id,
                    'notes' => 'Salida por Transferencia: ' . $warehouseTransfer->reference_number,
                    'unit_cost' => $balance->unit_cost,
                    'total_cost' => -($takeQty * $balance->unit_cost),
                ]);
                $balance->decrement('on_hand', $takeQty);

                // 2. IN to destination warehouse
                StockMovement::create([
                    'warehouse_id' => $toWarehouse,
                    'product_id' => $item->product_id,
                    'batch_id' => $balance->batch_id,
                    'user_id' => auth()->id(),
                    'type' => 'IN',
                    'quantity' => $takeQty,
                    'reason' => 'Transferencia Ingreso',
                    'reference_type' => WarehouseTransfer::class,
                    'reference_id' => $warehouseTransfer->id,
                    'notes' => 'Ingreso por Transferencia: ' . $warehouseTransfer->reference_number,
                    'unit_cost' => $balance->unit_cost,
                    'total_cost' => ($takeQty * $balance->unit_cost),
                ]);

                $destBalance = StockBalance::firstOrCreate(
                    [
                        'warehouse_id' => $toWarehouse,
                        'product_id' => $item->product_id,
                        'batch_id' => $balance->batch_id,
                    ],
                    ['on_hand' => 0]
                );
                $destBalance->increment('on_hand', $takeQty);

                $remainingQty -= $takeQty;
            }
            
            // If $remainingQty > 0 after the loop, it means there was not enough stock.
            // In a real system we would throw an exception or handle negative stock.
            // For now, we assume the transfer was validated before completion.
        }
    }
}
