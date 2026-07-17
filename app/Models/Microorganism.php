<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Microorganism extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'gram_stain', 'group', 'status'];
    protected $casts = ['status' => 'boolean'];

    public function isolates(): HasMany { return $this->hasMany(MicrobiologyIsolate::class); }
}
