<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfitTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected ?string $heading = 'Tendencia de Utilidad Neta';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $saleItemsQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->where('sales.status', 'CONFIRMED');

        $orderItemsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.payment_status', 'paid');

        if ($period === 'week') {
            $saleItemsQuery->whereBetween('sales.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $orderItemsQuery->whereBetween('orders.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $saleItemsQuery->whereBetween('sales.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $orderItemsQuery->whereBetween('orders.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $saleItemsQuery->whereBetween('sales.created_at', [now()->startOfYear(), now()->endOfYear()]);
            $orderItemsQuery->whereBetween('orders.created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $saleItemsQuery->whereDate('sales.created_at', '>=', $dateFrom);
                $orderItemsQuery->whereDate('orders.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $saleItemsQuery->whereDate('sales.created_at', '<=', $dateTo);
                $orderItemsQuery->whereDate('orders.created_at', '<=', $dateTo);
            }
        }

        $format = match ($period) {
            'year' => 'YYYY-MM',
            'week' => 'YYYY-MM-DD',
            'custom' => 'YYYY-MM-DD',
            default => 'YYYY-MM-DD',
        };

        $salesData = $saleItemsQuery
            ->select(DB::raw("TO_CHAR(sales.created_at, '{$format}') as date"), DB::raw('SUM(sale_items.subtotal) as total_revenue'), DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'))
            ->groupBy('date')
            ->get();

        $ordersData = $orderItemsQuery
            ->select(DB::raw("TO_CHAR(orders.created_at, '{$format}') as date"), DB::raw('SUM(order_items.subtotal) as total_revenue'), DB::raw('SUM(order_items.quantity * order_items.unit_cost) as total_cost'))
            ->groupBy('date')
            ->get();

        $combined = $salesData->concat($ordersData)
            ->groupBy('date')
            ->map(function ($items) {
                $revenue = $items->sum('total_revenue');
                $cost = $items->sum('total_cost');
                return [
                    'date' => $items->first()->date,
                    'profit' => $revenue - $cost
                ];
            })
            ->sortBy('date');

        return [
            'datasets' => [
                [
                    'label' => 'Utilidad Neta (S/)',
                    'data' => $combined->pluck('profit')->values()->toArray(),
                    'borderColor' => '#3b82f6', // Tailwind blue-500
                    'backgroundColor' => 'rgba(59, 130, 246, 0.2)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $combined->pluck('date')->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
