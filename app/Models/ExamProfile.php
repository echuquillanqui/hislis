<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ExamProfile extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function exams(): BelongsToMany
    {
        return $this->belongsToMany(Exam::class, 'exam_profile_exam')
            ->withPivot('sort_order')
            ->withTimestamps();
    }

    public function tariffItems(): MorphMany
    {
        return $this->morphMany(TariffItem::class, 'tariffable');
    }
}
