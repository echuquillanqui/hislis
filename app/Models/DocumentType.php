<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentType extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'min_length',
        'max_length',
        'is_numeric',
        'status',
        'sort_order',
    ];

    protected $casts = [
        'is_numeric' => 'boolean',
        'status' => 'boolean',
    ];
}
