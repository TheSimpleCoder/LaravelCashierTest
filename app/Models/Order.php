<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    protected $fillable = [
        'merchant_id',
        'amount',
        'currency',
        'status',
    ];

    public function merchant() : BelongsTo
    {
        return $this->belongsTo(Merchant::class);
    }
}
