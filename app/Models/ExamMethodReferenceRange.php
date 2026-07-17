<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamMethodReferenceRange extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_method_id',
        'equipment_id',
        'measurement_unit_id',
        'branch_id',
        'sex',
        'min_age_days',
        'max_age_days',
        'pregnancy',
        'condition',
        'min_value',
        'max_value',
        'reference_text',
        'status',
    ];

    protected $casts = [
        'pregnancy' => 'boolean',
        'min_value' => 'decimal:4',
        'max_value' => 'decimal:4',
        'status' => 'boolean',
    ];

    public function examMethod(): BelongsTo
    {
        return $this->belongsTo(ExamMethod::class);
    }
}
