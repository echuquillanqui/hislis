<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RequestingPhysician extends Model
{
    use HasFactory;

    protected $fillable = [
        'document_type_id',
        'physician_specialty_id',
        'document_number',
        'first_name',
        'last_name',
        'license_number',
        'phone',
        'email',
        'status',
    ];

    protected $casts = [
        'status' => 'boolean',
    ];

    public function documentType(): BelongsTo
    {
        return $this->belongsTo(DocumentType::class);
    }

    public function specialty(): BelongsTo
    {
        return $this->belongsTo(PhysicianSpecialty::class, 'physician_specialty_id');
    }
}
