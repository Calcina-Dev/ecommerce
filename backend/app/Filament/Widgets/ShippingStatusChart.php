<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ShippingStatusChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected ?string $heading = 'Estado de Pedidos (Mensual)';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $query = DB::table('orders');

        if ($period === 'week') {
            $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $query->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $query->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $query->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $query->whereDate('created_at', '<=', $dateTo);
            }
        }

        $statuses = $query->select('status', DB::raw('COUNT(*) as total'))
            ->groupBy('status')
            ->get();

        $labels = [];
        $data = [];
        $colors = [];

        foreach ($statuses as $stat) {
            $labels[] = strtoupper($stat->status);
            $data[] = $stat->total;
            
            // Assign unique colors based on status to avoid duplicates
            switch (strtolower($stat->status)) {
                case 'delivered':
                case 'completed':
                    $colors[] = '#10b981'; // emerald-500
                    break;
                case 'shipped':
                    $colors[] = '#8b5cf6'; // violet-500
                    break;
                case 'processing':
                    $colors[] = '#f59e0b'; // amber-500
                    break;
                case 'pending_payment':
                case 'pending':
                    $colors[] = '#3b82f6'; // blue-500
                    break;
                case 'cancelled':
                case 'failed':
                case 'refunded':
                    $colors[] = '#ef4444'; // red-500
                    break;
                default:
                    // Provide a generic distinct color for unknown statuses
                    $colors[] = '#' . substr(md5($stat->status), 0, 6);
                    break;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Órdenes',
                    'data' => $data,
                    'backgroundColor' => $colors,
                ],
            ],
            'labels' => $labels,
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
