<?php

namespace Tests\Feature;

use App\Models\Branch;
use App\Models\CashMovement;
use App\Models\Exam;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\Cash\CashRegisterService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class CashRegisterServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_cash_session_registers_partial_and_mixed_order_payments(): void
    {
        [$branch, $cashier, $order, $cash, $card] = $this->cashContext(100);
        $service = app(CashRegisterService::class);

        $session = $service->open(['branch_id' => $branch->id, 'cashier_id' => $cashier->id, 'opening_amount' => 20, 'code' => 'CAJA-001']);
        $service->registerPayment($session, $order, [['payment_method_id' => $cash->id, 'amount' => 40]], $cashier->id);
        $service->registerPayment($session, $order, [
            ['payment_method_id' => $cash->id, 'amount' => 30],
            ['payment_method_id' => $card->id, 'amount' => 30, 'reference' => 'POS-123'],
        ], $cashier->id);

        $this->assertSame('120.00', $session->fresh()->expected_amount);
        $this->assertSame(3, CashMovement::where('type', 'payment')->count());
        $this->assertSame(100.0, (float) CashMovement::where('lab_order_id', $order->id)->sum('amount'));
    }

    public function test_cash_close_calculates_difference_after_expenses_and_compensations(): void
    {
        [$branch, $cashier, $order, $cash] = $this->cashContext(80);
        $service = app(CashRegisterService::class);

        $session = $service->open(['branch_id' => $branch->id, 'cashier_id' => $cashier->id, 'opening_amount' => 50, 'code' => 'CAJA-002']);
        $payment = $service->registerPayment($session, $order, [['payment_method_id' => $cash->id, 'amount' => 80]], $cashier->id)[0];
        $service->registerExpense($session, ['amount' => 10, 'description' => 'Movilidad'], $cashier->id);
        $service->compensate($payment, $cashier->id, 'Anulación por error de registro');
        $closed = $service->close($session, 45, 'Faltante en arqueo');

        $this->assertSame('40.00', $closed->expected_amount);
        $this->assertSame('5.00', $closed->difference_amount);
        $this->assertSame('closed', $closed->status);
        $this->assertDatabaseHas('cash_movements', ['type' => 'compensation', 'compensates_movement_id' => $payment->id]);
    }

    public function test_reference_is_required_for_configured_payment_methods(): void
    {
        [$branch, $cashier, $order, , $card] = $this->cashContext(20);
        $session = app(CashRegisterService::class)->open(['branch_id' => $branch->id, 'cashier_id' => $cashier->id, 'code' => 'CAJA-003']);

        $this->expectException(ValidationException::class);
        app(CashRegisterService::class)->registerPayment($session, $order, [['payment_method_id' => $card->id, 'amount' => 20]], $cashier->id);
    }

    private function cashContext(float $orderTotal): array
    {
        $branch = Branch::create(['code' => uniqid('BR'), 'name' => 'Central', 'status' => true]);
        $cashier = User::factory()->create();
        $patient = Patient::create(['dni' => fake()->unique()->numerify('########'), 'first_name' => 'Ana', 'last_name' => 'Paz', 'phone' => '999', 'birth_date' => '1990-01-01', 'gender' => 'F']);
        $exam = Exam::create(['code' => uniqid('EX'), 'name' => 'Glucosa', 'status' => true]);
        $order = LabOrder::create(['patient_id' => $patient->id, 'code' => uniqid('ORD'), 'ordered_at' => now(), 'status' => 'registered', 'subtotal' => $orderTotal, 'total' => $orderTotal]);
        $order->items()->create(['orderable_type' => Exam::class, 'orderable_id' => $exam->id, 'exam_id' => $exam->id, 'description' => $exam->name, 'unit_price' => $orderTotal, 'total' => $orderTotal]);
        $cash = PaymentMethod::create(['code' => uniqid('CASH'), 'name' => 'Efectivo', 'status' => true]);
        $card = PaymentMethod::create(['code' => uniqid('CARD'), 'name' => 'Tarjeta', 'status' => true, 'requires_reference' => true]);

        return [$branch, $cashier, $order, $cash, $card];
    }
}
