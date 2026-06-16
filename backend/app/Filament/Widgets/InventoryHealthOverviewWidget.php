<?php

namespace App\Filament\Widgets;

use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;
use App\Models\Product;
use App\Models\OrderItem;
use Carbon\Carbon;

class InventoryHealthOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;
    protected int | string | array $columnSpan = 'full';

    protected function getStats(): array
    {
        // 1. Total Value of Inventory (Capital Estancado)
        $products = Product::all();
        $totalCapital = 0;
        $lowStockSkus = 0;
        
        foreach ($products as $product) {
            $totalStock = $product->total_stock;
            $cost = $product->average_entry_cost;
            $totalCapital += ($totalStock * $cost);
            
            if ($totalStock > 0 && $totalStock <= 5) { // Assuming 5 is low stock threshold
                $lowStockSkus++;
            }
        }

        // 2. Dead Stock Percentage
        // Products with stock > 0 but no sales in the last 90 days
        $ninetyDaysAgo = Carbon::now()->subDays(90);
        $totalSkusWithStock = 0;
        $deadSkus = 0;

        foreach ($products as $product) {
            if ($product->total_stock > 0) {
                $totalSkusWithStock++;
                
                $hasSales = OrderItem::where('product_id', $product->id)
                    ->whereHas('order', function ($query) use ($ninetyDaysAgo) {
                        $query->where('status', '!=', 'cancelled')
                              ->where('created_at', '>=', $ninetyDaysAgo);
                    })->exists();

                if (!$hasSales) {
                    $deadSkus++;
                }
            }
        }

        $deadStockPercentage = $totalSkusWithStock > 0 ? ($deadSkus / $totalSkusWithStock) * 100 : 0;

        return [
            Stat::make('Capital Estancado en Inventario', 'S/ ' . number_format($totalCapital, 2))
                ->description('Valor de compra de todo el stock actual')
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning'),
            
            Stat::make('SKUs en Alerta (Low Stock)', $lowStockSkus)
                ->description('Productos con 5 o menos unidades')
                ->descriptionIcon('heroicon-m-exclamation-triangle')
                ->color($lowStockSkus > 0 ? 'danger' : 'success'),
            
            Stat::make('Inventario Muerto (> 90 días)', number_format($deadStockPercentage, 1) . '%')
                ->description($deadSkus . ' productos sin ventas recientes')
                ->descriptionIcon('heroicon-m-archive-box-x-mark')
                ->color($deadStockPercentage > 15 ? 'danger' : 'success'),
        ];
    }
}
