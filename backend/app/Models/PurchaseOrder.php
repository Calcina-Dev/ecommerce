<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\PurchaseOrderObserver;

#[ObservedBy([PurchaseOrderObserver::class])]
class PurchaseOrder extends Model
{
    protected $fillable = ['supplier_id', 'order_number', 'status', 'total_amount', 'notes', 'expected_delivery_date', 'document_series', 'document_number'];

    protected $casts = [
        'expected_delivery_date' => 'date',
        'total_amount' => 'decimal:2',
    ];

    public function supplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(PurchaseOrderItem::class);
    }
}
