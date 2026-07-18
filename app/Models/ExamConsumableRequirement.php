<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamConsumableRequirement extends Model
{
    protected $fillable = ['exam_id', 'product_id', 'warehouse_id', 'estimated_quantity', 'allowed_variance_percent', 'auto_consume', 'is_active'];
    protected $casts = ['estimated_quantity' => 'decimal:4', 'allowed_variance_percent' => 'decimal:2', 'auto_consume' => 'boolean', 'is_active' => 'boolean'];

    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function warehouse(): BelongsTo { return $this->belongsTo(Warehouse::class); }
}
