<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MonthlyInventoryCountLine extends Model
{
    protected $fillable = ['monthly_inventory_count_id', 'product_id', 'inventory_lot_id', 'snapshot_quantity', 'counted_quantity', 'difference_quantity', 'unit_cost', 'valuation_amount', 'adjustment_applied', 'notes'];

    protected $casts = [
        'snapshot_quantity' => 'decimal:4',
        'counted_quantity' => 'decimal:4',
        'difference_quantity' => 'decimal:4',
        'unit_cost' => 'decimal:4',
        'valuation_amount' => 'decimal:4',
        'adjustment_applied' => 'boolean',
    ];

    public function count(): BelongsTo { return $this->belongsTo(MonthlyInventoryCount::class, 'monthly_inventory_count_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function lot(): BelongsTo { return $this->belongsTo(InventoryLot::class, 'inventory_lot_id'); }
}
