<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TemplateFieldOption extends Model
{
    use HasFactory;

    protected $fillable = ['template_field_id', 'label', 'value', 'sort_order'];

    public function field() { return $this->belongsTo(TemplateField::class, 'template_field_id'); }
}
