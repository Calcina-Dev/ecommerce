<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;

use App\Models\Sale;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class SalesTrendChart extends ChartWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected ?string $heading = 'Tendencia de Ingresos vs Margen';
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;
        
        $saleQuery = Sale::query()->where('status', 'CONFIRMED');
        $orderQuery = \App\Models\Order::query()->where('payment_status', 'paid');

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

        $allTransactions = $sales->concat($orders);

        // Group by day for week/month, by month for year
        $grouped = $allTransactions->groupBy(function($tx) use ($period) {
            return $period === 'year' 
                ? Carbon::parse($tx->created_at)->format('M')
                : Carbon::parse($tx->created_at)->format('d M');
        });

        $labels = [];
        $ingresos = [];
        $utilidad = [];

        foreach ($grouped as $date => $dayTx) {
            $labels[] = $date;
            $ingresos[] = $dayTx->sum('total_amount');
            $utilidad[] = $dayTx->sum('total_amount') - $dayTx->sum('total_cost');
        }

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos',
                    'data' => $ingresos,
                    'borderColor' => '#4f46e5',
                    'backgroundColor' => 'rgba(79, 70, 229, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
                [
                    'label' => 'Utilidad',
                    'data' => $utilidad,
                    'borderColor' => '#10b981',
                    'backgroundColor' => 'rgba(16, 185, 129, 0.1)',
                    'fill' => true,
                    'tension' => 0.4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }
}
