<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Product;
use App\Models\Category;
use App\Models\Brand;

class CatalogController extends Controller
{
    public function home()
    {
        $featuredProducts = Product::with(['images', 'primaryImage', 'brand'])
            ->where('is_active', true)
            ->where('is_featured', true)
            ->limit(8)
            ->get();

        $categories = Category::where('is_active', true)
            ->whereNull('parent_id')
            ->limit(6)
            ->get();

        return response()->json([
            'featured_products' => $featuredProducts,
            'categories' => $categories,
        ]);
    }

    public function products(Request $request)
    {
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

        $products = $query->paginate(12);

        return response()->json($products);
    }

    public function productDetail($slug)
    {
        $product = Product::with(['images', 'brand', 'category'])
            ->where('slug', $slug)
            ->where('is_active', true)
            ->firstOrFail();

        return response()->json($product);
    }

    public function filters()
    {
        $categories = Category::where('is_active', true)->get();
        $brands = Brand::where('is_active', true)->get();

        return response()->json([
            'categories' => $categories,
            'brands' => $brands,
        ]);
    }
}
