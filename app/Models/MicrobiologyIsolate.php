<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MicrobiologyIsolate extends Model
{
    use HasFactory;

    protected $fillable = ['microbiology_culture_id', 'microorganism_id', 'isolate_number', 'quantity', 'notes'];

    public function culture(): BelongsTo { return $this->belongsTo(MicrobiologyCulture::class, 'microbiology_culture_id'); }
    public function microorganism(): BelongsTo { return $this->belongsTo(Microorganism::class); }
    public function susceptibilities(): HasMany { return $this->hasMany(AntibioticSusceptibility::class); }
}
