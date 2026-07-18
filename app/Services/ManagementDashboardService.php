<?php

namespace App\Services;

use App\Models\CashMovement;
use App\Models\FinanceMovement;
use App\Models\InventoryBalance;
use App\Models\LabOrder;
use App\Models\MonthlyInventoryCount;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class ManagementDashboardService
{
    public function metrics(?string $from = null, ?string $to = null): array
    {
        [$start, $end] = $this->period($from, $to);

        $orders = LabOrder::query()->whereBetween('ordered_at', [$start, $end]);
        $income = FinanceMovement::query()->where('type', 'income')->where('status', 'confirmed')->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $expenses = FinanceMovement::query()->where('type', 'expense')->where('status', 'confirmed')->whereBetween('occurred_on', [$start->toDateString(), $end->toDateString()])->sum('amount');
        $cash = CashMovement::query()->where('status', 'confirmed')->whereBetween('occurred_at', [$start, $end]);

        return [
            'period' => ['from' => $start->toDateString(), 'to' => $end->toDateString()],
            'kpis' => [
                'orders_count' => (clone $orders)->count(),
                'orders_total' => (float) (clone $orders)->sum('total'),
                'average_ticket' => (float) ((clone $orders)->avg('total') ?? 0),
                'cash_collected' => (float) (clone $cash)->whereIn('type', ['payment', 'income'])->sum('amount'),
                'operational_income' => (float) $income,
                'operational_expenses' => (float) $expenses,
                'profit' => (float) $income - (float) $expenses,
                'open_inventory_counts' => MonthlyInventoryCount::whereIn('status', ['draft', 'counted'])->count(),
                'low_stock_products' => InventoryBalance::query()
                    ->join('products', 'products.id', '=', 'inventory_balances.product_id')
                    ->whereColumn('inventory_balances.quantity', '<=', 'products.min_stock')
                    ->count(),
            ],
            'orders_by_status' => $this->pluckCounts((clone $orders)->selectRaw('status, count(*) as aggregate')->groupBy('status')->pluck('aggregate', 'status')),
            'income_vs_expenses' => [
                'income' => (float) $income,
                'expenses' => (float) $expenses,
            ],
            'alerts' => $this->alerts(),
        ];
    }

    public function exportCsv(array $metrics): string
    {
        $rows = [["Indicador", "Valor"]];
        foreach ($metrics['kpis'] as $key => $value) {
            $rows[] = [$key, (string) $value];
        }

        return collect($rows)->map(fn (array $row) => implode(',', array_map(fn ($value) => '"'.str_replace('"', '""', $value).'"', $row)))->implode("\n")."\n";
    }

    private function period(?string $from, ?string $to): array
    {
        $start = $from ? Carbon::parse($from)->startOfDay() : now()->startOfMonth();
        $end = $to ? Carbon::parse($to)->endOfDay() : now()->endOfDay();

        return [$start, $end];
    }

    private function pluckCounts(Collection $counts): array
    {
        return $counts->map(fn ($value) => (int) $value)->all();
    }

    private function alerts(): array
    {
        return [
            'low_stock' => InventoryBalance::query()
                ->join('products', 'products.id', '=', 'inventory_balances.product_id')
                ->whereColumn('inventory_balances.quantity', '<=', 'products.min_stock')
                ->orderBy('products.name')
                ->limit(5)
                ->pluck('products.name')
                ->all(),
            'inventory_counts_pending_close' => MonthlyInventoryCount::whereIn('status', ['draft', 'counted'])->count(),
        ];
    }
}
