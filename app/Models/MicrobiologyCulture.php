<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MicrobiologyCulture extends Model
{
    use HasFactory;

    protected $fillable = ['lab_result_record_id', 'lab_sample_id', 'culture_type', 'growth_status', 'colony_count', 'direct_exam', 'notes', 'incubated_at', 'reported_at'];
    protected $casts = ['incubated_at' => 'datetime', 'reported_at' => 'datetime'];

    public function resultRecord(): BelongsTo { return $this->belongsTo(LabResultRecord::class, 'lab_result_record_id'); }
    public function sample(): BelongsTo { return $this->belongsTo(LabSample::class, 'lab_sample_id'); }
    public function isolates(): HasMany { return $this->hasMany(MicrobiologyIsolate::class); }
}
