<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CustomerPriceList extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'name',
        'valid_from',
        'valid_to',
        'status',
    ];

    protected $casts = [
        'valid_from' => 'date',
        'valid_to' => 'date',
        'status' => 'boolean',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(CustomerPriceListItem::class);
    }
}
