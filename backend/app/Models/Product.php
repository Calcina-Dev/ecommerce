<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Product extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'name',
        'slug',
        'sku',
        'short_description',
        'description',
        'price',
        'compare_at_price',
        'stock',
        'is_active',
        'is_featured',
        'show_gmp_badge',
        'badge_1_title',
        'badge_1_subtitle',
        'show_fefo_badge',
        'badge_2_title',
        'badge_2_subtitle',
        'show_shipping_badge',
        'badge_3_title',
        'badge_3_subtitle',
        'brand_id',
        'category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'show_gmp_badge' => 'boolean',
        'show_fefo_badge' => 'boolean',
        'show_shipping_badge' => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
    ];

    protected static function booted(): void
    {
        static::saved(function ($product) {
            \Illuminate\Support\Facades\Cache::forget('catalog_detail_v3_' . $product->slug);
            if ($product->getOriginal('slug')) {
                \Illuminate\Support\Facades\Cache::forget('catalog_detail_v3_' . $product->getOriginal('slug'));
            }
            \Illuminate\Support\Facades\Cache::flush();
        });

        static::deleted(function ($product) {
            \Illuminate\Support\Facades\Cache::forget('catalog_detail_v3_' . $product->slug);
            \Illuminate\Support\Facades\Cache::flush();
        });
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class);
    }

    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function primaryImage()
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true)->orWhere(function ($query) {
            $query->orderBy('sort_order')->limit(1);
        });
    }

    public function stockBalances()
    {
        return $this->hasMany(StockBalance::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function getAverageEntryCostAttribute()
    {
        $movements = StockMovement::where('product_id', $this->id)->where('type', 'IN')->get();
        $totalQuantity = $movements->sum('quantity');
        
        if ($totalQuantity == 0) {
            return 0;
        }

        $totalCost = $movements->sum(function ($mov) {
            return $mov->quantity * $mov->unit_cost;
        });

        return $totalCost / $totalQuantity;
    }

    public function getRecommendedPriceAttribute()
    {
        $cost = $this->average_entry_cost;
        // Margen de ganancia sugerido: 60%
        return $cost * 1.60;
    }

    public function getTotalStockAttribute()
    {
        return $this->stockBalances()->sum('on_hand');
    }

    public function warehouses()
    {
        return $this->belongsToMany(Warehouse::class)
            ->withPivot('stock')
            ->withTimestamps();
    }
}
