<?php

namespace App\Services;

use Illuminate\Database\Eloquent\Model;
use App\Models\Order;
use App\Models\Sale;
use App\Models\StockMovement;
use App\Models\PurchaseOrder;

class TraceabilityService
{
    /**
     * Devuelve un array cronológico de nodos representando el ciclo de vida de una transacción.
     */
    public function getTraceabilityNodes(Model $record): array
    {
        if ($record instanceof Order) {
            return $this->buildOrderNetwork($record);
        } elseif ($record instanceof Sale) {
            return $this->buildSaleNetwork($record);
        } elseif ($record instanceof StockMovement) {
            return $this->buildStockMovementNetwork($record);
        } elseif ($record instanceof PurchaseOrder) {
            return $this->buildPurchaseNetwork($record);
        } elseif ($record instanceof \App\Models\PurchaseInvoice) {
            return $this->buildInvoiceNetwork($record);
        }

        return ['nodes' => [], 'edges' => []];
    }

    private function buildInvoiceNetwork(\App\Models\PurchaseInvoice $invoice): array
    {
        if ($invoice->purchase_order_id) {
            $purchase = PurchaseOrder::find($invoice->purchase_order_id);
            if ($purchase) return $this->buildPurchaseNetwork($purchase);
        }

        $nodes = [];
        $edges = [];
        $invId = 'inv-' . $invoice->id;
        $nodes[] = $this->formatNode(
            $invId, 
            "<b>Recepción (Factura)</b>\n" . $invoice->document_number . "\nS/ " . number_format($invoice->total_amount, 2), 
            'Estado: ' . $invoice->status, 
            '#8b5cf6', 
            ''
        );
        $movs = StockMovement::where('reference_type', \App\Models\PurchaseInvoice::class)->where('reference_id', $invoice->id)->get();
        foreach ($movs as $mov) {
            $movId = 'mov-' . $mov->id;
            $kardexCode = $this->getKardexCode($mov);
            $nodes[] = $this->formatNode(
                $movId, 
                "<b>Ingreso Kardex " . ($kardexCode ? "($kardexCode)" : "") . "</b>\n" . ($mov->product ? $mov->product->name : 'Prod') . "\nCant: " . $mov->quantity, 
                'Lote: ' . ($mov->batch ? $mov->batch->batch_number : 'N/A'), 
                '#22c55e', 
                ''
            );
            $edges[] = ['from' => $invId, 'to' => $movId, 'arrows' => 'to', 'color' => '#9ca3af'];
        }
        return ['nodes' => $nodes, 'edges' => $edges];
    }

    private function formatNode(string $id, string $title, string $description, string $colorHex, string $iconCode): array
    {
        return [
            'id' => $id,
            'label' => $title,
            'title' => $description, // Tooltip
            'shape' => 'box',
            'color' => [
                'background' => '#ffffff',
                'border' => $colorHex,
                'highlight' => [
                    'background' => '#f9fafb',
                    'border' => $colorHex,
                ],
            ],
            'font' => [
                'multi' => 'html',
                'color' => '#111827',
                'size' => 14,
            ],
            'borderWidth' => 2,
            'shadow' => true,
            'margin' => 10,
        ];
    }

    private function getKardexCode(StockMovement $movement): string
    {
        return ($movement->document_series ? $movement->document_series . '-' : '') . $movement->document_number;
    }

    private function buildOrderNetwork(Order $order): array
    {
        $nodes = [];
        $edges = [];

        $orderId = 'order-' . $order->id;
        $nodes[] = $this->formatNode(
            $orderId, 
            "<b>Orden Web</b>\n" . $order->order_number . ($order->total_amount ? "\nS/ " . number_format($order->total_amount, 2) : ''), 
            'Fecha: ' . $order->created_at->format('d/m/Y H:i'), 
            '#3b82f6', 
            ''
        );

        if ($order->payment_method || strtolower($order->payment_status) === 'paid') {
            $payId = 'pay-' . $order->id;
            $nodes[] = $this->formatNode(
                $payId, 
                "<b>Pago Web</b>\n" . ($order->payment_method ?: 'Medio Digital') . "\nS/ " . number_format($order->total_amount, 2), 
                'Estado: ' . ucfirst($order->payment_status ?? 'Pagado'), 
                '#22c55e', 
                ''
            );
            $edges[] = ['from' => $orderId, 'to' => $payId, 'arrows' => 'to', 'color' => '#9ca3af'];
        }

        if ($order->document_number) {
            $docId = 'doc-' . $order->id;
            $nodes[] = $this->formatNode(
                $docId, 
                "<b>" . ($order->document_type ?? 'COMPROBANTE') . "</b>\n" . $order->document_series . '-' . $order->document_number, 
                'Generado automáticamente', 
                '#22c55e', 
                ''
            );
            $edges[] = ['from' => $orderId, 'to' => $docId, 'arrows' => 'to', 'color' => '#9ca3af'];
        }

        $movements = StockMovement::where('reference_type', 'ORDER')->where('reference_id', $order->id)->get();
        foreach ($movements as $movement) {
            $movId = 'mov-' . $movement->id;
            $kardexCode = $this->getKardexCode($movement);
            $nodes[] = $this->formatNode(
                $movId, 
                "<b>Salida Kardex " . ($kardexCode ? "($kardexCode)" : "") . "</b>\n" . ($movement->product ? $movement->product->name : 'Prod') . "\nCant: " . $movement->quantity, 
                'Lote: ' . ($movement->batch ? $movement->batch->batch_number : 'N/A'), 
                '#f97316', 
                ''
            );
            $edges[] = ['from' => $orderId, 'to' => $movId, 'arrows' => 'to', 'color' => '#9ca3af'];
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }

    private function buildSaleNetwork(Sale $sale): array
    {
        $nodes = [];
        $edges = [];

        $saleId = 'sale-' . $sale->id;
        $nodes[] = $this->formatNode(
            $saleId, 
            "<b>Venta POS</b>\n" . ($sale->document_series ? "{$sale->document_series}-{$sale->document_number}" : "#{$sale->id}") . "\nS/ " . number_format($sale->total_amount, 2), 
            'Fecha: ' . $sale->created_at->format('d/m/Y H:i'), 
            '#3b82f6', 
            ''
        );

        foreach ($sale->payments ?? [] as $payment) {
            $payId = 'pay-' . $payment->id;
            $nodes[] = $this->formatNode(
                $payId, 
                "<b>Pago Recibido</b>\n" . ($payment->paymentMethod ? $payment->paymentMethod->name : 'N/A') . "\nS/ " . number_format($payment->amount, 2), 
                'Pago de Venta', 
                '#22c55e', 
                ''
            );
            $edges[] = ['from' => $saleId, 'to' => $payId, 'arrows' => 'to', 'color' => '#9ca3af'];
        }

        $movements = StockMovement::where('reference_type', 'SALE')->where('reference_id', $sale->id)->get();
        foreach ($movements as $movement) {
            $movId = 'mov-' . $movement->id;
            $kardexCode = $this->getKardexCode($movement);
            $nodes[] = $this->formatNode(
                $movId, 
                "<b>Salida Kardex " . ($kardexCode ? "($kardexCode)" : "") . "</b>\n" . ($movement->product ? $movement->product->name : 'Prod') . "\nCant: " . $movement->quantity, 
                'Lote: ' . ($movement->batch ? $movement->batch->batch_number : 'N/A'), 
                '#f97316', 
                ''
            );
            $edges[] = ['from' => $saleId, 'to' => $movId, 'arrows' => 'to', 'color' => '#9ca3af'];
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }

    private function buildStockMovementNetwork(StockMovement $movement): array
    {
        if ($movement->reference_type === 'ORDER') {
            $order = Order::find($movement->reference_id);
            if ($order) return $this->buildOrderNetwork($order);
        } elseif ($movement->reference_type === 'SALE') {
            $sale = Sale::find($movement->reference_id);
            if ($sale) return $this->buildSaleNetwork($sale);
        } elseif ($movement->reference_type === \App\Models\PurchaseInvoice::class || $movement->reference_type === 'PURCHASE') {
            $invoice = \App\Models\PurchaseInvoice::find($movement->reference_id);
            if ($invoice) return $this->buildInvoiceNetwork($invoice);
        }

        $nodes = [];
        $kardexCode = $this->getKardexCode($movement);
        $nodes[] = $this->formatNode(
            'mov-' . $movement->id, 
            "<b>Mov. Kardex " . ($kardexCode ? "($kardexCode)" : "") . "</b>\n" . $movement->type, 
            'Motivo: ' . $movement->reason, 
            $movement->type === 'IN' ? '#22c55e' : '#f97316', 
            ''
        );

        return ['nodes' => $nodes, 'edges' => []];
    }

    private function buildPurchaseNetwork(PurchaseOrder $order): array
    {
        $nodes = [];
        $edges = [];

        $poId = 'po-' . $order->id;
        $nodes[] = $this->formatNode(
            $poId, 
            "<b>Orden de Compra</b>\n" . $order->order_number . "\nS/ " . number_format($order->total_amount, 2), 
            'Proveedor: ' . ($order->supplier ? $order->supplier->name : 'N/A'), 
            '#3b82f6', 
            ''
        );

        $invoices = \App\Models\PurchaseInvoice::where('purchase_order_id', $order->id)->get();
        foreach ($invoices as $invoice) {
            $invId = 'inv-' . $invoice->id;
            $nodes[] = $this->formatNode(
                $invId, 
                "<b>Recepción (Factura)</b>\n" . $invoice->document_number . "\nS/ " . number_format($invoice->total_amount, 2), 
                'Estado: ' . $invoice->status, 
                '#8b5cf6', 
                ''
            );
            $edges[] = ['from' => $poId, 'to' => $invId, 'arrows' => 'to', 'color' => '#9ca3af'];

            $movements = StockMovement::where('reference_type', \App\Models\PurchaseInvoice::class)->where('reference_id', $invoice->id)->get();
            foreach ($movements as $movement) {
                $movId = 'mov-' . $movement->id;
                $kardexCode = $this->getKardexCode($movement);
                $nodes[] = $this->formatNode(
                    $movId, 
                    "<b>Ingreso Kardex " . ($kardexCode ? "($kardexCode)" : "") . "</b>\n" . ($movement->product ? $movement->product->name : 'Prod') . "\nCant: " . $movement->quantity, 
                    'Lote: ' . ($movement->batch ? $movement->batch->batch_number : 'N/A'), 
                    '#22c55e', 
                    ''
                );
                $edges[] = ['from' => $invId, 'to' => $movId, 'arrows' => 'to', 'color' => '#9ca3af'];
            }
        }

        return ['nodes' => $nodes, 'edges' => $edges];
    }
}
