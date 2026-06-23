<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\OrderObserver;

#[ObservedBy([OrderObserver::class])]
class Order extends Model
{
    protected $fillable = [
        'user_id',
        'order_number',
        'status',
        'total_amount',
        'total_cost',
        'shipping_name',
        'shipping_email',
        'shipping_phone',
        'shipping_address',
        'shipping_city',
        'shipping_department',
        'shipping_province',
        'shipping_district',
        'shipping_postal_code',
        'payment_method',
        'payment_status',
        'document_type',
        'document_series',
        'document_number',
        'coupon_id',
        'discount_amount',
        'shipping_method_id',
        'tracking_code',
        'shipping_cost',
        'card_brand',
        'card_bin',
        'card_last_digits',
        'card_country',
        'is_foreign_card',
        'gateway_transaction_id',
        'processing_at',
        'shipped_at',
        'delivered_at',
        'cancelled_at',
    ];

    protected $casts = [
        'processing_at' => 'datetime',
        'shipped_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function coupon(): BelongsTo
    {
        return $this->belongsTo(Coupon::class);
    }

    public function shippingMethod(): BelongsTo
    {
        return $this->belongsTo(ShippingMethod::class);
    }

    public function notes()
    {
        return $this->hasMany(OrderNote::class)->orderBy('created_at', 'desc');
    }
}
