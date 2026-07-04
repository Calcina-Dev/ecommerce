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
        $cacheKey = 'catalog_products_v3_' . md5(json_encode($request->all()));
        
        $products = Cache::remember($cacheKey, 60, function () use ($request) {
            $query = Product::with(['images', 'primaryImage', 'brand', 'category'])
                ->where('is_active', true);

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
                $query->where('name', 'ilike', '%' . $request->search . '%');
            }

            if ($request->boolean('on_sale')) {
                $query->where('compare_at_price', '>', 0)
                      ->whereColumn('compare_at_price', '>', 'price');
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
}
