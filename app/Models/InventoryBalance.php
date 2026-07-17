<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class InventoryBalance extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'inventory_lot_id', 'quantity'];

    protected $casts = ['quantity' => 'decimal:4'];

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lot(): BelongsTo { return $this->belongsTo(InventoryLot::class, 'inventory_lot_id'); }
}
