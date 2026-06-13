<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class TopCategoriesChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 6;
    protected ?string $heading = 'Rendimiento por Categoría (Ingresos)';
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
            ->select('products.category_id', DB::raw('SUM(sale_items.subtotal) as total_revenue'))
            ->groupBy('products.category_id')
            ->get();

        $ordersData = $orderItemsQuery
            ->select('products.category_id', DB::raw('SUM(order_items.subtotal) as total_revenue'))
            ->groupBy('products.category_id')
            ->get();

        $categoryIds = $salesData->pluck('category_id')->merge($ordersData->pluck('category_id'))->filter()->unique();
        $categories = \App\Models\Category::whereIn('id', $categoryIds)->pluck('name', 'id');

        $combined = $salesData->concat($ordersData)
            ->filter(fn($item) => !is_null($item->category_id))
            ->groupBy('category_id')
            ->map(function ($items, $categoryId) use ($categories) {
                return [
                    'name' => $categories[$categoryId] ?? 'Categoría ' . $categoryId,
                    'revenue' => $items->sum('total_revenue')
                ];
            })
            ->sortByDesc('revenue')
            ->take(5);

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos Netos (S/)',
                    'data' => $combined->pluck('revenue')->values()->toArray(),
                    'backgroundColor' => '#ec4899', // pink-500
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
