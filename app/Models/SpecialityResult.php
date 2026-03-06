<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialityResult extends Model
{
    use HasFactory;

    protected $fillable = ['order_item_id', 'user_id', 'content', 'pdf_path', 'status'];
    protected $casts = ['content' => 'array'];

    public function orderItem() { return $this->belongsTo(OrderItem::class); }
    public function doctor() { return $this->belongsTo(User::class, 'user_id'); }
}
