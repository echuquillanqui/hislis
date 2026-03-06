<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabExam extends Model
{
    use HasFactory;

    protected $fillable = ['specialty_lab_id', 'name', 'price', 'unit', 'input_type', 'input_options', 'min_ref', 'max_ref', 'status', 'area_id'];
    protected $casts = ['input_options' => 'array'];

    public function area() { return $this->belongsTo(Area::class); }
    public function specialtyLab() {
        return $this->belongsTo(SpecialtyLab::class, 'specialty_lab_id');
    }
    public function bundleItems() { return $this->morphMany(BundleItem::class, 'itemable'); }
    public function orderItems() { return $this->morphMany(OrderItem::class, 'itemable'); }
}
