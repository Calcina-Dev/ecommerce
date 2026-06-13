<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Product;
use App\Models\ProductImage;
use Illuminate\Support\Facades\Schema;

class CatalogSeeder extends Seeder
{
    public function run(): void
    {
        // Limpiar las tablas para evitar conflictos de datos únicos
        Schema::disableForeignKeyConstraints();
        ProductImage::truncate();
        Product::truncate();
        Brand::truncate();
        Category::truncate();
        Schema::enableForeignKeyConstraints();

        // --- CATEGORÍAS ---
        $vitaminas = Category::create([
            'name' => 'Vitaminas',
            'slug' => 'vitaminas',
            'description' => 'Vitaminas y minerales esenciales para tu salud diaria.',
        ]);

        $proteinas = Category::create([
            'name' => 'Proteínas',
            'slug' => 'proteinas',
            'description' => 'Suplementos de proteína para favorecer la recuperación y ganancia muscular.',
        ]);

        $creatinas = Category::create([
            'name' => 'Creatinas',
            'slug' => 'creatinas',
            'description' => 'Creatina monohidratada para potenciar la fuerza, energía y explosividad.',
        ]);

        $aminoacidos = Category::create([
            'name' => 'Aminoácidos',
            'slug' => 'aminoacidos',
            'description' => 'BCAAs y aminoácidos esenciales para la síntesis de proteínas y recuperación.',
        ]);

        // --- MARCAS ---
        $optimum = Brand::create([
            'name' => 'Optimum Nutrition',
            'slug' => 'optimum-nutrition',
        ]);

        $centrum = Brand::create([
            'name' => 'Centrum',
            'slug' => 'centrum',
        ]);

        $muscletech = Brand::create([
            'name' => 'MuscleTech',
            'slug' => 'muscletech',
        ]);

        $dymatize = Brand::create([
            'name' => 'Dymatize',
            'slug' => 'dymatize',
        ]);

        $universal = Brand::create([
            'name' => 'Universal Nutrition',
            'slug' => 'universal-nutrition',
        ]);

        // --- PRODUCTOS ---

        // 1. Whey Gold Standard
        $p1 = Product::create([
            'name' => '100% Whey Gold Standard (5 lbs)',
            'slug' => '100-whey-gold-standard',
            'sku' => 'ON-WHEY-01',
            'short_description' => 'Proteína de suero de leche aislada de alta calidad.',
            'description' => 'La proteína en polvo de suero de leche más vendida del mundo. Aporta 24g de proteína de suero de leche de alta calidad, principalmente de aislado de proteína de suero (WPI), con bajos niveles de grasa y azúcares.',
            'price' => 299.90,
            'compare_at_price' => 350.00,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $optimum->id,
            'category_id' => $proteinas->id,
        ]);
        ProductImage::create([
            'product_id' => $p1->id,
            'image_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 2. Micronized Creatine Powder
        $p2 = Product::create([
            'name' => 'Micronized Creatine Powder (300g)',
            'slug' => 'micronized-creatine-powder',
            'sku' => 'ON-CREA-01',
            'short_description' => 'Creatina monohidratada micronizada de máxima pureza.',
            'description' => 'Apoya el reciclaje de ATP para movimientos explosivos. 5 gramos de creatina monohidratada pura por porción para apoyar el desarrollo de fuerza y masa muscular.',
            'price' => 139.90,
            'compare_at_price' => 160.00,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $optimum->id,
            'category_id' => $creatinas->id,
        ]);
        ProductImage::create([
            'product_id' => $p2->id,
            'image_url' => 'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 3. Amino Energy
        $p3 = Product::create([
            'name' => 'Essential Amino Energy (30 serv)',
            'slug' => 'essential-amino-energy',
            'sku' => 'ON-AMINO-01',
            'short_description' => 'Aminoácidos con cafeína de fuentes naturales.',
            'description' => 'Fórmula para cualquier momento que requieras energía mental, enfoque, y recuperación física. Contiene aminoácidos esenciales y cafeína natural proveniente del té verde.',
            'price' => 149.90,
            'stock' => 0,
            'is_featured' => false,
            'brand_id' => $optimum->id,
            'category_id' => $aminoacidos->id,
        ]);
        ProductImage::create([
            'product_id' => $p3->id,
            'image_url' => 'https://images.unsplash.com/photo-1546483875-ad9014c88eba?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 4. Centrum Multivitamínico Adultos
        $p4 = Product::create([
            'name' => 'Centrum Multivitamínico Adultos (100 tab)',
            'slug' => 'centrum-adultos',
            'sku' => 'CEN-ADULT-01',
            'short_description' => 'Multivitamínico diario completo para adultos.',
            'description' => 'Formulado con vitaminas y minerales clave para ayudar a cubrir las necesidades nutricionales diarias. Apoya la energía, la inmunidad y el metabolismo.',
            'price' => 89.90,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $centrum->id,
            'category_id' => $vitaminas->id,
        ]);
        ProductImage::create([
            'product_id' => $p4->id,
            'image_url' => 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 5. Centrum Mujer
        $p5 = Product::create([
            'name' => 'Centrum Mujer (100 tab)',
            'slug' => 'centrum-mujer',
            'sku' => 'CEN-WOMEN-01',
            'short_description' => 'Multivitamínico balanceado específicamente para mujeres.',
            'description' => 'Formulado para apoyar la salud ósea, la apariencia saludable de la piel, cabello y uñas, además de aportar energía diaria.',
            'price' => 95.00,
            'compare_at_price' => 110.00,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $centrum->id,
            'category_id' => $vitaminas->id,
        ]);
        ProductImage::create([
            'product_id' => $p5->id,
            'image_url' => 'https://images.unsplash.com/photo-1616679911721-eff6eec18fcd?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 6. Centrum Hombre
        $p6 = Product::create([
            'name' => 'Centrum Hombre (100 tab)',
            'slug' => 'centrum-hombre',
            'sku' => 'CEN-MEN-01',
            'short_description' => 'Multivitamínico formulado para la salud masculina.',
            'description' => 'Contiene niveles más altos de vitaminas del complejo B y zinc para apoyar la función muscular, la salud del corazón y el metabolismo energético masculino.',
            'price' => 95.00,
            'compare_at_price' => 110.00,
            'stock' => 0,
            'is_featured' => false,
            'brand_id' => $centrum->id,
            'category_id' => $vitaminas->id,
        ]);
        ProductImage::create([
            'product_id' => $p6->id,
            'image_url' => 'https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 7. Nitro-Tech Performance
        $p7 = Product::create([
            'name' => 'Nitro-Tech Performance (4 lbs)',
            'slug' => 'nitro-tech-performance',
            'sku' => 'MT-NITRO-01',
            'short_description' => 'Proteína de suero de leche mejorada con creatina.',
            'description' => 'Fórmula científicamente diseñada para todos los atletas que buscan más músculo, más fuerza y mejor rendimiento. Contiene aislado de proteína de suero y péptidos, además de 3g de creatina.',
            'price' => 279.90,
            'compare_at_price' => 310.00,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $muscletech->id,
            'category_id' => $proteinas->id,
        ]);
        ProductImage::create([
            'product_id' => $p7->id,
            'image_url' => 'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 8. Platinum 100% Creatine
        $p8 = Product::create([
            'name' => 'Platinum 100% Creatine (400g)',
            'slug' => 'platinum-100-creatine',
            'sku' => 'MT-CREA-01',
            'short_description' => 'Creatina pura micronizada ultrafina.',
            'description' => 'Entrega creatina directamente a tus músculos, impulsando el rendimiento. La creatina aumenta la fuerza muscular y acelera la recuperación entre entrenamientos.',
            'price' => 125.00,
            'stock' => 0,
            'is_featured' => false,
            'brand_id' => $muscletech->id,
            'category_id' => $creatinas->id,
        ]);
        ProductImage::create([
            'product_id' => $p8->id,
            'image_url' => 'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 9. ISO 100 Hydrolyzed
        $p9 = Product::create([
            'name' => 'ISO 100 Hydrolyzed (5 lbs)',
            'slug' => 'iso-100-hydrolyzed',
            'sku' => 'DYM-ISO-01',
            'short_description' => 'Proteína hidrolizada aislada de máxima absorción.',
            'description' => 'Una de las proteínas más limpias y de absorción más rápida del mercado. Cero grasas, cero lactosa, perfecta para definición muscular extrema.',
            'price' => 359.90,
            'compare_at_price' => 399.00,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $dymatize->id,
            'category_id' => $proteinas->id,
        ]);
        ProductImage::create([
            'product_id' => $p9->id,
            'image_url' => 'https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 10. Animal Pak Multivitamin
        $p10 = Product::create([
            'name' => 'Animal Pak Multivitamin (44 packs)',
            'slug' => 'animal-pak-multivitamin',
            'sku' => 'UN-ANIMAL-01',
            'short_description' => 'El pack de entrenamiento multivitamínico legendario.',
            'description' => 'Complejo de rendimiento definitivo cargado con más de 85 nutrientes clave. Diseñado para culturistas y atletas de fuerza que exigen el máximo rendimiento de sus cuerpos.',
            'price' => 229.90,
            'compare_at_price' => 250.00,
            'stock' => 0,
            'is_featured' => true,
            'brand_id' => $universal->id,
            'category_id' => $vitaminas->id,
        ]);
        ProductImage::create([
            'product_id' => $p10->id,
            'image_url' => 'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // 11. BCAA 5000 Powder
        $p11 = Product::create([
            'name' => 'BCAA 5000 Powder (300g)',
            'slug' => 'bcaa-5000-powder',
            'sku' => 'ON-BCAA-01',
            'short_description' => 'Aminoácidos ramificados ratio 2:1:1.',
            'description' => 'Aporta 5g de BCAAs (L-Leucina, L-Isoleucina y L-Valina) por servicio para proteger el músculo del catabolismo y acelerar la recuperación post-entrenamiento.',
            'price' => 119.90,
            'stock' => 0,
            'is_featured' => false,
            'brand_id' => $optimum->id,
            'category_id' => $aminoacidos->id,
        ]);
        ProductImage::create([
            'product_id' => $p11->id,
            'image_url' => 'https://images.unsplash.com/photo-1546483875-ad9014c88eba?q=80&w=600&auto=format&fit=crop',
            'is_primary' => true,
        ]);

        // --- GENERACIÓN DINÁMICA DE 30 PRODUCTOS ADICIONALES ---
        $prefixes = ['Whey Protein', 'Creatine Monohydrate', 'BCAA 2:1:1', 'Pre-Workout', 'Mass Gainer', 'Glutamine', 'Omega 3', 'Multivitamin', 'ZMA', 'Casein', 'Isolate Protein', 'Carnitine', 'CLA', 'Colágeno Hidrolizado', 'Magnesio', 'Vitamina D3', 'Ashwagandha', 'Tribulus'];
        $suffixes = ['Gold', 'Pro', 'Elite', 'Max', 'Extreme', 'Ultra', 'Performance', '100%', 'Advanced', 'Platinum', 'Premium', 'Essential'];
        $sizes = ['1 lb', '2 lbs', '5 lbs', '300g', '500g', '30 serv', '60 caps', '120 caps', '1kg', '90 tabs'];
        
        $images = [
            'https://images.unsplash.com/photo-1574680096145-d05b474e2155?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1517838277536-f5f99be501cd?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1546483875-ad9014c88eba?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1584017911766-d451b3d0e843?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1616679911721-eff6eec18fcd?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1584308666744-24d5e47ac9db?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1593095948071-474c5cc2989d?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1579758629938-03607ccdbaba?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1605296830714-7c02e14957ac?q=80&w=600&auto=format&fit=crop',
            'https://images.unsplash.com/photo-1550583713-33e1eb6eb2c6?q=80&w=600&auto=format&fit=crop',
        ];

        for ($i = 12; $i <= 41; $i++) {
            $name = $prefixes[array_rand($prefixes)] . ' ' . $suffixes[array_rand($suffixes)] . ' (' . $sizes[array_rand($sizes)] . ')';
            $basePrice = rand(40, 350) + 0.90;
            
            $p = Product::create([
                'name' => $name,
                'slug' => \Illuminate\Support\Str::slug($name) . '-' . $i,
                'sku' => 'SUPP-GEN-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'short_description' => 'Suplemento nutricional de alta calidad y rendimiento para acompañar tu estilo de vida.',
                'description' => 'Fórmula científicamente comprobada para ayudarte a alcanzar tus metas físicas. Fabricado bajo los más altos estándares de calidad internacional.',
                'price' => $basePrice,
                'compare_at_price' => rand(0, 1) ? $basePrice + rand(20, 50) : null,
                'stock' => 0,
                'is_featured' => rand(0, 4) === 0, // 20% probabilidad de ser destacado
                'brand_id' => Brand::inRandomOrder()->first()->id,
                'category_id' => Category::inRandomOrder()->first()->id,
            ]);
            
            ProductImage::create([
                'product_id' => $p->id,
                'image_url' => $images[array_rand($images)],
                'is_primary' => true,
            ]);
        }
    }
}
