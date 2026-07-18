<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class FinanceMovement extends Model
{
    use HasFactory;

    protected $fillable = ['finance_period_id', 'finance_category_id', 'branch_id', 'lab_order_id', 'cash_movement_id', 'created_by', 'type', 'status', 'amount', 'occurred_on', 'reference', 'description'];

    protected $casts = ['amount' => 'decimal:2', 'occurred_on' => 'date'];

    public function period(): BelongsTo { return $this->belongsTo(FinancePeriod::class, 'finance_period_id'); }
    public function category(): BelongsTo { return $this->belongsTo(FinanceCategory::class, 'finance_category_id'); }
}
