<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Bundle extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'description', 'price', 'status'];

    public function items() { return $this->hasMany(BundleItem::class); }

    
}
