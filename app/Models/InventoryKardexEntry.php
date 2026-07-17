<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use LogicException;

class InventoryKardexEntry extends Model
{
    protected $fillable = ['product_id', 'warehouse_id', 'inventory_lot_id', 'user_id', 'movement_type', 'quantity_in', 'quantity_out', 'balance_after', 'reference_type', 'reference_id', 'reason', 'occurred_at'];

    protected $casts = ['quantity_in' => 'decimal:4', 'quantity_out' => 'decimal:4', 'balance_after' => 'decimal:4', 'occurred_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new LogicException('El kardex es inmutable.'));
        static::deleting(fn () => throw new LogicException('El kardex es inmutable.'));
    }

    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lot(): BelongsTo { return $this->belongsTo(InventoryLot::class, 'inventory_lot_id'); }
}
