<?php

namespace Tests\Feature;

use App\Models\AnalyticalPrinciple;
use App\Models\AuditLog;
use App\Models\Branch;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Services\AuditLogger;
use Database\Seeders\LisAdministrationSeeder;
use Database\Seeders\LisCatalogSeeder;
use Database\Seeders\LisRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class LisBaseConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_lis_seeders_create_roles_permissions_catalogs_and_access_scope(): void
    {
        $admin = User::create([
            'name' => 'Admin Sistema',
            'username' => 'admin',
            'dni' => '12345678',
            'email' => 'admin@admin.com',
            'password' => Hash::make('admin123'),
            'status' => true,
        ]);

        $this->seed(LisRolesAndPermissionsSeeder::class);
        $this->seed(LisCatalogSeeder::class);
        $this->seed(LisAdministrationSeeder::class);

        $this->assertTrue(Role::where('name', 'super-admin')->exists());
        $this->assertTrue(Permission::where('name', 'audit.view')->exists());
        $this->assertTrue(Branch::where('code', 'PRINCIPAL')->exists());
        $this->assertTrue(AnalyticalPrinciple::where('code', 'ECLIA')->exists());
        $this->assertTrue(PaymentMethod::where('code', 'EFECTIVO')->exists());
        $this->assertTrue($admin->fresh()->branches()->where('code', 'PRINCIPAL')->exists());
        $this->assertTrue($admin->fresh()->hasRole('super-admin'));
    }

    public function test_audit_logger_removes_sensitive_values(): void
    {
        $user = User::create([
            'name' => 'Audit User',
            'username' => 'audit',
            'dni' => '87654321',
            'email' => 'audit@example.test',
            'password' => Hash::make('secret'),
            'status' => true,
        ]);

        $this->actingAs($user);

        app(AuditLogger::class)->log(
            action: 'settings.updated',
            newValues: [
                'currency' => 'PEN',
                'password' => 'plain-secret',
                'nested' => [
                    'token' => 'token-secret',
                    'visible' => 'ok',
                ],
            ],
            reason: 'Prueba de auditoría'
        );

        $audit = AuditLog::firstOrFail();

        $this->assertSame('settings.updated', $audit->action);
        $this->assertSame('PEN', $audit->new_values['currency']);
        $this->assertArrayNotHasKey('password', $audit->new_values);
        $this->assertArrayNotHasKey('token', $audit->new_values['nested']);
        $this->assertSame('ok', $audit->new_values['nested']['visible']);
    }
}
