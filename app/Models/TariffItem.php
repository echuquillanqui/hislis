<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class TariffItem extends Model
{
    use HasFactory;

    protected $fillable = ['tariff_id', 'tariffable_id', 'tariffable_type', 'price', 'status'];

    protected $casts = [
        'price' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function tariff(): BelongsTo
    {
        return $this->belongsTo(Tariff::class);
    }

    public function tariffable(): MorphTo
    {
        return $this->morphTo();
    }
}
