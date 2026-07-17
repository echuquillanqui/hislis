<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Warehouse extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'area_id', 'status'];

    public function inventories() { return $this->hasMany(Inventory::class); }
    public function movements() { return $this->hasMany(StockMovement::class); }
    public function balances() { return $this->hasMany(InventoryBalance::class); }
    public function kardexEntries() { return $this->hasMany(InventoryKardexEntry::class); }
    public function area() { return $this->belongsTo(Area::class); }
}
