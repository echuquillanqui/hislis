<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Service extends Model
{
    use HasFactory;

    protected $fillable = ['area_id', 'template_id', 'name', 'price', 'status'];

    public function area() { return $this->belongsTo(Area::class); }
    public function template() { return $this->belongsTo(Template::class); }
    public function bundleItems() { return $this->morphMany(BundleItem::class, 'itemable'); }
    public function orderItems() { return $this->morphMany(OrderItem::class, 'itemable'); }

    public function getActiveTemplate()
    {
        // 1. Prioridad: ¿El servicio tiene template propio?
        if ($this->template_id) return $this->template;

        // 2. ¿La sub-área tiene template?
        if ($this->area->template_id) return $this->area->defaultTemplate;

        // 3. ¿El área padre tiene template?
        if ($this->area->parent && $this->area->parent->template_id) {
            return $this->area->parent->defaultTemplate;
        }

        return null;
    }
}
