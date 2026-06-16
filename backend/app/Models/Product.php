<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

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
        'brand_id',
        'category_id',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'price' => 'decimal:2',
        'compare_at_price' => 'decimal:2',
    ];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
