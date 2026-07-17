<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LabSampleEvent extends Model
{
    protected $fillable = ['lab_sample_id', 'user_id', 'event', 'notes', 'occurred_at'];
    protected $casts = ['occurred_at' => 'datetime'];

    public function sample(): BelongsTo { return $this->belongsTo(LabSample::class, 'lab_sample_id'); }
}
