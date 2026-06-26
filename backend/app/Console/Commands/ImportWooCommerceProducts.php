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

            DB::statement('TRUNCATE orders, order_items, sales, purchase_invoices, purchase_invoice_lines, purchase_orders, purchase_order_items, stock_movements, stock_balances, batches, product_images, products, coupons, cash_sessions, categories, brands CASCADE;');

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

            // 2. Categoría
            $categoryId = null;
            if (!empty($categoriesStr)) {
                $cats = explode(',', $categoriesStr);
                $firstCatName = trim(explode('>', $cats[0])[0]);
                if (!empty($firstCatName)) {
                    $category = Category::firstOrCreate(
                        ['slug' => Str::slug($firstCatName)],
                        ['name' => $firstCatName, 'is_active' => true]
                    );
                    $categoryId = $category->id;
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
            $cleanSku = !empty($sku) ? $sku : 'WC-' . strtoupper(Str::slug(substr($name, 0, 15))) . '-' . rand(10, 99);
            $product = Product::updateOrCreate(
                ['sku' => $cleanSku],
                [
                    'name' => $name,
                    'slug' => Str::slug($name) . '-' . rand(100, 999),
                    'short_description' => substr(strip_tags($shortDesc), 0, 255),
                    'description' => $desc ?: $shortDesc,
                    'price' => $finalPrice,
                    'compare_at_price' => $comparePrice,
                    'stock' => $stockQty,
                    'is_active' => true,
                    'category_id' => $categoryId,
                    'brand_id' => $brandId,
                ]
            );

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
