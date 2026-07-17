<?php

namespace App\Models;

class PaymentMethod extends BaseCatalog
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'sort_order',
        'requires_reference',
    ];

    protected $casts = [
        'status' => 'boolean',
        'requires_reference' => 'boolean',
    ];
}
