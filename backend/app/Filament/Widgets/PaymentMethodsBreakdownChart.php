<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\SalePayment;
use Illuminate\Support\Facades\DB;

class PaymentMethodsBreakdownChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 2;
    protected ?string $heading = 'Ingresos por Método de Pago (POS)';
    protected int | string | array $columnSpan = 'full';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'today';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $paymentQuery = SalePayment::query()->join('payment_methods', 'sale_payments.payment_method_id', '=', 'payment_methods.id');

        if ($period === 'today') {
            $paymentQuery->whereDate('sale_payments.created_at', now()->toDateString());
        } elseif ($period === 'week') {
            $paymentQuery->whereBetween('sale_payments.created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $paymentQuery->whereBetween('sale_payments.created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $paymentQuery->whereBetween('sale_payments.created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $paymentQuery->whereDate('sale_payments.created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $paymentQuery->whereDate('sale_payments.created_at', '<=', $dateTo);
            }
        }

        $paymentsData = $paymentQuery
            ->select('payment_methods.name', DB::raw('SUM(sale_payments.amount) as total'))
            ->groupBy('payment_methods.name')
            ->orderByDesc('total')
            ->get();

        return [
            'datasets' => [
                [
                    'label' => 'Total (S/)',
                    'data' => $paymentsData->pluck('total')->toArray(),
                    'backgroundColor' => '#3b82f6', // Use a single solid color like blue for all bars
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $paymentsData->pluck('name')->toArray(),
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
