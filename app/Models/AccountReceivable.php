<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AccountReceivable extends Model
{
    use HasFactory;

    protected $fillable = ['finance_period_id', 'branch_id', 'lab_order_id', 'patient_id', 'customer_id', 'original_amount', 'paid_amount', 'balance_amount', 'issued_on', 'due_on', 'status'];

    protected $casts = ['original_amount' => 'decimal:2', 'paid_amount' => 'decimal:2', 'balance_amount' => 'decimal:2', 'issued_on' => 'date', 'due_on' => 'date'];

    public function period(): BelongsTo { return $this->belongsTo(FinancePeriod::class, 'finance_period_id'); }
    public function order(): BelongsTo { return $this->belongsTo(LabOrder::class, 'lab_order_id'); }
}
