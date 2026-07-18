<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CashMovement extends Model
{
    use HasFactory;

    protected $fillable = ['cash_session_id', 'lab_order_id', 'payment_method_id', 'created_by', 'compensates_movement_id', 'type', 'status', 'amount', 'reference', 'description', 'occurred_at'];

    protected $casts = ['amount' => 'decimal:2', 'occurred_at' => 'datetime'];

    public function session(): BelongsTo { return $this->belongsTo(CashSession::class, 'cash_session_id'); }
    public function labOrder(): BelongsTo { return $this->belongsTo(LabOrder::class); }
    public function paymentMethod(): BelongsTo { return $this->belongsTo(PaymentMethod::class); }
    public function compensates(): BelongsTo { return $this->belongsTo(CashMovement::class, 'compensates_movement_id'); }
}
