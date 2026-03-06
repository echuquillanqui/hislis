@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="permissionManager()">
    <form action="{{ route('roles.update', $role->id) }}" method="POST">
        @csrf 
        @method('PUT')
        
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4 class="fw-bold text-dark">
                <i class="fa-solid fa-shield-halved text-primary me-2"></i> 
                Permisos del Rol: <span class="text-primary text-uppercase">{{ $role->name }}</span>
            </h4>
            <div>
                <a href="{{ route('roles.index') }}" class="btn btn-outline-secondary border-2 fw-bold me-2">
                    <i class="fa-solid fa-arrow-left"></i> VOLVER
                </a>
                <button type="submit" class="btn btn-success border-2 fw-bold shadow">
                    <i class="fa-solid fa-save"></i> GUARDAR CAMBIOS
                </button>
            </div>
        </div>

        <div class="card border-dark shadow-sm mb-4">
            <div class="card-body bg-light d-flex align-items-center justify-content-between py-2">
                <span class="fw-bold text-muted small uppercase">Acciones Globales</span>
                <div>
                    <button type="button" class="btn btn-sm btn-dark fw-bold border-2" @click="toggleAll(true)">
                        <i class="fa-solid fa-check-double"></i> MARCAR TODO
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-dark fw-bold border-2" @click="toggleAll(false)">
                        <i class="fa-solid fa-xmark"></i> DESMARCAR TODO
                    </button>
                </div>
            </div>
        </div>

        <div class="row">
            @foreach($permissions as $modulo => $lista)
            <div class="col-xl-3 col-lg-4 col-md-6 mb-4">
                <div class="card border-dark h-100 shadow-sm">
                    <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center border-bottom border-dark">
                        <span class="fw-bold small"><i class="fa-solid fa-box"></i> {{ strtoupper($modulo) }}</span>
                        <div class="form-check form-switch mb-0">
                            <input class="form-check-input border-white" type="checkbox" 
                                   @change="toggleModule('{{ $modulo }}', $event.target.checked)">
                        </div>
                    </div>
                    <div class="card-body bg-white p-3">
                        @foreach($lista as $permiso)
                        <div class="form-check mb-2">
                            <input class="form-check-input border-dark permiso-checkbox" 
                                   type="checkbox" 
                                   name="permissions[]" 
                                   value="{{ $permiso->id }}" 
                                   data-module="{{ $modulo }}"
                                   id="p{{ $permiso->id }}"
                                   {{ in_array($permiso->id, $rolePermissions) ? 'checked' : '' }}>
                            <label class="form-check-label small fw-bold text-dark" for="p{{ $permiso->id }}">
                                {{ str_replace($modulo.'_', '', $permiso->name) }}
                            </label>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </form>
</div>

<script>
function permissionManager() {
    return {
        // Seleccionar o deseleccionar todo el rol
        toggleAll(state) {
            document.querySelectorAll('.permiso-checkbox').forEach(el => {
                el.checked = state;
            });
        },
        // Seleccionar o deseleccionar un módulo específico (Bloque)
        toggleModule(module, state) {
            document.querySelectorAll(`.permiso-checkbox[data-module="${module}"]`).forEach(el => {
                el.checked = state;
            });
        }
    }
}
</script>
@endsection