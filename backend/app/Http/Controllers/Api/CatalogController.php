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
        $data = Cache::remember('catalog_home', 600, function () {
            return [
                'featured_products' => Product::with(['images', 'primaryImage', 'brand'])
                    ->where('is_active', true)
                    ->where('is_featured', true)
                    ->limit(8)
                    ->get(),
                'categories' => Category::where('is_active', true)
                    ->whereNull('parent_id')
                    ->limit(6)
                    ->get(),
            ];
        });

        return response()->json($data);
    }

    public function products(Request $request)
    {
        $cacheKey = 'catalog_products_' . md5(json_encode($request->all()));
        
        $products = Cache::remember($cacheKey, 600, function () use ($request) {
            $query = Product::with(['images', 'primaryImage', 'brand', 'category'])
                ->where('is_active', true);

            if ($request->has('category_id')) {
                $query->where('category_id', $request->category_id);
            }

            if ($request->has('brand_id')) {
                $query->where('brand_id', $request->brand_id);
            }

            if ($request->has('search')) {
                $query->where('name', 'ilike', '%' . $request->search . '%');
            }

            if ($request->boolean('on_sale')) {
                $query->where('compare_at_price', '>', 0)
                      ->whereColumn('compare_at_price', '>', 'price');
            }

            return $query->paginate(12);
        });

        return response()->json($products);
    }

    public function productDetail($slug)
    {
        $product = Cache::remember('catalog_detail_' . $slug, 600, function () use ($slug) {
            return Product::with(['images', 'brand', 'category'])
                ->where('slug', $slug)
                ->where('is_active', true)
                ->firstOrFail();
        });

        return response()->json($product);
    }

    public function filters()
    {
        $data = Cache::remember('catalog_filters', 3600, function () {
            return [
                'categories' => Category::where('is_active', true)->get(),
                'brands' => Brand::where('is_active', true)->get(),
            ];
        });

        return response()->json($data);
    }
}
