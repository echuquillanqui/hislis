<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\LisRolesAndPermissionsSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class NavigationConfigurationTest extends TestCase
{
    use RefreshDatabase;

    public function test_navigation_items_reference_existing_named_routes(): void
    {
        foreach (config('navigation') as $group) {
            $this->assertNotEmpty($group['children'], "El grupo {$group['title']} debe tener elementos.");

            foreach ($group['children'] as $item) {
                $this->assertTrue(
                    Route::has($item['route']),
                    "La opción {$group['title']} / {$item['title']} referencia una ruta inexistente: {$item['route']}"
                );
            }
        }
    }

    public function test_navigation_hides_permission_protected_items_from_unauthorized_users(): void
    {
        $this->seed(LisRolesAndPermissionsSeeder::class);

        Role::firstOrCreate(['name' => 'consulta', 'guard_name' => 'web']);

        $user = User::create([
            'name' => 'Consulta',
            'username' => 'consulta',
            'dni' => '10000001',
            'email' => 'consulta@example.test',
            'password' => Hash::make('secret'),
            'status' => true,
        ]);
        $user->assignRole('consulta');

        $response = $this->actingAs($user)->get(route('home'));

        $response->assertOk();
        $response->assertSee('Pacientes y clientes');
        $response->assertDontSee('Dashboard gerencial');
        $response->assertDontSee('Resumen de inventario');
        $response->assertDontSee('Roles y permisos');
    }
}
