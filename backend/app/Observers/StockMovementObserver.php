<?php

namespace App\Observers;

use App\Models\StockMovement;

class StockMovementObserver
{
    public function creating(StockMovement $stockMovement): void
    {
        if (empty($stockMovement->document_number)) {
            $docType = $stockMovement->type === 'IN' ? 'NOTA_INGRESO' : 'NOTA_SALIDA';
            
            $series = \App\Models\DocumentSeries::where('document_type', $docType)
                ->where('is_active', true)
                ->first();

            if ($series) {
                $series->increment('current_number');
                $stockMovement->document_series = $series->series;
                $stockMovement->document_number = str_pad($series->current_number, 6, '0', STR_PAD_LEFT);
            }
        }
    }

    /**
     * Handle the StockMovement "created" event.
     */
    public function created(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "updated" event.
     */
    public function updated(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "deleted" event.
     */
    public function deleted(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "restored" event.
     */
    public function restored(StockMovement $stockMovement): void
    {
        //
    }

    /**
     * Handle the StockMovement "force deleted" event.
     */
    public function forceDeleted(StockMovement $stockMovement): void
    {
        //
    }
}
