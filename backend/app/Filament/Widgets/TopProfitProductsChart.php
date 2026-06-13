<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class TopProfitProductsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected ?string $heading = 'Top 5 Productos por Utilidad Neta';
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

        $salesData = $saleItemsQuery
            ->select('sale_items.product_id', DB::raw('SUM(sale_items.subtotal) as total_revenue'), DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'))
            ->groupBy('sale_items.product_id')
            ->get();

        $ordersData = $orderItemsQuery
            ->select('order_items.product_id', DB::raw('SUM(order_items.subtotal) as total_revenue'), DB::raw('SUM(order_items.quantity * order_items.unit_cost) as total_cost'))
            ->groupBy('order_items.product_id')
            ->get();

        $productIds = $salesData->pluck('product_id')->merge($ordersData->pluck('product_id'))->unique();
        $products = \App\Models\Product::whereIn('id', $productIds)->pluck('name', 'id');

        $combined = $salesData->concat($ordersData)
            ->groupBy('product_id')
            ->map(function ($items, $productId) use ($products) {
                $revenue = $items->sum('total_revenue');
                $cost = $items->sum('total_cost');
                return [
                    'name' => $products[$productId] ?? 'Producto ' . $productId,
                    'profit' => $revenue - $cost
                ];
            })
            ->sortByDesc('profit')
            ->take(5);

        return [
            'datasets' => [
                [
                    'label' => 'Utilidad Neta (S/)',
                    'data' => $combined->pluck('profit')->values()->toArray(),
                    'backgroundColor' => '#10b981', // Tailwind emerald-500
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $combined->pluck('name')->values()->toArray(),
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }
}
