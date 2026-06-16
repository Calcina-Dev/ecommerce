<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseStatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

use App\Models\Sale;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Filament\Support\Colors\Color;

class StatsOverviewWidget extends BaseStatsOverviewWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;
        
        $saleQuery = Sale::query()->where('status', 'CONFIRMED');
        $orderQuery = \App\Models\Order::query()->where('payment_status', 'paid');
        $customerQuery = \App\Models\Customer::query();

        if ($period === 'week') {
            $saleQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $orderQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $customerQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $saleQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $orderQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $customerQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $saleQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            $orderQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            $customerQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $saleQuery->whereDate('created_at', '>=', $dateFrom);
                $orderQuery->whereDate('created_at', '>=', $dateFrom);
                $customerQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $saleQuery->whereDate('created_at', '<=', $dateTo);
                $orderQuery->whereDate('created_at', '<=', $dateTo);
                $customerQuery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $sales = $saleQuery->get();
        $orders = $orderQuery->get();
        $nuevosClientes = $customerQuery->count();

        $totalVentas = $sales->sum('total_amount') + $orders->sum('total_amount');
        $totalCost = $sales->sum('total_cost') + $orders->sum('total_cost');
        $margen = $totalVentas - $totalCost;
        $numOperaciones = $sales->count() + $orders->count();
        $ticketPromedio = $numOperaciones > 0 ? ($totalVentas / $numOperaciones) : 0;

        return [
            Stat::make('Ventas del Período', 'S/ ' . number_format($totalVentas, 2))
                ->description('Ventas brutas acumuladas')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('primary'),

            Stat::make('Utilidad (Margen)', 'S/ ' . number_format($margen, 2))
                ->description('Ganancia bruta sobre costo')
                ->descriptionIcon('heroicon-m-arrow-trending-up')
                ->color('success'),

            Stat::make('Ticket Promedio', 'S/ ' . number_format($ticketPromedio, 2))
                ->description('Monto medio por boleta')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('warning'),

            Stat::make('Volumen de Ventas', number_format($numOperaciones) . ' oper.')
                ->description('Transacciones confirmadas')
                ->descriptionIcon('heroicon-m-cube')
                ->color('info'),
        ];
    }
}
