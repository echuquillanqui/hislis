<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabSample extends Model
{
    use HasFactory;

    protected $fillable = ['lab_order_id', 'sample_type_id', 'collected_by', 'barcode', 'status', 'collected_at', 'notes'];
    protected $casts = ['collected_at' => 'datetime'];

    public function labOrder(): BelongsTo { return $this->belongsTo(LabOrder::class); }
    public function sampleType(): BelongsTo { return $this->belongsTo(SampleType::class); }
    public function items(): BelongsToMany { return $this->belongsToMany(LabOrderItem::class, 'lab_order_item_lab_sample')->withTimestamps(); }
    public function events(): HasMany { return $this->hasMany(LabSampleEvent::class); }
}
