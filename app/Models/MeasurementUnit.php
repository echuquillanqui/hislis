<?php

namespace App\Models;

class MeasurementUnit extends BaseCatalog
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'sort_order',
        'symbol',
        'unit_type',
    ];
}
