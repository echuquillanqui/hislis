<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CashSession extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'cashier_id', 'code', 'status', 'opening_amount', 'expected_amount', 'counted_amount', 'difference_amount', 'opened_at', 'closed_at', 'opening_notes', 'closing_notes'];

    protected $casts = ['opening_amount' => 'decimal:2', 'expected_amount' => 'decimal:2', 'counted_amount' => 'decimal:2', 'difference_amount' => 'decimal:2', 'opened_at' => 'datetime', 'closed_at' => 'datetime'];

    public function branch(): BelongsTo { return $this->belongsTo(Branch::class); }
    public function cashier(): BelongsTo { return $this->belongsTo(User::class, 'cashier_id'); }
    public function movements(): HasMany { return $this->hasMany(CashMovement::class); }
}
