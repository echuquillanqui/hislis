<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Branch extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'legal_name',
        'ruc',
        'address',
        'phone',
        'email',
        'is_main',
        'status',
    ];

    protected $casts = [
        'is_main' => 'boolean',
        'status' => 'boolean',
    ];

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_branch_access')
            ->withPivot('is_default')
            ->withTimestamps();
    }
}
