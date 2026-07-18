<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LabOrder extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'patient_id', 'customer_id', 'requesting_physician_id', 'tariff_id', 'created_by', 'code', 'ordered_at', 'status', 'subtotal', 'discount_total', 'total', 'clinical_notes'];

    protected $casts = ['ordered_at' => 'datetime', 'subtotal' => 'decimal:2', 'discount_total' => 'decimal:2', 'total' => 'decimal:2'];

    public function patient(): BelongsTo { return $this->belongsTo(Patient::class); }
    public function tariff(): BelongsTo { return $this->belongsTo(Tariff::class); }
    public function items(): HasMany { return $this->hasMany(LabOrderItem::class); }
    public function samples(): HasMany { return $this->hasMany(LabSample::class); }
    public function cashMovements(): HasMany { return $this->hasMany(CashMovement::class); }
}
