<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Tariff extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'code', 'name', 'currency', 'valid_from', 'valid_to', 'status'];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'status' => 'boolean',
    ];

    public function branch(): BelongsTo
    {
        return $this->belongsTo(Branch::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(TariffItem::class);
    }
}
