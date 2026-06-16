<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class PurchaseFrequencyChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    protected ?string $heading = 'Frecuencia de Compra';
    protected static ?int $sort = 3;
    protected ?string $maxHeight = '300px';

    protected function getData(): array
    {
        // Agrupar usuarios por la cantidad de compras que han hecho
        $userPurchaseCounts = Order::where('status', '!=', 'cancelled')
            ->select('user_id', DB::raw('COUNT(id) as total_purchases'))
            ->groupBy('user_id')
            ->pluck('total_purchases');

        $oneTime = 0;
        $twoToThree = 0;
        $fourPlus = 0;

        foreach ($userPurchaseCounts as $count) {
            if ($count == 1) {
                $oneTime++;
            } elseif ($count >= 2 && $count <= 3) {
                $twoToThree++;
            } elseif ($count >= 4) {
                $fourPlus++;
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Clientes',
                    'data' => [$oneTime, $twoToThree, $fourPlus],
                    'backgroundColor' => ['#94a3b8', '#3b82f6', '#10b981'],
                ],
            ],
            'labels' => ['1 Compra', '2-3 Compras', '+4 Compras'],
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
