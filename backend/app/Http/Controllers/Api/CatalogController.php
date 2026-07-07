<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;
use Illuminate\Support\Facades\Cache;

class CatalogController extends Controller
{
    public function home()
    {
        $data = Cache::remember('catalog_home_v3', 600, function () {
            return [
                'featured_products' => Product::with(['images', 'primaryImage', 'brand'])
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->where('is_featured', true)
                    ->limit(8)
                    ->get()
                    ->toArray(),
                'categories' => Category::where('is_active', true)
                    ->whereNull('parent_id')
                    ->limit(6)
                    ->get()
                    ->toArray(),
            ];
        });

        return response()->json($data);
    }

    public function products(Request $request)
    {
        $queryParams = $request->only(['page', 'category_id', 'brand_id', 'search', 'sort_by', 'min_price', 'max_price', 'on_sale']);
        $cacheKey = 'catalog_products_v3_' . md5(json_encode($queryParams));
        
        $products = Cache::remember($cacheKey, 60, function () use ($request) {
            $query = Product::with(['images', 'primaryImage', 'brand', 'category'])
                ->where('is_active', true)
                ->where('stock', '>', 0);

            if ($request->filled('category_id') && $request->category_id !== 'undefined') {
                $catId = $request->category_id;
                $childIds = \App\Models\Category::where('parent_id', $catId)->pluck('id')->toArray();
                $grandChildIds = !empty($childIds) ? \App\Models\Category::whereIn('parent_id', $childIds)->pluck('id')->toArray() : [];
                $targetIds = array_merge([$catId], $childIds, $grandChildIds);

                $query->where(function ($q) use ($targetIds) {
                    $q->whereIn('category_id', $targetIds)
                      ->orWhereHas('categories', function ($cQuery) use ($targetIds) {
                          $cQuery->whereIn('categories.id', $targetIds);
                      });
                });
            }

            if ($request->filled('brand_id') && $request->brand_id !== 'undefined') {
                $query->where('brand_id', $request->brand_id);
            }

            if ($request->filled('search') && $request->search !== 'undefined') {
                $searchTerm = trim($request->search);
                $words = array_filter(explode(' ', $searchTerm), fn($w) => mb_strlen($w) >= 3);

                $query->where(function ($q) use ($searchTerm, $words) {
                    // Búsqueda por frase completa en nombre, keywords y descripciones
                    $q->where('name', 'ilike', '%' . $searchTerm . '%')
                      ->orWhere('keywords', 'ilike', '%' . $searchTerm . '%')
                      ->orWhere('short_description', 'ilike', '%' . $searchTerm . '%')
                      ->orWhere('description', 'ilike', '%' . $searchTerm . '%');

                    // Si hay múltiples palabras (ej: "dolor muscular"), permitir coincidencia combinada (todas las palabras presentes)
                    if (count($words) > 1) {
                        $q->orWhere(function ($subQ) use ($words) {
                            foreach ($words as $word) {
                                $subQ->where(function ($wQ) use ($word) {
                                    $wQ->where('name', 'ilike', '%' . $word . '%')
                                       ->orWhere('keywords', 'ilike', '%' . $word . '%')
                                       ->orWhere('short_description', 'ilike', '%' . $word . '%')
                                       ->orWhere('description', 'ilike', '%' . $word . '%');
                                });
                            }
                        });
                    }
                });
            }

            if ($request->boolean('on_sale')) {
                $query->where('compare_at_price', '>', 0)
                      ->whereColumn('compare_at_price', '>', 'price');
            }

            if ($request->filled('min_price') && is_numeric($request->min_price)) {
                $query->where('price', '>=', $request->min_price);
            }

            if ($request->filled('max_price') && is_numeric($request->max_price)) {
                $query->where('price', '<=', $request->max_price);
            }

            if ($request->filled('sort_by')) {
                switch ($request->sort_by) {
                    case 'price_asc':
                        $query->orderBy('price', 'asc');
                        break;
                    case 'price_desc':
                        $query->orderBy('price', 'desc');
                        break;
                    case 'newest':
                        $query->orderBy('created_at', 'desc');
                        break;
                    case 'name_asc':
                        $query->orderBy('name', 'asc');
                        break;
                    default:
                        $query->orderBy('id', 'desc');
                        break;
                }
            } else {
                $query->orderBy('id', 'desc');
            }

            return $query->paginate(12)->toArray();
        });

        return response()->json($products);
    }

    public function productDetail($slug)
    {
        $product = Cache::remember('catalog_detail_v3_' . $slug, 600, function () use ($slug) {
            return Product::with(['images', 'brand', 'category', 'categories'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail()
                ->toArray();
        });

        return response()->json($product);
    }

    public function filters()
    {
        $data = Cache::remember('catalog_filters_tree_v4', 3600, function () {
            return [
                'categories' => Category::where('is_active', true)
                    ->whereNull('parent_id')
                    ->whereIn('slug', ['para-ti', 'por-necesidad-especifica', 'vitaminas-y-suplementos', 'ofertas', 'destacados-peruanos'])
                    ->orderByRaw("CASE 
                        WHEN slug = 'para-ti' THEN 1
                        WHEN slug = 'por-necesidad-especifica' THEN 2
                        WHEN slug = 'vitaminas-y-suplementos' THEN 3
                        WHEN slug = 'ofertas' THEN 4
                        WHEN slug = 'destacados-peruanos' THEN 5
                        ELSE 6 END")
                    ->with(['children' => function ($q) {
                        $q->where('is_active', true)->with(['children' => fn ($q2) => $q2->where('is_active', true)]);
                    }])
                    ->get()
                    ->toArray(),
                'brands' => Brand::where('is_active', true)->get()->toArray(),
            ];
        });

        return response()->json($data);
    }

    /**
     * Verificar stock y precios actualizados para un lote de productos del carrito.
     */
    public function checkStock(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array|min:1|max:50',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $products = Product::whereIn('id', $request->product_ids)
            ->select('id', 'name', 'slug', 'price', 'stock', 'is_active')
            ->get()
            ->keyBy('id');

        $result = [];
        foreach ($request->product_ids as $productId) {
            $product = $products->get($productId);
            if ($product) {
                $result[] = [
                    'id' => $product->id,
                    'name' => $product->name,
                    'price' => (string) $product->price,
                    'stock' => $product->stock,
                    'is_active' => $product->is_active,
                    'available' => $product->is_active && $product->stock > 0,
                ];
            } else {
                $result[] = [
                    'id' => $productId,
                    'available' => false,
                    'stock' => 0,
                ];
            }
        }

        return response()->json($result);
    }
}
