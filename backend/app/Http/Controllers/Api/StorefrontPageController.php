<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\StorefrontPage;
use App\Models\Category;
use App\Models\Product;

class StorefrontPageController extends Controller
{
    public function show($slug)
    {
        $page = StorefrontPage::where('slug', $slug)->where('is_active', true)->firstOrFail();

        // Process blocks to load relationships dynamically (like products or categories)
        $blocks = $page->blocks ?? [];
        
        $processedBlocks = collect($blocks)->map(function ($block) {
            if ($block['type'] === 'category_grid') {
                $categoryIds = $block['data']['category_ids'] ?? [];
                $block['data']['categories'] = Category::whereIn('id', $categoryIds)->get();
            }

            if ($block['type'] === 'featured_products') {
                $productIds = $block['data']['product_ids'] ?? [];
                $block['data']['products'] = Product::with(['primaryImage', 'brand'])
                    ->whereIn('id', $productIds)
                    ->where('is_active', true)
                    ->where('stock', '>', 0)
                    ->get();
            }

            return $block;
        });

        return response()->json([
            'title' => $page->title,
            'slug' => $page->slug,
            'blocks' => $processedBlocks,
        ]);
    }
}
