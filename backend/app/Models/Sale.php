<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Attributes\ObservedBy;
use App\Observers\SaleObserver;

#[ObservedBy([SaleObserver::class])]
class Sale extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'user_id',
        'warehouse_id',
        'document_type',
        'document_series',
        'document_number',
        'subtotal',
        'total_tax',
        'total_amount',
        'total_cost',
        'status',
        'customer_id',
        'customer_email',
    ];

    public function customer()
    {
        return $this->belongsTo(User::class, 'customer_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function warehouse()
    {
        return $this->belongsTo(Warehouse::class);
    }

    public function items()
    {
        return $this->hasMany(SaleItem::class);
    }

    public function payments()
    {
        return $this->hasMany(SalePayment::class);
    }

    public function stockMovements()
    {
        return $this->hasMany(StockMovement::class);
    }
}
