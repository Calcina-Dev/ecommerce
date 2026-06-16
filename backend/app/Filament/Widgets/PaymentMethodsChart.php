<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\SalePayment;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class PaymentMethodsChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 3;
    protected ?string $heading = 'Métodos de Pago (POS)';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;
        
        $salePaymentQuery = \App\Models\SalePayment::query()->whereHas('sale', function($q) {
            $q->where('status', 'CONFIRMED');
        });

        if ($period === 'week') {
            $salePaymentQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $salePaymentQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $salePaymentQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $salePaymentQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $salePaymentQuery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $salePayments = $salePaymentQuery->with('paymentMethod')->get();

        $methods = [];

        foreach ($salePayments as $payment) {
            $name = $payment->paymentMethod->name ?? 'Desconocido';
            $methods[$name] = ($methods[$name] ?? 0) + $payment->amount;
        }

        // Sort by amount descending
        arsort($methods);

        $labels = array_keys($methods);
        $data = array_values($methods);
        $colors = ['#4f46e5', '#10b981', '#f59e0b', '#ef4444', '#8b5cf6'];

        return [
            'datasets' => [
                [
                    'label' => 'Total Cobrado',
                    'data' => $data,
                    'backgroundColor' => array_slice($colors, 0, count($data)),
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
