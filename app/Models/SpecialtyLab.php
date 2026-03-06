<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SpecialtyLab extends Model
{
    use HasFactory;

    protected $table = 'specialty_labs'; // Nombre exacto solicitado
    protected $fillable = ['area_id', 'name', 'status'];

    // Relación: Especialidad -> Área (Laboratorio)
    public function area() {
        return $this->belongsTo(Area::class);
    }

    // Relación: Especialidad -> Muchos Exámenes
    public function labExams() {
        return $this->hasMany(LabExam::class, 'specialty_lab_id');
    }
}
