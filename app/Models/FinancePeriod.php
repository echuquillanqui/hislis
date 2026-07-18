<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class FinancePeriod extends Model
{
    use HasFactory;

    protected $fillable = ['branch_id', 'code', 'starts_at', 'ends_at', 'status', 'closed_by', 'closed_at', 'reopened_by', 'reopened_at', 'closing_notes', 'reopening_reason'];

    protected $casts = ['starts_at' => 'date', 'ends_at' => 'date', 'closed_at' => 'datetime', 'reopened_at' => 'datetime'];

    public function movements(): HasMany { return $this->hasMany(FinanceMovement::class); }
    public function receivables(): HasMany { return $this->hasMany(AccountReceivable::class); }
}
