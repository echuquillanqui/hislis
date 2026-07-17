<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ExamMethod extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_id',
        'analytical_principle_id',
        'equipment_id',
        'manufacturer_id',
        'sample_type_id',
        'measurement_unit_id',
        'name',
        'result_type',
        'decimals',
        'analytical_range',
        'detection_limit',
        'is_default',
        'show_on_report',
        'status',
    ];

    protected $casts = [
        'is_default' => 'boolean',
        'show_on_report' => 'boolean',
        'status' => 'boolean',
    ];

    public function exam(): BelongsTo
    {
        return $this->belongsTo(Exam::class);
    }

    public function analyticalPrinciple(): BelongsTo
    {
        return $this->belongsTo(AnalyticalPrinciple::class);
    }

    public function equipment(): BelongsTo
    {
        return $this->belongsTo(Equipment::class);
    }

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function sampleType(): BelongsTo
    {
        return $this->belongsTo(SampleType::class);
    }

    public function measurementUnit(): BelongsTo
    {
        return $this->belongsTo(MeasurementUnit::class);
    }

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(ExamMethodReferenceRange::class);
    }

    public function criticalValues(): HasMany
    {
        return $this->hasMany(ExamMethodCriticalValue::class);
    }
}
