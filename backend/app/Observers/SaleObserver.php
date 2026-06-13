<?php

namespace App\Observers;

use App\Models\Sale;

class SaleObserver
{
    public function creating(Sale $sale): void
    {
        if (empty($sale->document_number) && !empty($sale->document_type)) {
            $series = \App\Models\DocumentSeries::where('document_type', strtoupper($sale->document_type))
                ->where('is_active', true)
                ->first();

            if ($series) {
                $series->increment('current_number');
                $sale->document_series = $series->series;
                $sale->document_number = str_pad($series->current_number, 6, '0', STR_PAD_LEFT);
            }
        }
    }

    /**
     * Handle the Sale "created" event.
     */
    public function created(Sale $sale): void
    {
        // Stock deduction is now handled in PosPage after items are created.
    }

    public function updated(Sale $sale): void
    {
        if ($sale->isDirty('status')) {
            if ($sale->status === 'CONFIRMED') {
                $this->deductStock($sale);
            } elseif ($sale->status === 'CANCELLED') {
                $this->restoreStock($sale);
            }
        }
    }

    private function deductStock(Sale $sale): void
    {
        $inventoryService = new \App\Services\InventoryService();
        $totalCost = $inventoryService->deductStockFEFO(
            $sale->items,
            $sale->warehouse_id ?? \App\Models\Warehouse::first()?->id ?? 1,
            'SALE',
            $sale->id,
            $sale->document_number ?? (string)$sale->id,
            $sale->user_id
        );

        // Guardar el costo total en la venta
        $sale->updateQuietly(['total_cost' => $totalCost]);
    }

    private function restoreStock(Sale $sale): void
    {
        $inventoryService = new \App\Services\InventoryService();
        $inventoryService->restoreStock(
            'SALE',
            $sale->id,
            $sale->document_number ?? (string)$sale->id,
            $sale->user_id
        );
    }
}
