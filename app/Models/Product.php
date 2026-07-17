<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description', 'category', 'inventory_category_id', 'measurement_unit_id', 'is_lot_controlled', 'requires_fefo', 'min_stock'];

    public function inventories() { return $this->hasMany(Inventory::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }
    public function lots() { return $this->hasMany(InventoryLot::class); }
    public function balances() { return $this->hasMany(InventoryBalance::class); }
    public function kardexEntries() { return $this->hasMany(InventoryKardexEntry::class); }
    public function inventoryCategory() { return $this->belongsTo(InventoryCategory::class); }
    public function measurementUnit() { return $this->belongsTo(MeasurementUnit::class); }
}
