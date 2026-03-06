<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LabResult extends Model
{
    use HasFactory;

    protected $fillable = ['order_item_id', 'lab_exam_id', 'result_value', 'is_abnormal'];

    public function orderItem() { return $this->belongsTo(OrderItem::class); }
    public function labExam() { return $this->belongsTo(LabExam::class); }
}
