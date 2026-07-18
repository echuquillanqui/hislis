<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabConsumptionAttempt extends Model
{
    protected $fillable = ['lab_order_item_id', 'idempotency_key', 'status', 'reprocess_number', 'failure_reason', 'processed_at'];
    protected $casts = ['processed_at' => 'datetime'];

    public function labOrderItem(): BelongsTo { return $this->belongsTo(LabOrderItem::class); }
    public function consumptions(): HasMany { return $this->hasMany(LabConsumption::class); }
}
