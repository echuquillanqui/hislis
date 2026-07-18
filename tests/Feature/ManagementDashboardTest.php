<?php

namespace Tests\Feature;

use App\Models\FinanceCategory;
use App\Models\FinanceMovement;
use App\Models\FinancePeriod;
use App\Models\LabOrder;
use App\Models\Patient;
use App\Models\User;
use App\Services\ManagementDashboardService;
use Database\Seeders\LisRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class ManagementDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_management_dashboard_service_calculates_kpis_and_profit(): void
    {
        $patient = Patient::create(['dni' => '70000001', 'first_name' => 'Ana', 'last_name' => 'Paz', 'phone' => '999999999', 'birth_date' => '1990-01-01', 'gender' => 'F']);
        LabOrder::create(['patient_id' => $patient->id, 'code' => 'LIS-2026-0001', 'ordered_at' => '2026-07-10 08:00:00', 'status' => 'registered', 'total' => 150]);

        $period = FinancePeriod::create(['code' => '2026-07', 'starts_at' => '2026-07-01', 'ends_at' => '2026-07-31']);
        $incomeCategory = FinanceCategory::create(['code' => 'ING', 'name' => 'Ingresos', 'type' => 'income']);
        $expenseCategory = FinanceCategory::create(['code' => 'EGR', 'name' => 'Egresos', 'type' => 'expense']);
        FinanceMovement::create(['finance_period_id' => $period->id, 'finance_category_id' => $incomeCategory->id, 'type' => 'income', 'status' => 'confirmed', 'amount' => 200, 'occurred_on' => '2026-07-10']);
        FinanceMovement::create(['finance_period_id' => $period->id, 'finance_category_id' => $expenseCategory->id, 'type' => 'expense', 'status' => 'confirmed', 'amount' => 50, 'occurred_on' => '2026-07-10']);

        $metrics = app(ManagementDashboardService::class)->metrics('2026-07-01', '2026-07-31');

        $this->assertSame(1, $metrics['kpis']['orders_count']);
        $this->assertSame(150.0, $metrics['kpis']['orders_total']);
        $this->assertSame(150.0, $metrics['kpis']['profit']);
        $this->assertSame(1, $metrics['orders_by_status']['registered']);
    }

    public function test_management_dashboard_requires_permission_and_exports_csv(): void
    {
        $this->seed(LisRolesAndPermissionsSeeder::class);
        $user = User::factory()->create(['username' => 'gerencia']);
        $user->assignRole('gerencia');

        $this->actingAs($user)
            ->get(route('dashboard.management.index'))
            ->assertOk()
            ->assertSee('Dashboard gerencial');

        $this->actingAs($user)
            ->get(route('dashboard.management.export'))
            ->assertOk()
            ->assertHeader('Content-Type', 'text/csv; charset=UTF-8')
            ->assertSee('orders_count');

        $restricted = User::factory()->create(['username' => 'consulta']);
        Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);
        Permission::firstOrCreate(['name' => 'dashboard.management.view', 'guard_name' => 'web']);
        $restricted->assignRole('consulta');

        $this->actingAs($restricted)
            ->get(route('dashboard.management.index'))
            ->assertForbidden();
    }
}
