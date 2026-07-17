<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Area extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'status', 'is_medical', 'parent_id', 'template_id'];

    public function users() { return $this->hasMany(User::class); }

    public function authorizedUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'user_area_access')
            ->withPivot('is_default')
            ->withTimestamps();
    }
    public function services() { return $this->hasMany(Service::class); }
    public function labExams() { return $this->hasMany(LabExam::class); }
    public function parent() { return $this->belongsTo(Area::class, 'parent_id'); }
    public function subareas() { return $this->hasMany(Area::class, 'parent_id'); }
    public function defaultTemplate() { return $this->belongsTo(Template::class, 'template_id'); }
    public function template() { 
        return $this->belongsTo(Template::class); 
    }

    public function scopeMedical($query)
    {
        return $query->where('is_medical', true)->where('status', true);
    }
}
