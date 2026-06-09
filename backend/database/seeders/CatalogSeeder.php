<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        $vitaminas = Category::create([
            'name' => 'Vitaminas',
            'slug' => 'vitaminas',
            'description' => 'Vitaminas esenciales para el día a día',
        ]);

        $proteinas = Category::create([
            'name' => 'Proteínas',
            'slug' => 'proteinas',
            'description' => 'Proteínas para recuperación muscular',
        ]);

        $brand1 = Brand::create([
            'name' => 'Optimum Nutrition',
            'slug' => 'optimum-nutrition',
        ]);

        $brand2 = Brand::create([
            'name' => 'Centrum',
            'slug' => 'centrum',
        ]);

        Product::create([
            'name' => '100% Whey Gold Standard',
            'slug' => '100-whey-gold-standard',
            'sku' => 'ON-WHEY-01',
            'short_description' => 'Proteína de suero de leche aislada',
            'description' => 'La proteína de suero más vendida del mundo.',
            'price' => 299.90,
            'compare_at_price' => 350.00,
            'stock' => 50,
            'is_featured' => true,
            'brand_id' => $brand1->id,
            'category_id' => $proteinas->id,
        ]);

        Product::create([
            'name' => 'Centrum Multivitamínico Adultos',
            'slug' => 'centrum-adultos',
            'sku' => 'CEN-01',
            'short_description' => 'Multivitamínico diario',
            'description' => 'Completo desde la A al Zinc.',
            'price' => 89.90,
            'stock' => 100,
            'is_featured' => true,
            'brand_id' => $brand2->id,
            'category_id' => $vitaminas->id,
        ]);
    }
}
