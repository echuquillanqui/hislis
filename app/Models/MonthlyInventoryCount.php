<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MonthlyInventoryCount extends Model
{
    protected $fillable = ['warehouse_id', 'created_by', 'approved_by', 'period_month', 'status', 'counted_at', 'approved_at', 'closed_at', 'notes'];

    protected $casts = ['period_month' => 'date', 'counted_at' => 'datetime', 'approved_at' => 'datetime', 'closed_at' => 'datetime'];

    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
    public function lines(): HasMany { return $this->hasMany(MonthlyInventoryCountLine::class); }
}
