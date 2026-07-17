<?php

namespace App\Models;

class InventoryMovementType extends BaseCatalog
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'sort_order',
        'direction',
        'requires_approval',
        'affects_cost',
    ];

    protected $casts = [
        'status' => 'boolean',
        'requires_approval' => 'boolean',
        'affects_cost' => 'boolean',
    ];
}
