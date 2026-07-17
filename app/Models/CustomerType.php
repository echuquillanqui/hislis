<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CustomerType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'requires_ruc',
        'allows_credit',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'requires_ruc' => 'boolean',
        'allows_credit' => 'boolean',
        'status' => 'boolean',
    ];
}
