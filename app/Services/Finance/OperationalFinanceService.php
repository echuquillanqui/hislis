<?php

namespace App\Services\Finance;

use App\Models\AccountReceivable;
use App\Models\FinanceCategory;
use App\Models\FinanceMovement;
use App\Models\FinancePeriod;
use App\Models\LabOrder;
use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class OperationalFinanceService
{
    public function openMonthlyPeriod(int|string|null $branchId, CarbonInterface|string $month): FinancePeriod
    {
        $date = Carbon::parse($month)->startOfMonth();
        return FinancePeriod::firstOrCreate(
            ['branch_id' => $branchId, 'starts_at' => $date->toDateString(), 'ends_at' => $date->copy()->endOfMonth()->toDateString()],
            ['code' => ($branchId ?: 'GLOBAL').'-'.$date->format('Ym'), 'status' => 'open']
        );
    }

    public function registerMovement(array $data, ?int $userId = null): FinanceMovement
    {
        return DB::transaction(function () use ($data, $userId) {
            $period = $this->periodFor($data['branch_id'] ?? null, $data['occurred_on'] ?? now());
            $this->ensureOpen($period);
            $category = FinanceCategory::findOrFail($data['finance_category_id']);
            if ($category->type !== $data['type']) {
                throw ValidationException::withMessages(['finance_category_id' => 'La categoría no corresponde al tipo de movimiento.']);
            }

            return FinanceMovement::create($data + [
                'finance_period_id' => $period->id,
                'created_by' => $userId,
                'status' => 'confirmed',
            ]);
        });
    }

    public function createReceivableForOrder(LabOrder $order, ?CarbonInterface $dueOn = null): AccountReceivable
    {
        return DB::transaction(function () use ($order, $dueOn) {
            $period = $this->periodFor($order->branch_id, $order->ordered_at ?? now());
            $this->ensureOpen($period);

            return AccountReceivable::create([
                'finance_period_id' => $period->id,
                'branch_id' => $order->branch_id,
                'lab_order_id' => $order->id,
                'patient_id' => $order->patient_id,
                'customer_id' => $order->customer_id,
                'original_amount' => $order->total,
                'balance_amount' => $order->total,
                'issued_on' => ($order->ordered_at ?? now())->toDateString(),
                'due_on' => $dueOn?->toDateString(),
            ]);
        });
    }

    public function applyReceivablePayment(AccountReceivable $receivable, float $amount): AccountReceivable
    {
        return DB::transaction(function () use ($receivable, $amount) {
            $receivable = AccountReceivable::whereKey($receivable->id)->lockForUpdate()->firstOrFail();
            $this->ensureOpen($receivable->period()->lockForUpdate()->first());
            if ($amount <= 0 || $amount > (float) $receivable->balance_amount + 0.00001) {
                throw ValidationException::withMessages(['amount' => 'El abono debe ser mayor a cero y no superar el saldo.']);
            }
            $paid = (float) $receivable->paid_amount + $amount;
            $balance = max(0, (float) $receivable->original_amount - $paid);
            $receivable->update(['paid_amount' => $paid, 'balance_amount' => $balance, 'status' => $balance <= 0.00001 ? 'paid' : 'partial']);
            return $receivable->fresh();
        });
    }

    public function closePeriod(FinancePeriod $period, int $userId, ?string $notes = null): FinancePeriod
    {
        $period->update(['status' => 'closed', 'closed_by' => $userId, 'closed_at' => now(), 'closing_notes' => $notes]);
        return $period->fresh();
    }

    public function reopenPeriod(FinancePeriod $period, int $userId, string $reason): FinancePeriod
    {
        $period->update(['status' => 'open', 'reopened_by' => $userId, 'reopened_at' => now(), 'reopening_reason' => $reason]);
        return $period->fresh();
    }

    private function periodFor(int|string|null $branchId, CarbonInterface|string $date): FinancePeriod
    {
        $date = Carbon::parse($date);
        return FinancePeriod::where('branch_id', $branchId)->whereDate('starts_at', '<=', $date)->whereDate('ends_at', '>=', $date)->lockForUpdate()->first()
            ?? $this->openMonthlyPeriod($branchId, $date);
    }

    private function ensureOpen(FinancePeriod $period): void
    {
        if ($period->status !== 'open') {
            throw ValidationException::withMessages(['finance_period' => 'El periodo financiero está cerrado.']);
        }
    }
}
