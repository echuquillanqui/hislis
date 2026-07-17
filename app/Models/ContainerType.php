<?php

namespace App\Models;

class ContainerType extends BaseCatalog
{
    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'sort_order',
        'color',
    ];
}
