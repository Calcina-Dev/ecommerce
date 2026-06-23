<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InventoryService;

class OrderObserver
{
    /**
     * Handle the Order "saving" event.
     */
    public function saving(Order $order): void
    {
        if ($order->isDirty('status')) {
            $status = $order->status;
            if (in_array($status, ['processing', 'shipped', 'delivered', 'cancelled'])) {
                $column = "{$status}_at";
                if (!$order->{$column}) {
                    $order->{$column} = now();
                }
            }
        }
    }

    /**
     * Handle the Order "created" event.
     */
    public function created(Order $order): void
    {
        if (in_array($order->status, ['shipped', 'delivered'])) {
            $this->deductStock($order);
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->isDirty('status')) {
            if (in_array($order->status, ['shipped', 'delivered']) && !in_array($order->getOriginal('status'), ['shipped', 'delivered'])) {
                $this->assignDocumentNumber($order);
                $this->deductStock($order);
                
                if ($order->status === 'shipped' && $order->shipping_email) {
                    \Illuminate\Support\Facades\Mail::to($order->shipping_email)->send(new \App\Mail\OrderShipped($order));
                }
            } elseif ($order->status === 'cancelled' && in_array($order->getOriginal('status'), ['shipped', 'delivered'])) {
                $this->restoreStock($order);
            }
        }
    }

    private function assignDocumentNumber(Order $order): void
    {
        if (empty($order->document_number)) {
            $docType = $order->document_type ?? 'BOLETA';
            
            $series = \App\Models\DocumentSeries::where('document_type', strtoupper($docType))
                ->where('is_active', true)
                ->first();

            if ($series) {
                $series->increment('current_number');
                $order->updateQuietly([
                    'document_type' => strtoupper($docType),
                    'document_series' => $series->series,
                    'document_number' => str_pad($series->current_number, 6, '0', STR_PAD_LEFT)
                ]);
            }
        }
    }

    private function deductStock(Order $order): void
    {
        $inventoryService = new InventoryService();
        $totalCost = $inventoryService->deductStockFEFO(
            $order->items,
            \App\Models\Warehouse::first()?->id ?? 1,
            'ORDER',
            $order->id,
            $order->order_number ?? (string)$order->id,
            auth()->id() ?? $order->user_id
        );

        // Guardar el costo total en la orden
        $order->updateQuietly(['total_cost' => $totalCost]);
    }

    private function restoreStock(Order $order): void
    {
        $inventoryService = new InventoryService();
        $inventoryService->restoreStock(
            'ORDER',
            $order->id,
            $order->order_number ?? (string)$order->id,
            auth()->id() ?? $order->user_id
        );
    }
}
