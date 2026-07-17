<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'username', 'dni', 'colegiatura', 'rne', 'area_id', 'signature_path', 'digital_seal', 'status'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function area() { return $this->belongsTo(Area::class); }

    public function branches(): BelongsToMany
    {
        return $this->belongsToMany(Branch::class, 'user_branch_access')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function accessibleAreas(): BelongsToMany
    {
        return $this->belongsToMany(Area::class, 'user_area_access')
            ->withPivot('is_default')
            ->withTimestamps();
    }

    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }
    public function appointments() { return $this->hasMany(Appointment::class); }
    public function vouchers() { return $this->hasMany(Voucher::class); }
    public function specialityResults() { return $this->hasMany(SpecialityResult::class); }
}
