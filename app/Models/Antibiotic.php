<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Antibiotic extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'family', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function susceptibilities(): HasMany { return $this->hasMany(AntibioticSusceptibility::class); }
}
