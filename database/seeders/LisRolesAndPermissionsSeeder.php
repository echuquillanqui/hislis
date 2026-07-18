<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Models\Role;

class LisRolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'super-admin' => 'Superadministrador',
            'administrador' => 'Administrador',
            'recepcion' => 'Recepción',
            'caja' => 'Caja',
            'flebotomista' => 'Flebotomista',
            'tecnico-laboratorio' => 'Técnico de laboratorio',
            'responsable-area' => 'Responsable de área',
            'profesional-validador' => 'Profesional validador',
            'microbiologia' => 'Microbiología',
            'almacen' => 'Almacén',
            'compras' => 'Compras',
            'contabilidad' => 'Contabilidad',
            'gerencia' => 'Gerencia',
            'auditor' => 'Auditor',
            'consulta' => 'Usuario de consulta',
        ];

        $permissions = [
            'patients.create',
            'patients.edit',
            'orders.create',
            'payments.register',
            'cash.open',
            'cash.close',
            'samples.receive',
            'samples.reject',
            'results.enter',
            'results.validate',
            'results.approve',
            'reports.correct',
            'templates.create',
            'templates.publish',
            'methods.manage',
            'inventory.manage',
            'inventory.entries.register',
            'inventory.exits.register',
            'inventory.adjustments.create',
            'inventory.adjustments.approve',
            'costs.view',
            'finance.view',
            'finance.monthly-close',
            'dashboard.management.view',
            'dashboard.management.export',
            'audit.view',
            'branches.manage',
            'areas.manage',
            'catalogs.manage',
            'users.manage',
            'roles.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate([
                'name' => $permission,
                'guard_name' => 'web',
            ]);
        }

        foreach ($roles as $name => $label) {
            $role = Role::firstOrCreate([
                'name' => $name,
                'guard_name' => 'web',
            ]);

            if ($name === 'super-admin') {
                $role->syncPermissions(Permission::all());
            }

            if (in_array($name, ['administrador', 'gerencia', 'auditor'], true)) {
                $role->givePermissionTo(['dashboard.management.view', 'dashboard.management.export']);
            }
        }
    }
}
