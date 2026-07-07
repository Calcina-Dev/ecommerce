<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Favorite;
use App\Models\Product;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $favorites = $request->user()->favorites()
            ->with(['product' => function ($q) {
                $q->with(['primaryImage', 'brand', 'images'])->where('is_active', true);
            }])
            ->whereHas('product', function ($q) {
                $q->where('is_active', true);
            })
            ->latest()
            ->get();

        return response()->json($favorites);
    }

    public function toggle(Request $request, $productId)
    {
        $user = $request->user();
        $product = Product::where('is_active', true)->findOrFail($productId);

        $favorite = $user->favorites()->where('product_id', $productId)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json([
                'status' => 'removed',
                'message' => 'Eliminado de tus favoritos.',
                'product_id' => (int) $productId,
            ]);
        } else {
            $user->favorites()->create(['product_id' => $productId]);
            return response()->json([
                'status' => 'added',
                'message' => 'Añadido a tus favoritos.',
                'product_id' => (int) $productId,
            ], 201);
        }
    }

    public function sync(Request $request)
    {
        $request->validate([
            'product_ids' => 'required|array',
            'product_ids.*' => 'integer|exists:products,id',
        ]);

        $user = $request->user();
        $existingIds = $user->favorites()->pluck('product_id')->toArray();

        $toInsert = [];
        foreach ($request->product_ids as $pid) {
            if (!in_array($pid, $existingIds)) {
                $toInsert[] = [
                    'user_id' => $user->id,
                    'product_id' => $pid,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
                $existingIds[] = $pid;
            }
        }

        if (!empty($toInsert)) {
            Favorite::insert($toInsert);
        }

        $favorites = $user->favorites()
            ->with(['product' => function ($q) {
                $q->with(['primaryImage', 'brand', 'images'])->where('is_active', true);
            }])
            ->whereHas('product', fn($q) => $q->where('is_active', true))
            ->latest()
            ->get();

        return response()->json([
            'message' => 'Favoritos sincronizados correctamente.',
            'favorites' => $favorites,
        ]);
    }
}
