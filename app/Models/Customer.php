<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_type_id',
        'document_type_id',
        'document_number',
        'business_name',
        'commercial_name',
        'contact_name',
        'phone',
        'email',
        'address',
        'allows_credit',
        'credit_limit',
        'payment_due_days',
        'status',
    ];

    protected $casts = [
        'allows_credit' => 'boolean',
        'credit_limit' => 'decimal:2',
        'status' => 'boolean',
    ];

    public function type(): BelongsTo
    {
        return $this->belongsTo(CustomerType::class, 'customer_type_id');
    }

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(CustomerPriceList::class);
    }
}
