<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Carbon\Carbon;

class WebPaymentMethodsChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected ?string $heading = 'Métodos de Pago (Web)';
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;
        
        $orderQuery = \App\Models\Order::query()
            ->where('payment_status', 'paid')
            ->whereNotNull('payment_method');

        if ($period === 'week') {
            $orderQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $orderQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $orderQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $orderQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $orderQuery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $orders = $orderQuery->get();

        $methods = [];

        foreach ($orders as $order) {
            $name = $order->payment_method ?? 'Desconocido';
            
            // Format known gateways and seed data
            if (strtolower($name) === 'mercadopago') $name = 'Mercado Pago';
            if (strtolower($name) === 'paypal') $name = 'PayPal';
            if (strtolower($name) === 'stripe') $name = 'Stripe';
            if (strtolower($name) === 'culqi') $name = 'Culqi';
            if (strtolower($name) === 'niubiz') $name = 'Niubiz';
            
            // Seed data translations
            if (strtolower($name) === 'cash') $name = 'Efectivo';
            if (strtolower($name) === 'card') $name = 'Tarjeta';
            if (strtolower($name) === 'transfer') $name = 'Transferencia';

            $methods[$name] = ($methods[$name] ?? 0) + $order->total_amount;
        }

        arsort($methods);

        $labels = array_keys($methods);
        $data = array_values($methods);
        // Use a different color palette for web gateways
        $colors = ['#ec4899', '#3b82f6', '#f43f5e', '#14b8a6', '#f97316'];

        // If there are more methods than colors, loop or fill
        while(count($colors) < count($data)) {
            $colors = array_merge($colors, $colors);
        }

        return [
            'datasets' => [
                [
                    'label' => 'Total Cobrado (Web)',
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
