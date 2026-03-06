<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class BundleItem extends Model
{
    use HasFactory;

    protected $fillable = ['bundle_id', 'itemable_id', 'itemable_type'];

    public function bundle() { return $this->belongsTo(Bundle::class); }
    public function itemable() { return $this->morphTo(); }
}
