<?php

namespace App\Http\Controllers;

use App\Models\InventoryBalance;
use App\Models\InventoryCategory;
use App\Models\InventoryKardexEntry;
use App\Models\InventoryLot;
use App\Models\MonthlyInventoryCount;
use App\Models\Product;
use App\Models\Warehouse;

class InventoryDashboardController extends Controller
{
    public function index()
    {
        return view('admin.inventory.index', [
            'productsCount' => Product::count(),
            'categoriesCount' => InventoryCategory::count(),
            'warehousesCount' => Warehouse::count(),
            'lotsCount' => InventoryLot::count(),
            'balancesCount' => InventoryBalance::count(),
            'kardexCount' => InventoryKardexEntry::count(),
            'monthlyCountsCount' => MonthlyInventoryCount::count(),
        ]);
    }
}
