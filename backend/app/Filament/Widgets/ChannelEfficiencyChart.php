<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ChannelEfficiencyChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 5;
    protected ?string $heading = 'Eficiencia por Canal (Ingresos)';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $saleQuery = DB::table('sales')->where('status', 'CONFIRMED');
        $orderQuery = DB::table('orders')->where('payment_status', 'paid');

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

        $posRevenue = (float) $saleQuery->sum('total_amount');
        $webRevenue = (float) $orderQuery->sum('total_amount');

        return [
            'datasets' => [
                [
                    'label' => 'Ingresos Netos',
                    'data' => [$posRevenue, $webRevenue],
                    'backgroundColor' => ['#f59e0b', '#8b5cf6'], // amber-500, violet-500
                    'hoverOffset' => 4,
                ],
            ],
            'labels' => ['Tienda Física (POS)', 'Tienda Web (Orders)'],
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1500,
                'easing' => 'easeOutElastic',
            ],
        ];
    }
}
