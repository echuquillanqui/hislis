<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabResultRecord extends Model
{
    use HasFactory;

    protected $fillable = ['lab_order_item_id', 'template_version_id', 'exam_method_id', 'entered_by', 'technically_validated_by', 'professionally_approved_by', 'content', 'report_version', 'status', 'entered_at', 'technically_validated_at', 'professionally_approved_at', 'approval_notes'];
    protected $casts = ['content' => 'array', 'entered_at' => 'datetime', 'technically_validated_at' => 'datetime', 'professionally_approved_at' => 'datetime'];

    public function orderItem(): BelongsTo { return $this->belongsTo(LabOrderItem::class, 'lab_order_item_id'); }
    public function templateVersion(): BelongsTo { return $this->belongsTo(TemplateVersion::class); }
    public function examMethod(): BelongsTo { return $this->belongsTo(ExamMethod::class); }
    public function values(): HasMany { return $this->hasMany(LabResultValue::class); }
    public function corrections(): HasMany { return $this->hasMany(LabResultCorrection::class); }
    public function microbiologyCultures(): HasMany { return $this->hasMany(MicrobiologyCulture::class); }
}

