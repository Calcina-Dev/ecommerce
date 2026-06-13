<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class WarehouseTransferItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'warehouse_transfer_id',
        'product_id',
        'quantity',
    ];

    public function transfer()
    {
        return $this->belongsTo(WarehouseTransfer::class, 'warehouse_transfer_id');
    }

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
