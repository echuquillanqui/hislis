<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AntibioticSusceptibility extends Model
{
    use HasFactory;

    protected $fillable = ['microbiology_isolate_id', 'antibiotic_id', 'interpretation', 'method', 'mic', 'disk_diffusion_mm', 'notes'];
    protected $casts = ['mic' => 'decimal:3', 'disk_diffusion_mm' => 'decimal:2'];

    public function isolate(): BelongsTo { return $this->belongsTo(MicrobiologyIsolate::class, 'microbiology_isolate_id'); }
    public function antibiotic(): BelongsTo { return $this->belongsTo(Antibiotic::class); }
}
