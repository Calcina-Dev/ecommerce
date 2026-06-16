<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\CashSession;
use App\Models\SalePayment;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class CashFlowOverview extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        $period = $this->filters['period'] ?? 'today';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        $sessionQuery = CashSession::query();
        $paymentQuery = SalePayment::query();

        if ($period === 'today') {
            $sessionQuery->whereDate('created_at', now()->toDateString());
            $paymentQuery->whereDate('created_at', now()->toDateString());
        } elseif ($period === 'week') {
            $sessionQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
            $paymentQuery->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
        } elseif ($period === 'month') {
            $sessionQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
            $paymentQuery->whereBetween('created_at', [now()->startOfMonth(), now()->endOfMonth()]);
        } elseif ($period === 'year') {
            $sessionQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
            $paymentQuery->whereBetween('created_at', [now()->startOfYear(), now()->endOfYear()]);
        } elseif ($period === 'custom') {
            if ($dateFrom) {
                $sessionQuery->whereDate('created_at', '>=', $dateFrom);
                $paymentQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $sessionQuery->whereDate('created_at', '<=', $dateTo);
                $paymentQuery->whereDate('created_at', '<=', $dateTo);
            }
        }

        $sessions = $sessionQuery->with(['transactions', 'salePayments'])->get();
        $payments = $paymentQuery->get();

        $totalIngresosCaja = $payments->sum('amount');
        
        $descuadreTotal = 0;
        $sesionesCerradas = 0;

        foreach ($sessions as $session) {
            if ($session->closed_at) {
                $sesionesCerradas++;
                
                // Calculate expected balance
                $expectedBalance = $session->opening_balance;
                
                // Add sales payments in cash
                $cashSales = $session->salePayments()->whereHas('paymentMethod', fn($q) => $q->where('name', 'Efectivo'))->sum('amount');
                $expectedBalance += $cashSales;
                
                // Add/subtract cash transactions
                foreach ($session->transactions as $tx) {
                    if ($tx->type === 'in') {
                        $expectedBalance += $tx->amount;
                    } else {
                        $expectedBalance -= $tx->amount;
                    }
                }

                $descuadre = $session->closing_balance - $expectedBalance;
                $descuadreTotal += $descuadre;
            }
        }

        // Calculate discrepancy color
        $descuadreColor = $descuadreTotal < 0 ? 'danger' : ($descuadreTotal > 0 ? 'warning' : 'success');

        return [
            Stat::make('Ingresos en POS', 'S/ ' . number_format($totalIngresosCaja, 2))
                ->description('Cobros registrados en caja')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('success'),

            Stat::make('Descuadre Acumulado', 'S/ ' . number_format($descuadreTotal, 2))
                ->description('Diferencia efectivo físico vs sistema')
                ->descriptionIcon('heroicon-m-scale')
                ->color($descuadreColor),

            Stat::make('Sesiones de Caja', $sessions->count())
                ->description($sesionesCerradas . ' cerradas / ' . ($sessions->count() - $sesionesCerradas) . ' abiertas')
                ->descriptionIcon('heroicon-m-inbox-stack')
                ->color('primary'),
        ];
    }
}
