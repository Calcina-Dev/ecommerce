<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrdersMapWidget extends Widget
{
    use InteractsWithPageFilters;

    protected string $view = 'filament.widgets.orders-map-widget';
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 1;

    protected function getViewData(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $query = DB::table('orders')
            ->select('shipping_city', DB::raw('COUNT(id) as total_orders'), DB::raw('SUM(total_amount) as total_revenue'))
            ->whereNotNull('shipping_city')
            ->where('shipping_city', '!=', '');

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

        $ordersByCity = $query->groupBy('shipping_city')->get();

        // Convert to a dictionary with uppercase city names for easier matching in JS
        $mapData = [];
        foreach ($ordersByCity as $order) {
            $mapData[strtoupper(trim($order->shipping_city))] = [
                'orders' => $order->total_orders,
                'revenue' => $order->total_revenue
            ];
        }

        return [
            'mapData' => $mapData,
        ];
    }
}
