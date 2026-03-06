<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = ['voucher_id', 'itemable_id', 'itemable_type', 'price', 'status'];

    public function voucher() { return $this->belongsTo(Voucher::class); }
    public function itemable() { return $this->morphTo(); }
    public function labResult() {
    return $this->hasOne(LabResult::class, 'order_item_id');
}
    public function specialityResult() {
    return $this->hasOne(SpecialityResult::class, 'order_item_id');
}
}
