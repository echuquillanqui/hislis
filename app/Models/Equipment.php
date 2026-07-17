<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Equipment extends Model
{
    use HasFactory;

    protected $table = 'equipment';

    protected $fillable = ['manufacturer_id', 'code', 'name', 'model', 'serial_number', 'status'];

    protected $casts = ['status' => 'boolean'];

    public function manufacturer(): BelongsTo
    {
        return $this->belongsTo(Manufacturer::class);
    }

    public function examMethods(): HasMany
    {
        return $this->hasMany(ExamMethod::class);
    }
}
