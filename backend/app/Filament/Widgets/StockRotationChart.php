<?php

namespace App\Filament\Widgets;

use Filament\Widgets\ChartWidget;
use App\Models\Category;
use Illuminate\Support\Facades\DB;

class StockRotationChart extends ChartWidget
{
    protected ?string $pollingInterval = null;
    protected ?string $heading = 'Rotación de Inventario por Categoría';
    protected static ?int $sort = 2;
    protected ?string $maxHeight = '300px';

    public function getDescription(): string|\Illuminate\Contracts\Support\Htmlable|null
    {
        return new \Illuminate\Support\HtmlString('
            <div style="display: flex; align-items: center; gap: 1rem; font-size: 0.75rem; color: #6b7280; margin-top: 0.25rem;">
                <div style="display: flex; align-items: center; gap: 0.375rem;"><span style="width: 8px; height: 8px; border-radius: 9999px; background-color: #10b981;"></span> Excelente (> 1.0)</div>
                <div style="display: flex; align-items: center; gap: 0.375rem;"><span style="width: 8px; height: 8px; border-radius: 9999px; background-color: #f59e0b;"></span> Saludable (0.5 - 1.0)</div>
                <div style="display: flex; align-items: center; gap: 0.375rem;"><span style="width: 8px; height: 8px; border-radius: 9999px; background-color: #ef4444;"></span> Riesgo (< 0.5)</div>
            </div>
        ');
    }

    protected function getData(): array
    {
        $categories = Category::all();
        $labels = [];
        $data = [];
        $backgroundColors = [];

        foreach ($categories as $category) {
            $labels[] = $category->name;

            // Simplified Rotation: Total Sales / Average Inventory
            // We approximate this by counting sold items vs current stock in the category
            $soldItems = DB::table('order_items')
                ->join('products', 'order_items.product_id', '=', 'products.id')
                ->join('orders', 'order_items.order_id', '=', 'orders.id')
                ->where('products.category_id', $category->id)
                ->where('orders.status', '!=', 'cancelled')
                ->sum('order_items.quantity');

            $currentStock = DB::table('stock_balances')
                ->join('products', 'stock_balances.product_id', '=', 'products.id')
                ->where('products.category_id', $category->id)
                ->sum('stock_balances.on_hand');

            // Rotation Rate = Sold / Current Stock (approximation)
            $rotationRate = $currentStock > 0 ? ($soldItems / $currentStock) : 0;
            $data[] = round($rotationRate, 2);

            // Conditional Colors
            if ($rotationRate >= 1.0) {
                $backgroundColors[] = '#10b981'; // Verde (Excelente)
            } elseif ($rotationRate >= 0.5) {
                $backgroundColors[] = '#f59e0b'; // Amarillo (Saludable)
            } else {
                $backgroundColors[] = '#ef4444'; // Rojo (Lenta / Riesgo)
            }
        }

        return [
            'datasets' => [
                [
                    'label' => 'Índice de Rotación',
                    'data' => $data,
                    'backgroundColor' => $backgroundColors,
                    'borderRadius' => 4,
                ],
            ],
            'labels' => $labels,
        ];
    }

    protected function getType(): string
    {
        return 'bar';
    }

    protected function getOptions(): array
    {
        return [
            'animation' => [
                'duration' => 1500,
                'easing' => 'easeOutElastic',
            ],
        ];
    }
}
