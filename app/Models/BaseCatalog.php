<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

abstract class BaseCatalog extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'description',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];
}
