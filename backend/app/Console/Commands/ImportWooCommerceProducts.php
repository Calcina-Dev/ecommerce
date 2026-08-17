<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\Category;
use App\Models\Brand;
use App\Models\Batch;
use App\Models\StockBalance;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\DB;

class ImportWooCommerceProducts extends Command
{
    protected $signature = 'import:woocommerce {filename? : Nombre del archivo CSV en storage/} {--clean : Limpia toda la base de datos operativa antes de importar}';
    protected $description = 'Importa productos, stocks, precios, lotes e imágenes desde WooCommerce, opcionalmente limpiando datos operativos';

    public function handle()
    {
        $filename = $this->argument('filename');

        if ($this->option('clean')) {
            $this->warn('Limpiando base de datos operativa en PostgreSQL (OC, Facturas, Ventas, Clientes, Cajas, Inventario)...');

            DB::statement('TRUNCATE orders, order_items, sales, purchase_invoices, purchase_invoice_lines, purchase_orders, purchase_order_items, stock_movements, stock_balances, batches, product_images, products, coupons, cash_sessions, categories, brands RESTART IDENTITY CASCADE;');
            try { DB::statement("SELECT setval('products_id_seq', 69, true);"); } catch (\Throwable $e) {}

            User::whereNotIn('role', ['admin', 'employee'])->delete();

            $this->info('¡Limpieza quirúrgica completa! Sistema operativo reiniciado en blanco. Solo quedaron los administradores.');
        }

        $adminUser = User::whereIn('role', ['admin', 'employee'])->first();
        $adminUserId = $adminUser ? $adminUser->id : 1;

        if (!$filename) {
            $files = File::glob(storage_path('*.csv'));
            if (empty($files)) {
                $this->error('No se encontró ningún archivo CSV en storage/. Coloca el archivo descargado allí.');
                return 1;
            }
            $filepath = $files[0];
            $this->info("Usando archivo detectado automáticamente: " . basename($filepath));
        } else {
            $filepath = storage_path($filename);
            if (!File::exists($filepath)) {
                $this->error("El archivo $filepath no existe.");
                return 1;
            }
        }

        $handle = fopen($filepath, 'r');
        if (!$handle) {
            $this->error('No se pudo abrir el archivo.');
            return 1;
        }

        $header = fgetcsv($handle);
        $colMap = array_flip($header);

        $imagesDir = storage_path('app/public/products');
        if (!File::exists($imagesDir)) {
            File::makeDirectory($imagesDir, 0755, true, true);
        }

        // Garantizar la estructura predefinida de categorías y subcategorías
        $predefinedHierarchy = [
            'Para ti' => ['Para Mujeres', 'Para Hombres', 'Adultos Mayores (50+)', 'Para Niños', 'Para Deportistas'],
            'Por Necesidad Específica' => [
                'Dolor Articular' => [],
                'Mejor Inmunidad' => [],
                'Mejor Digestión' => ['Probióticos'],
            ],
            'Vitaminas y Suplementos' => ['Multivitamínicos', 'Gomitas', 'Magnesio', 'Omega 3 y aceites', 'Hierro y Calcio', 'Vitaminas B / C / D / E'],
            'Ofertas' => ['2x1 y Combos', 'Descuentos por tiempo limitado'],
            'Destacados Peruanos' => ['Aguaymanto', 'Cacao Peruano', 'Lucuma', 'Maca Andina', 'Sacha Inchi'],
        ];

        foreach ($predefinedHierarchy as $parentName => $children) {
            $parentCat = Category::firstOrCreate(
                ['slug' => Str::slug($parentName)],
                ['name' => $parentName, 'parent_id' => null, 'is_active' => true]
            );
            if ($parentCat->name !== $parentName) {
                $parentCat->update(['name' => $parentName]);
            }

            foreach ($children as $childKey => $childVal) {
                $childName = is_array($childVal) ? $childKey : $childVal;
                $subchildren = is_array($childVal) ? $childVal : [];

                $childCat = Category::firstOrCreate(
                    ['slug' => Str::slug($childName)],
                    ['name' => $childName, 'parent_id' => $parentCat->id, 'is_active' => true]
                );
                if ($childCat->parent_id !== $parentCat->id) {
                    $childCat->update(['parent_id' => $parentCat->id]);
                }

                foreach ($subchildren as $subName) {
                    $subCat = Category::firstOrCreate(
                        ['slug' => Str::slug($subName)],
                        ['name' => $subName, 'parent_id' => $childCat->id, 'is_active' => true]
                    );
                    if ($subCat->parent_id !== $childCat->id) {
                        $subCat->update(['parent_id' => $childCat->id]);
                    }
                }
            }
        }

        $importedCount = 0;
        $updatedCount = 0;
        $imagesDownloaded = 0;

        $this->output->progressStart();

        while (($row = fgetcsv($handle)) !== false) {
            $name = trim($row[$colMap['Nombre'] ?? 4] ?? '');
            if (empty($name)) {
                continue;
            }

            $sku = trim($row[$colMap['SKU'] ?? 2] ?? '');
            $priceNormal = trim($row[$colMap['Precio normal'] ?? 26] ?? '');
            $priceRebajado = trim($row[$colMap['Precio rebajado'] ?? 25] ?? '');
            $stockVal = trim($row[$colMap['Inventario'] ?? 15] ?? '');
            $shortDesc = trim($row[$colMap['Descripción corta'] ?? 8] ?? '');
            $desc = trim($row[$colMap['Descripción'] ?? 9] ?? '');
            $categoriesStr = trim($row[$colMap['Categorías'] ?? 27] ?? '');
            $brandsStr = trim($row[$colMap['Marcas'] ?? 41] ?? '');
            $imagesStr = trim($row[$colMap['Imágenes'] ?? 30] ?? '');

            // 1. Precios
            $regPrice = is_numeric($priceNormal) ? floatval($priceNormal) : 0;
            $salePrice = is_numeric($priceRebajado) ? floatval($priceRebajado) : 0;

            $finalPrice = $salePrice > 0 ? $salePrice : ($regPrice > 0 ? $regPrice : 29.90);
            $comparePrice = ($salePrice > 0 && $regPrice > $salePrice) ? $regPrice : null;

            $stockQty = is_numeric($stockVal) ? intval($stockVal) : 25;

            // 2. Categoría (múltiples y jerárquicas)
            $primaryCategoryId = null;
            $allCategoryIds = [];

            if (!empty($categoriesStr)) {
                $catsList = explode(',', $categoriesStr);
                foreach ($catsList as $catPath) {
                    $catPath = trim($catPath);
                    if (empty($catPath)) continue;

                    $parts = explode('>', $catPath);
                    $parentId = null;
                    $lastCat = null;

                    foreach ($parts as $partName) {
                        $partName = trim($partName);
                        if (empty($partName)) continue;

                        if ($partName === 'Ofertas Especiales') $partName = 'Ofertas';
                        if ($partName === 'Por Necesidad') $partName = 'Por Necesidad Específica';
                        if ($partName === 'Sistema Inmunológico Fuerte') $partName = 'Mejor Inmunidad';
                        if ($partName === 'Estreñimiento y Digestión') $partName = 'Mejor Digestión';

                        $slug = Str::slug($partName);
                        $category = Category::firstOrCreate(
                            ['slug' => $slug],
                            ['name' => $partName, 'parent_id' => $parentId, 'is_active' => true]
                        );

                        if ($parentId && !$category->parent_id && $category->id != $parentId) {
                            $category->update(['parent_id' => $parentId]);
                        }

                        $parentId = $category->id;
                        $lastCat = $category;
                        $allCategoryIds[] = $category->id;
                    }

                    if ($lastCat && !$primaryCategoryId) {
                        $primaryCategoryId = $lastCat->id;
                    }
                }
            }

            // 3. Marca
            $brandId = null;
            if (!empty($brandsStr)) {
                $brandNames = explode(',', $brandsStr);
                $firstBrandName = trim($brandNames[0]);
                if (!empty($firstBrandName)) {
                    $brand = Brand::firstOrCreate(
                        ['slug' => Str::slug($firstBrandName)],
                        ['name' => $firstBrandName, 'is_active' => true]
                    );
                    $brandId = $brand->id;
                }
            }

            // 4. Producto
            $cleanSku = !empty($sku) ? $sku : 'WC-' . strtoupper(Str::slug(substr($name, 0, 20)));

            $slug = Str::slug($name);
            $originalSlug = $slug;
            $counter = 1;
            // Si el slug ya existe para OTRO producto distinto (incluso en papelera), le agregamos un sufijo
            while (Product::withTrashed()->where('slug', $slug)->where('sku', '!=', $cleanSku)->exists()) {
                $slug = $originalSlug . '-' . $counter;
                $counter++;
            }

            $product = Product::updateOrCreate(
                ['sku' => $cleanSku],
                [
                    'name' => $name,
                    'slug' => $slug,
                    'short_description' => substr(strip_tags($shortDesc), 0, 255),
                    'description' => $desc ?: $shortDesc,
                    'price' => $finalPrice,
                    'compare_at_price' => $comparePrice,
                    'stock' => $stockQty,
                    'is_active' => true,
                    'category_id' => $primaryCategoryId,
                    'brand_id' => $brandId,
                ]
            );

            if (!empty($allCategoryIds)) {
                $product->categories()->syncWithoutDetaching(array_unique($allCategoryIds));
            }

            if ($product->wasRecentlyCreated) {
                $importedCount++;
            } else {
                $updatedCount++;
            }

            // 5. Inventario Inicial Clínico (Batch + FEFO StockBalance)
            if ($stockQty > 0 && $adminUserId) {
                $unitCost = round($finalPrice * 0.45, 2);
                $batch = Batch::firstOrCreate(
                    ['product_id' => $product->id, 'batch_number' => 'LOTE-WC-INIT'],
                    [
                        'expiration_date' => now()->addYears(2),
                        'status' => 'ACTIVE',
                        'unit_cost' => $unitCost,
                    ]
                );

                StockBalance::updateOrCreate(
                    ['warehouse_id' => 1, 'product_id' => $product->id, 'batch_id' => $batch->id],
                    ['on_hand' => $stockQty]
                );

                StockMovement::firstOrCreate(
                    ['product_id' => $product->id, 'batch_id' => $batch->id, 'reason' => 'Migración inicial de WooCommerce'],
                    [
                        'warehouse_id' => 1,
                        'user_id' => $adminUserId,
                        'type' => 'IN',
                        'quantity' => $stockQty,
                        'unit_cost' => $unitCost,
                        'total_cost' => $stockQty * $unitCost,
                    ]
                );
            }

            // 6. Descargar Imágenes físicas locales
            if (!empty($imagesStr)) {
                $imageUrls = explode(',', $imagesStr);
                $sortOrder = 0;
                
                foreach ($imageUrls as $imgUrl) {
                    $imgUrl = trim($imgUrl);
                    if (!empty($imgUrl) && filter_var($imgUrl, FILTER_VALIDATE_URL)) {
                        try {
                            $parsedUrl = parse_url($imgUrl);
                            $ext = pathinfo($parsedUrl['path'] ?? '', PATHINFO_EXTENSION) ?: 'jpg';
                            $localImgName = $product->id . '_' . $sortOrder . '_' . Str::slug(substr($product->name, 0, 18)) . '.' . $ext;
                            $localImgPath = $imagesDir . '/' . $localImgName;
                            $dbRelativeUrl = 'products/' . $localImgName;

                            if (!File::exists($localImgPath)) {
                                $imageContents = Http::timeout(10)->get($imgUrl)->body();
                                if ($imageContents) {
                                    File::put($localImgPath, $imageContents);
                                    $imagesDownloaded++;
                                }
                            }

                            ProductImage::updateOrCreate(
                                ['product_id' => $product->id, 'sort_order' => $sortOrder],
                                [
                                    'image_url' => $dbRelativeUrl,
                                    'is_primary' => ($sortOrder === 0),
                                ]
                            );
                            $sortOrder++;
                        } catch (\Throwable $e) {
                            error_log("Error descargando imagen $imgUrl: " . $e->getMessage());
                        }
                    }
                }
            }

            $this->output->progressAdvance();
        }

        fclose($handle);
        $this->output->progressFinish();

        $this->info("¡Migración e inventario inicial completados!");
        $this->info("Productos importados: $importedCount");
        $this->info("Imágenes guardadas físicas: $imagesDownloaded");
        
        return 0;
    }
}
