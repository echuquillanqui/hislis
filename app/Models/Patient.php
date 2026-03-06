<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Patient extends Model
{
    use HasFactory;

    protected $fillable = ['dni', 'first_name', 'last_name', 'phone', 'birth_date', 'gender'];

    public function appointments() { return $this->hasMany(Appointment::class); }
    public function vouchers() { return $this->hasMany(Voucher::class); }
    public function triages() { return $this->hasMany(Triage::class); }
}
