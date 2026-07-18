<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'default_turnaround_minutes',
        'requires_fasting',
        'template_id',
        'status',
    ];

    protected $casts = [
        'requires_fasting' => 'boolean',
        'status' => 'boolean',
    ];

    public function areas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'exam_area')
            ->withPivot('is_primary')
            ->withTimestamps();
    }

    public function sampleTypes(): BelongsToMany
    {
        return $this->belongsToMany(SampleType::class, 'exam_sample_type')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function methods(): HasMany
    {
        return $this->hasMany(ExamMethod::class);
    }

    public function template() { return $this->belongsTo(Template::class); }

    public function consumableRequirements(): HasMany
    {
        return $this->hasMany(ExamConsumableRequirement::class);
    }

    public function tariffItems(): MorphMany
    {
        return $this->morphMany(TariffItem::class, 'tariffable');
    }
}
