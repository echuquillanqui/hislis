<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = ['patient_id', 'user_id', 'service_id', 'date_time', 'status'];

    public function patient() { return $this->belongsTo(Patient::class); }
    public function doctor() { return $this->belongsTo(User::class, 'user_id'); }
    public function service() { return $this->belongsTo(Service::class); }
}
