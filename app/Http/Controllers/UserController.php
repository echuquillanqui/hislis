<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Area;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $query = User::with(['roles', 'area']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'LIKE', "%{$request->search}%")
                  ->orWhere('username', 'LIKE', "%{$request->search}%")
                  ->orWhere('dni', 'LIKE', "%{$request->search}%")
                  ->orWhere('email', 'LIKE', "%{$request->search}%");
            });
        }

        $users = $query->orderBy('id', 'desc')->paginate(10);
        $roles = Role::all();
        $areas = Area::where('status', 1)->get();

        if ($request->ajax()) {
            return response()->json([
                'users' => $users,
                'roles' => $roles,
                'areas' => $areas
            ]);
        }

        return view('admin.users.index', compact('roles', 'areas'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|unique:users,username',
            'dni' => 'required|unique:users,dni',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'required',
            'area_id' => 'nullable|exists:areas,id'
        ]);

        DB::transaction(function () use ($request) {
            $user = User::create([
                'name' => $request->name,
                'username' => $request->username,
                'dni' => $request->dni,
                'email' => $request->email,
                'colegiatura' => $request->colegiatura,
                'rne' => $request->rne,
                'password' => Hash::make($request->password),
                'area_id' => $request->area_id,
                'status' => $request->status ?? 1,
            ]);

            $user->assignRole($request->role);
        });

        return back()->with('success', 'Usuario creado exitosamente.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'username' => 'required|unique:users,username,' . $user->id,
            'dni' => 'required|unique:users,dni,' . $user->id,
            'email' => 'required|email|unique:users,email,' . $user->id,
            'role' => 'required',
            'area_id' => 'nullable|exists:areas,id'
        ]);

        $data = $request->only(['name', 'username', 'dni', 'email', 'colegiatura', 'rne', 'area_id', 'status']);

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);
        $user->syncRoles([$request->role]);

        return back()->with('success', 'Usuario actualizado correctamente.');
    }

    public function destroy(User $user)
    {
        // Evitar que el usuario se borre a sí mismo
        if (auth()->id() === $user->id) {
            return back()->with('error', 'No puedes eliminar tu propia cuenta.');
        }

        $user->delete();
        return back()->with('success', 'Usuario eliminado.');
    }
}
