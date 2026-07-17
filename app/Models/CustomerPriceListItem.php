<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class CustomerPriceListItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_price_list_id',
        'priceable_id',
        'priceable_type',
        'price',
        'discount_percent',
        'status',
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'discount_percent' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function priceList(): BelongsTo
    {
        return $this->belongsTo(CustomerPriceList::class, 'customer_price_list_id');
    }

    public function priceable(): MorphTo
    {
        return $this->morphTo();
    }
}
