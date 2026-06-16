<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Sale;
use App\Models\Customer;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class CustomerRetentionChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected ?string $heading = 'Retención: Nuevos vs Recurrentes';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $saleQuery = Sale::query()->where('status', 'CONFIRMED')->whereNotNull('customer_id');
        $orderQuery = \App\Models\Order::query()->where('payment_status', 'paid')->whereNotNull('user_id');

        if ($period === 'week') {
            $saleQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $orderQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $saleQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $orderQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $saleQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            $orderQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $saleQuery->whereDate('created_at', '>=', $dateFrom);
                $orderQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $saleQuery->whereDate('created_at', '<=', $dateTo);
                $orderQuery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $sales = $saleQuery->get();
        $orders = $orderQuery->get();
        
        $allTx = $sales->map(function ($sale) {
            return (object) ['client_id' => $sale->customer_id, 'is_sale' => true];
        })->concat($orders->map(function ($order) {
            return (object) ['client_id' => $order->user_id, 'is_sale' => false];
        }));
        
        $newCustomers = 0;
        $returningCustomers = 0;

        foreach ($allTx->groupBy('client_id') as $clientId => $txs) {
            // Count total distinct purchases of this client up to now
            $pastPurchases = Sale::where('customer_id', $clientId)->where('status', 'CONFIRMED')->count() 
                           + \App\Models\Order::where('user_id', $clientId)->where('payment_status', 'paid')->count();
            
            // Simplified logic: If total purchases <= count in this period, they are new
            if ($pastPurchases > count($txs)) {
                $returningCustomers += count($txs);
            } else {
                $newCustomers += 1;
                $returningCustomers += count($txs) - 1; 
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ventas',
                    'data' => [$newCustomers, $returningCustomers],
                    'backgroundColor' => ['#6366f1', '#10b981'],
                ],
            ],
            'labels' => ['Clientes Nuevos', 'Clientes Recurrentes'],
        ];
    }

    protected function getType(): string
    {
        return 'doughnut';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1500,
                'easing' => 'easeOutQuart',
            ],
        ];
    }
}
