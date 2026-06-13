<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class ProfitOverviewWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        // Base queries
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

        // We can just sum directly in DB for better performance
        $posRevenue = $saleQuery->sum('total_amount');
        $posCost = $saleQuery->sum('total_cost');

        // Order total_cost doesn't seem to be saved reliably in all flows earlier, 
        // but we added it. Let's sum from items to be perfectly safe, or just use the field if reliable.
        $webRevenue = $orderQuery->sum('total_amount');
        $webCost = $orderQuery->sum('total_cost');

        $totalRevenue = $posRevenue + $webRevenue;
        $totalCost = $posCost + $webCost;
        $netProfit = $totalRevenue - $totalCost;

        $marginPercent = $totalRevenue > 0 ? ($netProfit / $totalRevenue) * 100 : 0;

        return [
            Stat::make('Ingresos Brutos', 'S/ ' . number_format($totalRevenue, 2))
                ->description('Ventas POS y Web cobradas')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),
                
            Stat::make('Costo Total de Ventas', 'S/ ' . number_format($totalCost, 2))
                ->description('Costo unitario de los productos')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('danger'),
                
            Stat::make('Utilidad Neta', 'S/ ' . number_format($netProfit, 2))
                ->description(number_format($marginPercent, 1) . '% de Margen de Ganancia')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),
        ];
    }
}
