<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Customer;
use App\Models\Order;
use Illuminate\Support\Facades\DB;

class CustomerInsightsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $totalCustomers = Customer::count();
        $totalRevenue = Order::where('status', '!=', 'cancelled')->sum('total_amount');
        
        $arpu = $totalCustomers > 0 ? $totalRevenue / $totalCustomers : 0;
        
        $totalOrders = Order::where('status', '!=', 'cancelled')->count();
        $ticketPromedio = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        // Tasa de Retención (Clientes que han comprado más de 1 vez)
        $repeatCustomers = Order::where('status', '!=', 'cancelled')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(id) > 1')
            ->get()
            ->count();
        
        $retentionRate = $totalCustomers > 0 ? ($repeatCustomers / $totalCustomers) * 100 : 0;

        return [
            Stat::make('Total de Clientes Registrados', number_format($totalCustomers))
                ->description('Usuarios activos en la plataforma')
                ->descriptionIcon('heroicon-m-users')
                ->color('info'),
            
            Stat::make('ARPU (Ingreso Promedio por Usuario)', 'S/ ' . number_format($arpu, 2))
                ->description('Valor promedio que te deja cada cliente')
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success'),
            
            Stat::make('Ticket Promedio General', 'S/ ' . number_format($ticketPromedio, 2))
                ->description('Monto medio por cada compra')
                ->descriptionIcon('heroicon-m-shopping-cart')
                ->color('primary'),

            Stat::make('Tasa de Retención', number_format($retentionRate, 1) . '%')
                ->description('Clientes que compran más de una vez')
                ->descriptionIcon('heroicon-m-arrow-path')
                ->color($retentionRate >= 30 ? 'success' : 'warning'),
        ];
    }
}
