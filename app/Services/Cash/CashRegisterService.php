<?php

namespace App\Services\Cash;

use App\Models\CashMovement;
use App\Models\CashSession;
use App\Models\LabOrder;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class CashRegisterService
{
    public function open(array $data): CashSession
    {
        return DB::transaction(function () use ($data) {
            if (CashSession::where('branch_id', $data['branch_id'] ?? null)->where('cashier_id', $data['cashier_id'] ?? null)->where('status', 'open')->lockForUpdate()->exists()) {
                throw ValidationException::withMessages(['cash_session' => 'El cajero ya tiene una caja abierta en esta sede.']);
            }

            return CashSession::create([
                'branch_id' => $data['branch_id'] ?? null,
                'cashier_id' => $data['cashier_id'] ?? null,
                'code' => $data['code'] ?? 'CAJA-'.now()->format('Ymd-His'),
                'opening_amount' => $data['opening_amount'] ?? 0,
                'expected_amount' => $data['opening_amount'] ?? 0,
                'opened_at' => $data['opened_at'] ?? now(),
                'opening_notes' => $data['opening_notes'] ?? null,
            ]);
        });
    }

    public function registerPayment(CashSession $session, LabOrder $order, array $payments, ?int $userId = null): array
    {
        return DB::transaction(function () use ($session, $order, $payments, $userId) {
            $session = CashSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if ($session->status !== 'open') {
                throw ValidationException::withMessages(['cash_session' => 'La caja no está abierta.']);
            }

            $paid = (float) CashMovement::where('lab_order_id', $order->id)->where('type', 'payment')->where('status', 'confirmed')->sum('amount');
            $remaining = max(0, (float) $order->total - $paid);
            $totalPayment = collect($payments)->sum(fn ($payment) => (float) $payment['amount']);
            if ($totalPayment <= 0 || $totalPayment > $remaining + 0.00001) {
                throw ValidationException::withMessages(['amount' => 'El pago debe ser mayor a cero y no superar el saldo de la orden.']);
            }

            $movements = [];
            foreach ($payments as $payment) {
                $method = PaymentMethod::findOrFail($payment['payment_method_id']);
                if ($method->requires_reference && empty($payment['reference'])) {
                    throw ValidationException::withMessages(['reference' => 'El método de pago requiere referencia.']);
                }
                $movements[] = CashMovement::create([
                    'cash_session_id' => $session->id,
                    'lab_order_id' => $order->id,
                    'payment_method_id' => $method->id,
                    'created_by' => $userId,
                    'type' => 'payment',
                    'amount' => $payment['amount'],
                    'reference' => $payment['reference'] ?? null,
                    'description' => $payment['description'] ?? 'Cobro de orden '.$order->code,
                    'occurred_at' => $payment['occurred_at'] ?? now(),
                ]);
            }

            $this->refreshExpectedAmount($session);
            return $movements;
        });
    }

    public function registerExpense(CashSession $session, array $data, ?int $userId = null): CashMovement
    {
        return DB::transaction(function () use ($session, $data, $userId) {
            $session = CashSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            if ($session->status !== 'open') throw ValidationException::withMessages(['cash_session' => 'La caja no está abierta.']);
            $movement = CashMovement::create(['cash_session_id' => $session->id, 'created_by' => $userId, 'type' => 'expense', 'amount' => $data['amount'], 'reference' => $data['reference'] ?? null, 'description' => $data['description'] ?? null, 'occurred_at' => $data['occurred_at'] ?? now()]);
            $this->refreshExpectedAmount($session);
            return $movement;
        });
    }

    public function compensate(CashMovement $movement, ?int $userId = null, ?string $description = null): CashMovement
    {
        return DB::transaction(function () use ($movement, $userId, $description) {
            $session = CashSession::whereKey($movement->cash_session_id)->lockForUpdate()->firstOrFail();
            if ($session->status !== 'open') throw ValidationException::withMessages(['cash_session' => 'La caja no está abierta.']);
            $compensation = CashMovement::create($movement->only(['cash_session_id', 'lab_order_id', 'payment_method_id']) + ['created_by' => $userId, 'compensates_movement_id' => $movement->id, 'type' => 'compensation', 'amount' => -1 * (float) $movement->amount, 'reference' => $movement->reference, 'description' => $description ?? 'Movimiento compensatorio', 'occurred_at' => now()]);
            $this->refreshExpectedAmount($session);
            return $compensation;
        });
    }

    public function close(CashSession $session, float $countedAmount, ?string $notes = null): CashSession
    {
        return DB::transaction(function () use ($session, $countedAmount, $notes) {
            $session = CashSession::whereKey($session->id)->lockForUpdate()->firstOrFail();
            $this->refreshExpectedAmount($session);
            $session->update(['status' => 'closed', 'counted_amount' => $countedAmount, 'difference_amount' => $countedAmount - (float) $session->expected_amount, 'closed_at' => now(), 'closing_notes' => $notes]);
            return $session->fresh();
        });
    }

    private function refreshExpectedAmount(CashSession $session): void
    {
        $income = CashMovement::where('cash_session_id', $session->id)->whereIn('type', ['payment', 'income', 'compensation'])->where('status', 'confirmed')->sum('amount');
        $expenses = CashMovement::where('cash_session_id', $session->id)->where('type', 'expense')->where('status', 'confirmed')->sum('amount');
        $session->update(['expected_amount' => (float) $session->opening_amount + (float) $income - (float) $expenses]);
    }
}
