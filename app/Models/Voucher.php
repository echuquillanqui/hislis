<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;


class Voucher extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'user_id', 'type', 'series', 'number', 'total', 'status'];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function cashier() { return $this->belongsTo(User::class, 'user_id'); }
    public function items() { return $this->hasMany(OrderItem::class); }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }
}
