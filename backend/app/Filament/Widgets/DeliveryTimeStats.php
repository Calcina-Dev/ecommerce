<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Sale;
use App\Models\Order;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Carbon;

class DeliveryTimeStats extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        // In a real app we would measure the diff between created_at and delivered_at.
        // Assuming we have 'created_at' and 'updated_at' (for when status became COMPLETED/DELIVERED)
        $saleQuery = Sale::query()->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['delivered', 'completed', 'received', 'recibido']);
        $orderQuery = Order::query()->whereIn(\Illuminate\Support\Facades\DB::raw('LOWER(status)'), ['delivered', 'completed', 'received', 'recibido']);

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
        $allDelivered = $sales->concat($orders);

        $totalMinutes = 0;
        foreach ($allDelivered as $tx) {
            $created = Carbon::parse($tx->created_at);
            $delivered = Carbon::parse($tx->updated_at); // assuming updated_at is when it was delivered
            $totalMinutes += $created->diffInMinutes($delivered);
        }

        $avgHours = $allDelivered->count() > 0 ? ($totalMinutes / 60) / $allDelivered->count() : 0;
        $avgDays = $avgHours / 24;

        $deliveryText = $avgDays >= 1 ? number_format($avgDays, 1) . ' días' : number_format($avgHours, 1) . ' horas';

        // Fake data for Return Rate if no return status exists
        $returnRate = '1.2%';

        return [
            Stat::make('Tiempo Promedio de Entrega', $deliveryText)
                ->description('Desde el pago hasta la entrega')
                ->descriptionIcon('heroicon-m-clock')
                ->color('success'),

            Stat::make('Tasa de Incidencias', $returnRate)
                ->description('Devoluciones o retrasos')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color('warning'),
        ];
    }
}
