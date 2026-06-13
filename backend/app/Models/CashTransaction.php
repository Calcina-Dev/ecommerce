<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CashTransaction extends Model
{
    protected $fillable = [
        'cash_session_id',
        'type',
        'amount',
        'description',
    ];

    public function session()
    {
        return $this->belongsTo(CashSession::class, 'cash_session_id');
    }
}
