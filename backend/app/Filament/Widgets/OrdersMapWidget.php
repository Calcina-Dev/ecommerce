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
            ->select(
                DB::raw("COALESCE(NULLIF(shipping_department, ''), NULLIF(shipping_city, '')) as department"),
                DB::raw('COUNT(id) as total_orders'),
                DB::raw('SUM(total_amount) as total_revenue')
            )
            ->where(function ($q) {
                $q->where(function ($sub) {
                    $sub->whereNotNull('shipping_department')->where('shipping_department', '!=', '');
                })->orWhere(function ($sub) {
                    $sub->whereNotNull('shipping_city')->where('shipping_city', '!=', '');
                });
            })
            ->whereIn(DB::raw('LOWER(status)'), ['shipped', 'delivered', 'completed', 'received', 'recibido']);

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

        $ordersByDep = $query->groupBy(DB::raw("COALESCE(NULLIF(shipping_department, ''), NULLIF(shipping_city, ''))"))->get();

        // Convert to a dictionary with uppercase department names without accents for easier matching in JS
        $mapData = [];
        $unwanted = [
            'Á' => 'A', 'É' => 'E', 'Í' => 'I', 'Ó' => 'O', 'Ú' => 'U',
            'À' => 'A', 'È' => 'E', 'Ì' => 'I', 'Ò' => 'O', 'Ù' => 'U',
            'Ä' => 'A', 'Ë' => 'E', 'Ï' => 'I', 'Ö' => 'O', 'Ü' => 'U',
            'Â' => 'A', 'Ê' => 'E', 'Î' => 'I', 'Ô' => 'O', 'Û' => 'U',
        ];

        foreach ($ordersByDep as $order) {
            if ($order->department) {
                $depKey = strtoupper(trim($order->department));
                $depKey = strtr($depKey, $unwanted);

                if (!isset($mapData[$depKey])) {
                    $mapData[$depKey] = [
                        'orders' => 0,
                        'revenue' => 0,
                    ];
                }
                $mapData[$depKey]['orders'] += $order->total_orders;
                $mapData[$depKey]['revenue'] += $order->total_revenue;
            }
        }

        return [
            'mapData' => $mapData,
        ];
    }
}
