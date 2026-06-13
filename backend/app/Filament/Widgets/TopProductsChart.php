<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SaleItem;
use Filament\Widgets\Concerns\InteractsWithPageFilters;

class TopProductsChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected ?string $heading = 'Top 5 Productos por Ingresos';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;
        
        $saleItemQuery = SaleItem::query()->whereHas('sale', function($q) {
            $q->where('status', 'CONFIRMED');
        });
        $orderItemQuery = \App\Models\OrderItem::query()->whereHas('order', function($q) {
            $q->where('payment_status', 'paid');
        });

        if ($period === 'week') {
            $saleItemQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $orderItemQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $saleItemQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $orderItemQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $saleItemQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            $orderItemQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $saleItemQuery->whereDate('created_at', '>=', $dateFrom);
                $orderItemQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $saleItemQuery->whereDate('created_at', '<=', $dateTo);
                $orderItemQuery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $saleItems = $saleItemQuery->with('product')->get();
        $orderItems = $orderItemQuery->with('product')->get();
        $allItems = $saleItems->concat($orderItems);

        $topItems = $allItems->groupBy('product_id')->map(function($items) {
            return [
                'name' => $items->first()->product->name ?? $items->first()->product_name ?? 'Desconocido',
                'revenue' => $items->sum('subtotal')
            ];
        })->sortByDesc('revenue')->take(5);

        $labels = [];
        $data = [];
        $colors = ['#4f46e5', '#6366f1', '#818cf8', '#a5b4fc', '#e0e7ff'];

        foreach ($topItems as $item) {
            $labels[] = $item['name'];
            $data[] = $item['revenue'];
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'indexAxis' => 'y',
            'scales' => [
                'x' => [
                    'display' => false,
                ],
                'y' => [
                    'grid' => [
                        'display' => false,
                    ],
                ],
            ],
        ];
    }
}
