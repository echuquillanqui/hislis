<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::withCount('permissions')->orderBy('name')->get();
        return view('admin.roles.index', compact('roles'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:50|unique:roles,name',
            'guard_name' => 'nullable|string|max:50',
        ]);

        Role::create([
            'name' => $validated['name'],
            'guard_name' => $validated['guard_name'] ?? 'web',
        ]);

        return redirect()->route('roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function edit(Role $role)
    {
        // Agrupamos por el prefijo (ej: HIS_... se agrupa en 'HIS')
        $permissions = Permission::all()->groupBy(function($perm) {
            return explode('_', $perm->name)[0]; 
        });
        
        $rolePermissions = $role->permissions->pluck('id')->toArray();
        
        return view('admin.roles.edit', compact('role', 'permissions', 'rolePermissions'));
    }

    public function update(Request $request, Role $role)
    {
        // Validamos que los permisos existan
        $request->validate([
            'permissions' => 'array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        // Convertimos los IDs recibidos en nombres de permisos para mayor seguridad
        $permissions = \Spatie\Permission\Models\Permission::whereIn('id', $request->permissions ?? [])->get();
        
        // Sincronizamos usando la colección de modelos
        $role->syncPermissions($permissions);
        
        return redirect()->route('roles.index')->with('success', 'Permisos del Rol actualizados en bloque.');
    }

    public function destroy(Role $role)
    {
        if ($role->name === 'super-admin') {
            return redirect()->route('roles.index')->with('error', 'El rol super-admin no puede eliminarse.');
        }

        $role->delete();

        return redirect()->route('roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
