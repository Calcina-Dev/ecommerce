<?php

namespace App\Filament\Widgets;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class ProfitByProductTable extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?int $sort = 4;
    protected int | string | array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        $period = $this->filters['period'] ?? 'month';
        $dateFrom = $this->filters['date_from'] ?? null;
        $dateTo = $this->filters['date_to'] ?? null;

        return $table
            ->heading('Desempeño Financiero por Producto')
            ->query(function () use ($period, $dateFrom, $dateTo) {
                // To do this cleanly in Eloquent while using relationships is tricky,
                // we'll just build raw queries and join them to Product.
                
                $salesFilter = "";
                $ordersFilter = "";
                
                if ($period === 'week') {
                    $salesFilter = "AND sales.created_at >= '" . now()->startOfWeek() . "' AND sales.created_at <= '" . now()->endOfWeek() . "'";
                    $ordersFilter = "AND orders.created_at >= '" . now()->startOfWeek() . "' AND orders.created_at <= '" . now()->endOfWeek() . "'";
                } elseif ($period === 'month') {
                    $salesFilter = "AND sales.created_at >= '" . now()->startOfMonth() . "' AND sales.created_at <= '" . now()->endOfMonth() . "'";
                    $ordersFilter = "AND orders.created_at >= '" . now()->startOfMonth() . "' AND orders.created_at <= '" . now()->endOfMonth() . "'";
                } elseif ($period === 'year') {
                    $salesFilter = "AND sales.created_at >= '" . now()->startOfYear() . "' AND sales.created_at <= '" . now()->endOfYear() . "'";
                    $ordersFilter = "AND orders.created_at >= '" . now()->startOfYear() . "' AND orders.created_at <= '" . now()->endOfYear() . "'";
                } elseif ($period === 'custom') {
                    if ($dateFrom) {
                        $salesFilter .= " AND sales.created_at >= '{$dateFrom}'";
                        $ordersFilter .= " AND orders.created_at >= '{$dateFrom}'";
                    }
                    if ($dateTo) {
                        $salesFilter .= " AND sales.created_at <= '{$dateTo}'";
                        $ordersFilter .= " AND orders.created_at <= '{$dateTo}'";
                    }
                }

                $rawQuery = "
                    SELECT 
                        products.id, 
                        products.name,
                        COALESCE(s.qty, 0) + COALESCE(o.qty, 0) as total_qty,
                        COALESCE(s.rev, 0) + COALESCE(o.rev, 0) as total_revenue,
                        COALESCE(s.cst, 0) + COALESCE(o.cst, 0) as total_cost,
                        (COALESCE(s.rev, 0) + COALESCE(o.rev, 0)) - (COALESCE(s.cst, 0) + COALESCE(o.cst, 0)) as net_profit
                    FROM products
                    LEFT JOIN (
                        SELECT product_id, SUM(quantity) as qty, SUM(subtotal) as rev, SUM(quantity * unit_cost) as cst 
                        FROM sale_items 
                        INNER JOIN sales ON sales.id = sale_items.sale_id
                        WHERE sales.status = 'CONFIRMED' {$salesFilter}
                        GROUP BY product_id
                    ) s ON s.product_id = products.id
                    LEFT JOIN (
                        SELECT product_id, SUM(quantity) as qty, SUM(subtotal) as rev, SUM(quantity * unit_cost) as cst 
                        FROM order_items 
                        INNER JOIN orders ON orders.id = order_items.order_id
                        WHERE orders.payment_status = 'paid' {$ordersFilter}
                        GROUP BY product_id
                    ) o ON o.product_id = products.id
                    HAVING total_qty > 0
                ";

                // We can't use raw query as primary in Filament without trickery, 
                // but we can map the IDs and order. Or use a view.
                // A better approach is to just use a subquery join on the Eloquent Builder:
                
                return Product::query()
                    ->select('products.*')
                    ->selectRaw('COALESCE(s.qty, 0) + COALESCE(o.qty, 0) as total_qty')
                    ->selectRaw('COALESCE(s.rev, 0) + COALESCE(o.rev, 0) as total_revenue')
                    ->selectRaw('COALESCE(s.cst, 0) + COALESCE(o.cst, 0) as total_cost')
                    ->selectRaw('(COALESCE(s.rev, 0) + COALESCE(o.rev, 0)) - (COALESCE(s.cst, 0) + COALESCE(o.cst, 0)) as net_profit')
                    ->leftJoin(DB::raw("(
                        SELECT sale_items.product_id, SUM(sale_items.quantity) as qty, SUM(sale_items.subtotal) as rev, SUM(sale_items.quantity * sale_items.unit_cost) as cst 
                        FROM sale_items 
                        INNER JOIN sales ON sales.id = sale_items.sale_id
                        WHERE sales.status = 'CONFIRMED' {$salesFilter}
                        GROUP BY sale_items.product_id
                    ) s"), 's.product_id', '=', 'products.id')
                    ->leftJoin(DB::raw("(
                        SELECT order_items.product_id, SUM(order_items.quantity) as qty, SUM(order_items.subtotal) as rev, SUM(order_items.quantity * order_items.unit_cost) as cst 
                        FROM order_items 
                        INNER JOIN orders ON orders.id = order_items.order_id
                        WHERE orders.payment_status = 'paid' {$ordersFilter}
                        GROUP BY order_items.product_id
                    ) o"), 'o.product_id', '=', 'products.id')
                    ->whereRaw('(COALESCE(s.qty, 0) + COALESCE(o.qty, 0)) > 0');
            })
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Producto')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_qty')
                    ->label('Cant. Vendida')
                    ->sortable()
                    ->numeric(),
                Tables\Columns\TextColumn::make('total_revenue')
                    ->label('Ingresos Brutos')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('total_cost')
                    ->label('Costo Total')
                    ->money('PEN')
                    ->sortable(),
                Tables\Columns\TextColumn::make('net_profit')
                    ->label('Utilidad Neta')
                    ->money('PEN')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                Tables\Columns\TextColumn::make('margin_percent')
                    ->label('Margen %')
                    ->state(function ($record) {
                        $revenue = $record->total_revenue ?? 0;
                        $profit = $record->net_profit ?? 0;
                        if ($revenue > 0) {
                            return number_format(($profit / $revenue) * 100, 1) . '%';
                        }
                        return '0%';
                    })
                    ->color(function ($record) {
                        $revenue = $record->total_revenue ?? 0;
                        $profit = $record->net_profit ?? 0;
                        $pct = $revenue > 0 ? ($profit / $revenue) * 100 : 0;
                        if ($pct >= 30) return 'success';
                        if ($pct >= 10) return 'warning';
                        return 'danger';
                    }),
            ])
            ->defaultSort('net_profit', 'desc')
            ->paginated([5, 10, 25, 50, 'all']);
    }
}
