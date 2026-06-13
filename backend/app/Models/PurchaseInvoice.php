<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\PurchaseInvoiceObserver;

#[ObservedBy([PurchaseInvoiceObserver::class])]
class PurchaseInvoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'purchase_order_id',
        'supplier_id',
        'warehouse_id',
        'document_number',
        'issue_date',
        'total_amount',
        'shipping_cost',
        'discount',
        'status',
    ];

    protected $casts = [
        'issue_date' => 'date',
    ];

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function purchaseOrder()
    {
        return $this->belongsTo(PurchaseOrder::class);
    }

    public function supplier()
    {
        return $this->belongsTo(Supplier::class);
    }

    public function lines()
    {
        return $this->hasMany(PurchaseInvoiceLine::class);
    }

    public function movements()
    {
        return $this->morphMany(StockMovement::class, 'reference');
    }
}
