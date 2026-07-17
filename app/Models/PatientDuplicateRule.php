<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PatientDuplicateRule extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'fields',
        'is_blocking',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'fields' => 'array',
        'is_blocking' => 'boolean',
        'status' => 'boolean',
    ];
}
