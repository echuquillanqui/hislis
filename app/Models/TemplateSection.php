<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateSection extends Model
{
    use HasFactory;

    protected $fillable = ['template_version_id', 'title', 'slug', 'sort_order'];

    public function version() { return $this->belongsTo(TemplateVersion::class, 'template_version_id'); }
    public function fields() { return $this->hasMany(TemplateField::class)->orderBy('sort_order'); }
}
