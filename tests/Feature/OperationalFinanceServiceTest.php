<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\Exam;
use App\Models\FinanceCategory;
use App\Models\FinanceMovement;
use App\Models\FinancePeriod;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use App\Services\Finance\OperationalFinanceService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class OperationalFinanceServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_registers_income_and_expense_in_monthly_period(): void
    {
        [$branch, $user] = $this->financeContext();
        $income = FinanceCategory::create(['code' => 'VENTAS', 'name' => 'Ventas', 'type' => 'income']);
        $expense = FinanceCategory::create(['code' => 'SERV', 'name' => 'Servicios', 'type' => 'expense']);
        $service = app(OperationalFinanceService::class);

        $service->registerMovement(['branch_id' => $branch->id, 'finance_category_id' => $income->id, 'type' => 'income', 'amount' => 150, 'occurred_on' => '2026-07-10'], $user->id);
        $service->registerMovement(['branch_id' => $branch->id, 'finance_category_id' => $expense->id, 'type' => 'expense', 'amount' => 40, 'occurred_on' => '2026-07-11'], $user->id);

        $period = FinancePeriod::first();
        $this->assertSame('open', $period->status);
        $this->assertSame(2, FinanceMovement::where('finance_period_id', $period->id)->count());
        $this->assertSame(110.0, (float) FinanceMovement::where('type', 'income')->sum('amount') - (float) FinanceMovement::where('type', 'expense')->sum('amount'));
    }

    public function test_closed_period_blocks_movements_until_authorized_reopen(): void
    {
        [$branch, $user] = $this->financeContext();
        $category = FinanceCategory::create(['code' => 'VENTAS', 'name' => 'Ventas', 'type' => 'income']);
        $service = app(OperationalFinanceService::class);
        $period = $service->openMonthlyPeriod($branch->id, '2026-07-01');
        $service->closePeriod($period, $user->id, 'Cierre mensual');

        try {
            $service->registerMovement(['branch_id' => $branch->id, 'finance_category_id' => $category->id, 'type' => 'income', 'amount' => 10, 'occurred_on' => '2026-07-15'], $user->id);
            $this->fail('Closed periods must reject new movements.');
        } catch (ValidationException $exception) {
            $this->assertArrayHasKey('finance_period', $exception->errors());
        }

        $service->reopenPeriod($period->fresh(), $user->id, 'Corrección autorizada por administración');
        $movement = $service->registerMovement(['branch_id' => $branch->id, 'finance_category_id' => $category->id, 'type' => 'income', 'amount' => 10, 'occurred_on' => '2026-07-15'], $user->id);

        $this->assertSame('open', $period->fresh()->status);
        $this->assertSame('Corrección autorizada por administración', $period->fresh()->reopening_reason);
        $this->assertSame('10.00', $movement->amount);
    }

    public function test_accounts_receivable_tracks_partial_and_full_payments(): void
    {
        [$branch] = $this->financeContext();
        $order = $this->orderFor($branch, 120);
        $service = app(OperationalFinanceService::class);

        $receivable = $service->createReceivableForOrder($order, now()->addDays(15));
        $partial = $service->applyReceivablePayment($receivable, 50);
        $paid = $service->applyReceivablePayment($partial, 70);

        $this->assertSame('partial', $partial->status);
        $this->assertSame('70.00', $partial->balance_amount);
        $this->assertSame('paid', $paid->status);
        $this->assertSame('0.00', $paid->balance_amount);
    }

    private function financeContext(): array
    {
        return [
            Branch::create(['code' => uniqid('BR'), 'name' => 'Central', 'status' => true]),
            User::factory()->create(),
        ];
    }

    private function orderFor(Branch $branch, float $total): LabOrder
    {
        $patient = Patient::create(['dni' => fake()->unique()->numerify('########'), 'first_name' => 'Ana', 'last_name' => 'Paz', 'phone' => '999', 'birth_date' => '1990-01-01', 'gender' => 'F']);
        $exam = Exam::create(['code' => uniqid('EX'), 'name' => 'Glucosa', 'status' => true]);
        $order = LabOrder::create(['branch_id' => $branch->id, 'patient_id' => $patient->id, 'code' => uniqid('ORD'), 'ordered_at' => '2026-07-10', 'status' => 'registered', 'subtotal' => $total, 'total' => $total]);
        $order->items()->create(['orderable_type' => Exam::class, 'orderable_id' => $exam->id, 'exam_id' => $exam->id, 'description' => $exam->name, 'unit_price' => $total, 'total' => $total]);

        return $order;
    }
}
