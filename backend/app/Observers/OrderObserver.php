<?php

namespace App\Observers;

use App\Models\Order;
use App\Services\InventoryService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderPaid;
use App\Mail\OrderReceived;
use App\Mail\OrderShipped;
use App\Mail\OrderCompleted;
use App\Mail\OrderCancelled;

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
        \App\Models\OrderNote::create([
            'order_id' => $order->id,
            'user_id' => auth()->id(),
            'content' => "Pedido creado con estado '{$order->status}'.",
            'type' => 'system',
        ]);

        $isPaidOrProcessing = in_array($order->status, ['processing', 'shipped', 'delivered']) || $order->payment_status === 'paid';
        if ($isPaidOrProcessing) {
            $this->deductStock($order);
        }

        // Enviar email solo si la orden se creó ya pagada o en proceso (ej. desde POS o Admin)
        if ($order->shipping_email) {
            try {
                if ($isPaidOrProcessing) {
                    Mail::to($order->shipping_email)->send(new OrderPaid($order));
                    Log::info("Email de pago inicial enviado a {$order->shipping_email} para orden {$order->order_number}");
                }
            } catch (\Throwable $e) {
                Log::error("Failed to send order creation email for order {$order->order_number}: " . $e->getMessage(), [
                    'order_id' => $order->id,
                    'status' => $order->status,
                    'email' => $order->shipping_email,
                    'trace' => $e->getTraceAsString(),
                ]);
            }
        }
    }

    /**
     * Handle the Order "updated" event.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') || $order->wasChanged('payment_status')) {
            $isPaidOrProcessing = in_array($order->status, ['processing', 'shipped', 'delivered']) || $order->payment_status === 'paid';

            if ($order->wasChanged('status')) {
                \App\Models\OrderNote::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'content' => "El estado del pedido cambió a '{$order->status}'.",
                    'type' => 'system',
                ]);
            }

            if ($isPaidOrProcessing && empty($order->document_number)) {
                $this->assignDocumentNumber($order);
                $this->deductStock($order);
            } elseif ($order->status === 'cancelled' && $order->wasChanged('status')) {
                $this->restoreStock($order);
            }

            // Email Notifications for status and payment changes
            if ($order->shipping_email) {
                try {
                    // Verificar si se acaba de pagar o pasar a processing
                    $becamePaid = ($order->wasChanged('payment_status') && $order->payment_status === 'paid')
                                || ($order->wasChanged('status') && $order->status === 'processing');

                    if ($becamePaid) {
                        Mail::to($order->shipping_email)->send(new OrderPaid($order));
                        Log::info("Email de pago/confirmación enviado a {$order->shipping_email} para orden {$order->order_number}");
                    } elseif ($order->wasChanged('status') && $order->status === 'shipped') {
                        Mail::to($order->shipping_email)->send(new OrderShipped($order));
                        Log::info("Email de envío enviado a {$order->shipping_email} para orden {$order->order_number}");
                    } elseif ($order->wasChanged('status') && $order->status === 'delivered') {
                        Mail::to($order->shipping_email)->send(new OrderCompleted($order));
                        Log::info("Email de entrega enviado a {$order->shipping_email} para orden {$order->order_number}");
                    } elseif ($order->wasChanged('status') && $order->status === 'cancelled') {
                        Mail::to($order->shipping_email)->send(new OrderCancelled($order));
                        Log::info("Email de cancelación enviado a {$order->shipping_email} para orden {$order->order_number}");
                    }
                } catch (\Throwable $e) {
                    Log::error("Failed to send order status email for order {$order->order_number}: " . $e->getMessage(), [
                        'order_id' => $order->id,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'email' => $order->shipping_email,
                        'trace' => $e->getTraceAsString(),
                    ]);
                }
            }
        }

        if ($order->wasChanged('payment_status')) {
            \App\Models\OrderNote::create([
                'order_id' => $order->id,
                'user_id' => auth()->id(),
                'content' => "El estado de pago cambió de '" . ($order->getOriginal('payment_status') ?? 'pendiente') . "' a '{$order->payment_status}'.",
                'type' => 'system',
            ]);
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
                $docNumber = str_pad($series->current_number, 6, '0', STR_PAD_LEFT);
                $order->updateQuietly([
                    'document_type' => strtoupper($docType),
                    'document_series' => $series->series,
                    'document_number' => $docNumber
                ]);

                \App\Models\OrderNote::create([
                    'order_id' => $order->id,
                    'user_id' => auth()->id(),
                    'content' => "Documento " . strtoupper($docType) . " {$series->series}-{$docNumber} generado exitosamente.",
                    'type' => 'system',
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
