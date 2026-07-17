<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateVersion extends Model
{
    use HasFactory;

    protected $fillable = ['template_id', 'version_number', 'status', 'schema_snapshot', 'published_at', 'published_by'];
    protected $casts = ['schema_snapshot' => 'array', 'published_at' => 'datetime'];

    public function template() { return $this->belongsTo(Template::class); }
    public function sections() { return $this->hasMany(TemplateSection::class)->orderBy('sort_order'); }
}
