<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabConsumption extends Model
{
    protected $fillable = ['lab_consumption_attempt_id', 'lab_order_item_id', 'exam_consumable_requirement_id', 'product_id', 'warehouse_id', 'estimated_quantity', 'actual_quantity', 'variance_quantity', 'unit_cost', 'total_cost', 'is_reversal', 'reverses_consumption_id'];
    protected $casts = ['estimated_quantity' => 'decimal:4', 'actual_quantity' => 'decimal:4', 'variance_quantity' => 'decimal:4', 'unit_cost' => 'decimal:4', 'total_cost' => 'decimal:4', 'is_reversal' => 'boolean'];

    public function attempt(): BelongsTo { return $this->belongsTo(LabConsumptionAttempt::class, 'lab_consumption_attempt_id'); }
    public function labOrderItem(): BelongsTo { return $this->belongsTo(LabOrderItem::class); }
    public function requirement(): BelongsTo { return $this->belongsTo(ExamConsumableRequirement::class, 'exam_consumable_requirement_id'); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
