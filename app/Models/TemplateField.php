<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateField extends Model
{
    use HasFactory;

    protected $fillable = ['template_section_id', 'label', 'slug', 'type', 'column', 'required', 'unit', 'placeholder', 'sort_order'];
    protected $casts = ['required' => 'boolean'];

    public function section() { return $this->belongsTo(TemplateSection::class, 'template_section_id'); }
    public function options() { return $this->hasMany(TemplateFieldOption::class)->orderBy('sort_order'); }
    public function rules() { return $this->hasMany(TemplateConditionalRule::class); }
}
