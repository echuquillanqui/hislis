<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabResultCorrection extends Model
{
    use HasFactory;

    protected $fillable = ['lab_result_record_id', 'corrected_by', 'from_report_version', 'to_report_version', 'previous_content', 'new_content', 'reason', 'corrected_at'];
    protected $casts = ['previous_content' => 'array', 'new_content' => 'array', 'corrected_at' => 'datetime'];

    public function resultRecord(): BelongsTo { return $this->belongsTo(LabResultRecord::class); }
}
