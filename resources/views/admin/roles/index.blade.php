@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-primary"><i class="fa-solid fa-shield-halved me-2"></i> Roles del Sistema</h4>
        <button class="btn btn-primary border-2 fw-bold" data-bs-toggle="modal" data-bs-target="#modalRole">
            <i class="fa-solid fa-plus me-1"></i> NUEVO ROL
        </button>
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
                            @if($role->name !== 'super-admin')
                                <form action="{{ route('roles.destroy', $role->id) }}" method="POST" class="d-inline" onsubmit="return confirm('¿Eliminar este rol?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger border-2 fw-bold">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalRole" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <form action="{{ route('roles.store') }}" method="POST" class="modal-content border-dark border-2">
                @csrf
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-user-shield me-2"></i>Crear rol</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">Nombre del rol</label>
                        <input type="text" name="name" class="form-control border-dark" placeholder="ej: laboratorio, caja, recepcion" required>
                    </div>
                    <div>
                        <label class="form-label fw-bold small">Guard</label>
                        <input type="text" name="guard_name" class="form-control border-dark" value="web">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary fw-bold">GUARDAR</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
