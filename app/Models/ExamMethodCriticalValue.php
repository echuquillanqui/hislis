<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExamMethodCriticalValue extends Model
{
    use HasFactory;

    protected $fillable = [
        'exam_method_id',
        'low_value',
        'high_value',
        'message',
        'requires_notification',
        'status',
    ];

    protected $casts = [
        'low_value' => 'decimal:4',
        'high_value' => 'decimal:4',
        'requires_notification' => 'boolean',
        'status' => 'boolean',
    ];

    public function examMethod(): BelongsTo
    {
        return $this->belongsTo(ExamMethod::class);
    }
}
