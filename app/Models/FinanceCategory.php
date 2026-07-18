<?php

namespace App\Models;

class FinanceCategory extends BaseCatalog
{
    protected $fillable = ['code', 'name', 'type', 'description', 'status', 'sort_order'];

    protected $casts = ['status' => 'boolean'];
}
