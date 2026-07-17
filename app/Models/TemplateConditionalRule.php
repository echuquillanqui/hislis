<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateConditionalRule extends Model
{
    use HasFactory;

    protected $fillable = ['template_field_id', 'source_field_slug', 'operator', 'comparison_value', 'action'];

    public function field() { return $this->belongsTo(TemplateField::class, 'template_field_id'); }
}
