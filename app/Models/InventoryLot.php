<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryLot extends Model
{
    protected $fillable = ['product_id', 'lot_number', 'expiry_date', 'received_at', 'status'];

    protected $casts = ['expiry_date' => 'date', 'received_at' => 'date'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
}
