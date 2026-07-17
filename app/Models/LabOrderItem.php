<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class LabOrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['lab_order_id', 'orderable_id', 'orderable_type', 'parent_profile_id', 'exam_id', 'description', 'unit_price', 'discount', 'total', 'status'];
    protected $casts = ['unit_price' => 'decimal:2', 'discount' => 'decimal:2', 'total' => 'decimal:2'];

    public function labOrder(): BelongsTo { return $this->belongsTo(LabOrder::class); }
    public function orderable(): MorphTo { return $this->morphTo(); }
    public function exam(): BelongsTo { return $this->belongsTo(Exam::class); }
    public function samples(): BelongsToMany { return $this->belongsToMany(LabSample::class, 'lab_order_item_lab_sample')->withTimestamps(); }
    public function resultRecord() { return $this->hasOne(LabResultRecord::class); }
}
