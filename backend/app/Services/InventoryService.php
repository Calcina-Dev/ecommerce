<?php

namespace App\Services;

use App\Models\StockBalance;
use App\Models\StockMovement;

class InventoryService
{
    /**
     * Deduct stock using FEFO strategy and calculate total cost.
     * Returns the total cost of the deducted stock.
     * 
     * @param iterable $items Elements must have `product_id` and `quantity` properties/methods, and must be updateable if needed.
     * @param int $warehouseId
     * @param string $referenceType e.g., 'SALE', 'ORDER'
     * @param int $referenceId
     * @param string $referenceNumber e.g., document_number, order_number
     * @param int|null $userId
     * @return float The total cost of all deducted items.
     * @throws \Exception If there is not enough stock.
     */
    public function deductStockFEFO(iterable $items, int $warehouseId, string $referenceType, int $referenceId, string $referenceNumber, ?int $userId = null): float
    {
        $totalCost = 0;

        foreach ($items as $item) {
            $qtyToDeduct = $item->quantity;
            $itemTotalCost = 0;

            // FEFO Algorithm: Get available batches ordered by expiration_date ASC
            $balances = StockBalance::where('warehouse_id', $warehouseId)
                ->whereHas('batch', function ($q) use ($item) {
                    $q->where('product_id', $item->product_id);
                })
                ->where('on_hand', '>', 0)
                ->join('batches', 'stock_balances.batch_id', '=', 'batches.id')
                ->orderBy('batches.expiration_date', 'asc')
                ->select('stock_balances.*', 'batches.unit_cost as batch_unit_cost')
                ->get();

            foreach ($balances as $balance) {
                if ($qtyToDeduct <= 0) break;

                $deducted = min($balance->on_hand, $qtyToDeduct);
                $balance->decrement('on_hand', $deducted);
                $qtyToDeduct -= $deducted;

                $movementCost = $deducted * ($balance->batch_unit_cost ?? 0);
                $itemTotalCost += $movementCost;

                // Register Stock Movement
                StockMovement::create([
                    'warehouse_id' => $warehouseId,
                    'product_id' => $item->product_id,
                    'batch_id' => $balance->batch_id,
                    'user_id' => $userId ?? auth()->id() ?? 1,
                    'type' => 'OUT',
                    'quantity' => $deducted,
                    'unit_cost' => $balance->batch_unit_cost ?? 0,
                    'total_cost' => $movementCost,
                    'reference_type' => $referenceType,
                    'reference_id' => $referenceId,
                    'reason' => match ($referenceType) {
                        'ORDER' => 'Venta Web',
                        'SALE' => 'Venta POS',
                        'PURCHASE' => 'Compra',
                        default => 'Movimiento',
                    },
                    'notes' => 'Salida por ' . $referenceType . ' ' . $referenceNumber,
                ]);
            }

            if ($qtyToDeduct > 0) {
                throw new \Exception("Stock insuficiente para el producto ID: {$item->product_id}. Faltan {$qtyToDeduct} unidades.");
            }

            // Actualizar stock denormalizado del producto
            $product = \App\Models\Product::find($item->product_id);
            if ($product) {
                $product->decrement('stock', $item->quantity);
            }

            // Guardar el costo unitario promedio en el item
            $item->update([
                'unit_cost' => $item->quantity > 0 ? ($itemTotalCost / $item->quantity) : 0,
            ]);

            $totalCost += $itemTotalCost;
        }

        return $totalCost;
    }

    /**
     * Restore stock when a sale or order is cancelled.
     * 
     * @param string $referenceType e.g., 'SALE', 'ORDER'
     * @param int $referenceId
     * @param string $referenceNumber
     * @param int|null $userId
     */
    public function restoreStock(string $referenceType, int $referenceId, string $referenceNumber, ?int $userId = null): void
    {
        $movements = StockMovement::where('reference_type', $referenceType)
            ->where('reference_id', $referenceId)
            ->where('type', 'OUT')
            ->get();

        foreach ($movements as $mov) {
            $balance = StockBalance::firstOrCreate([
                'warehouse_id' => $mov->warehouse_id,
                'batch_id' => $mov->batch_id,
            ], [
                'on_hand' => 0,
            ]);

            $balance->increment('on_hand', $mov->quantity);

            StockMovement::create([
                'warehouse_id' => $mov->warehouse_id,
                'product_id' => $mov->product_id,
                'batch_id' => $mov->batch_id,
                'user_id' => $userId ?? auth()->id() ?? 1,
                'type' => 'IN',
                'quantity' => $mov->quantity,
                'unit_cost' => $mov->unit_cost,
                'total_cost' => $mov->total_cost,
                'reference_type' => $referenceType . '_CANCEL',
                'reference_id' => $referenceId,
                'notes' => 'Reingreso por Anulación de ' . $referenceType . ' ' . $referenceNumber,
            ]);

            // Actualizar stock denormalizado del producto
            $product = \App\Models\Product::find($mov->product_id);
            if ($product) {
                $product->increment('stock', $mov->quantity);
            }
        }
    }
}
