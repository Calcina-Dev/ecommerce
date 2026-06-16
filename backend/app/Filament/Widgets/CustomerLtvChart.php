<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use App\Models\Customer;
use Carbon\Carbon;

class CustomerLtvChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    protected ?string $heading = 'Evolución del LTV (Life Time Value)';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        $months = [];
        $ltvData = [];

        for ($i = 5; $i >= 0; $i--) {
            $month = Carbon::now()->subMonths($i);
            $months[] = $month->format('M Y');

            // Calculate LTV up to this month
            // Total Revenue up to end of this month / Total Customers up to end of this month
            $endOfMonth = $month->copy()->endOfMonth();
            
            $totalCustomers = Customer::where('created_at', '<=', $endOfMonth)->count();
            $totalRevenue = Order::where('created_at', '<=', $endOfMonth)
                                 ->where('status', '!=', 'cancelled')
                                 ->sum('total_amount');

            $ltv = $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0;
            $ltvData[] = round($ltv, 2);
        }

        return [
            'datasets' => [
                [
                    'label' => 'LTV (S/)',
                    'data' => $ltvData,
                    'borderColor' => '#3b82f6',
                    'backgroundColor' => 'rgba(59, 130, 246, 0.1)',
                    'fill' => true,
                ],
            ],
            'labels' => $months,
        ];
    }

    protected function getType(): string
    {
        return 'line';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1500,
                'easing' => 'easeOutQuart',
            ],
            'elements' => [
                'line' => [
                    'tension' => 0.4, // Smooth curves
                ],
            ],
        ];
    }
}
