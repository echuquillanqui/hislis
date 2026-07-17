<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResultValue extends Model
{
    use HasFactory;

    protected $fillable = ['lab_result_record_id', 'template_field_id', 'exam_method_reference_range_id', 'field_slug', 'field_label', 'field_type', 'unit', 'value', 'numeric_value', 'reference_min', 'reference_max', 'reference_text', 'flag', 'is_abnormal', 'is_critical'];
    protected $casts = ['numeric_value' => 'decimal:4', 'reference_min' => 'decimal:4', 'reference_max' => 'decimal:4', 'is_abnormal' => 'boolean', 'is_critical' => 'boolean'];

    public function resultRecord(): BelongsTo { return $this->belongsTo(LabResultRecord::class); }
}
