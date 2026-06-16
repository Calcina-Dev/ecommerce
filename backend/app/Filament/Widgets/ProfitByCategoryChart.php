<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ProfitByCategoryChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected ?string $heading = 'Rentabilidad por Categoría';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $saleItemsQuery = DB::table('sale_items')
            ->join('sales', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->where('sales.status', 'CONFIRMED');

        $orderItemsQuery = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('products', 'order_items.product_id', '=', 'products.id')
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
            ->select('products.category_id', DB::raw('SUM(sale_items.subtotal) as total_revenue'), DB::raw('SUM(sale_items.quantity * sale_items.unit_cost) as total_cost'))
            ->groupBy('products.category_id')
            ->get();

        $ordersData = $orderItemsQuery
            ->select('products.category_id', DB::raw('SUM(order_items.subtotal) as total_revenue'), DB::raw('SUM(order_items.quantity * order_items.unit_cost) as total_cost'))
            ->groupBy('products.category_id')
            ->get();

        $categoryIds = $salesData->pluck('category_id')->merge($ordersData->pluck('category_id'))->filter()->unique();
        $categories = \App\Models\Category::whereIn('id', $categoryIds)->pluck('name', 'id');

        $combined = $salesData->concat($ordersData)
            ->filter(fn($item) => !is_null($item->category_id))
            ->groupBy('category_id')
            ->map(function ($items, $categoryId) use ($categories) {
                $revenue = $items->sum('total_revenue');
                $cost = $items->sum('total_cost');
                return [
                    'name' => $categories[$categoryId] ?? 'Categoría ' . $categoryId,
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
                    'backgroundColor' => ['#4f46e5', '#ec4899', '#10b981', '#f59e0b', '#8b5cf6'],
                    'hoverOffset' => 4
                ],
            ],
            'labels' => $combined->pluck('name')->values()->toArray(),
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
