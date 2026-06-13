<?php

namespace App\Observers;

use App\Models\PurchaseOrder;

class PurchaseOrderObserver
{
    public function creating(PurchaseOrder $purchaseOrder): void
    {
        if (empty($purchaseOrder->document_number)) {
            $series = \App\Models\DocumentSeries::where('document_type', 'ORDEN_COMPRA')
                ->where('is_active', true)
                ->first();

            if ($series) {
                $series->increment('current_number');
                $purchaseOrder->document_series = $series->series;
                $purchaseOrder->document_number = str_pad($series->current_number, 6, '0', STR_PAD_LEFT);
                $purchaseOrder->order_number = $purchaseOrder->document_series . '-' . $purchaseOrder->document_number;
            }
        }
    }

    /**
     * Handle the PurchaseOrder "created" event.
     */
    public function created(PurchaseOrder $purchaseOrder): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "updated" event.
     */
    public function updated(PurchaseOrder $purchaseOrder): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "deleted" event.
     */
    public function deleted(PurchaseOrder $purchaseOrder): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "restored" event.
     */
    public function restored(PurchaseOrder $purchaseOrder): void
    {
        //
    }

    /**
     * Handle the PurchaseOrder "force deleted" event.
     */
    public function forceDeleted(PurchaseOrder $purchaseOrder): void
    {
        //
    }
}
