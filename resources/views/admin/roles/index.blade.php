@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary"><i class="fa-solid fa-shield-halved me-2"></i> Roles del Sistema</h4>
    </div>

    <div class="card border-dark shadow">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Nombre del Rol</th>
                        <th>Cant. Permisos</th>
                        <th>Guardia (Guard Name)</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($roles as $role)
                    <tr>
                        <td class="ps-4 fw-bold text-uppercase">{{ $role->name }}</td>
                        <td>
                            <span class="badge bg-info text-dark border border-dark">
                                {{ $role->permissions_count }} permisos asignados
                            </span>
                        </td>
                        <td class="text-muted small">{{ $role->guard_name }}</td>
                        <td class="text-end pe-4">
                            <a href="{{ route('roles.edit', $role->id) }}" class="btn btn-sm btn-outline-dark border-2 fw-bold">
                                <i class="fa-solid fa-lock-open me-1"></i> GESTIONAR PERMISOS
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection