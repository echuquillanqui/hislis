@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="userApp()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark"><i class="fa-solid fa-user-tie text-primary me-2"></i> Gestión de Personal</h4>
        <button class="btn btn-primary px-4 shadow border-2" @click="openModal('create')">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Miembro
        </button>
    </div>

    <div class="card border-dark shadow-sm mb-4">
        <div class="card-body bg-light">
            <div class="row g-2">
                <div class="col-md-12">
                    <label class="small fw-bold mb-1 text-muted">Búsqueda Global</label>
                    <div class="input-group">
                        <span class="input-group-text bg-white border-dark"><i class="fa-solid fa-magnifying-glass"></i></span>
                        <input type="text" class="form-control border-dark shadow-sm" 
                               placeholder="Buscar por Nombre, DNI, Email o Usuario..." 
                               x-model="search" @keyup.debounce.500ms="getUsers(1)">
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-dark shadow">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Identidad</th>
                        <th>Acceso / Email</th>
                        <th>Médico (CMP/RNE)</th>
                        <th>Área / Rol</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="user in pagination.data" :key="user.id">
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold" x-text="user.name"></div>
                                <div class="small text-muted" x-text="'DNI: ' + user.dni"></div>
                            </td>
                            <td>
                                <div class="small fw-bold" x-text="'@' + user.username"></div>
                                <div class="small text-muted" x-text="user.email"></div>
                            </td>
                            <td>
                                <div class="small" x-show="user.colegiatura" x-text="'CMP: ' + user.colegiatura"></div>
                                <div class="small" x-show="user.rne" x-text="'RNE: ' + user.rne"></div>
                                <div class="small text-muted" x-show="!user.colegiatura && !user.rne">N/A</div>
                            </td>
                            <td>
                                <div class="badge bg-light text-dark border border-dark" x-text="user.area ? user.area.name : 'S/A'"></div>
                                <div class="small fw-bold text-primary mt-1" x-text="user.roles[0]?.name || 'S/R'"></div>
                            </td>
                            <td class="text-center">
                                <span :class="user.status ? 'badge bg-success border' : 'badge bg-danger border'" 
                                      x-text="user.status ? 'ACTIVO' : 'INACTIVO'"></span>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary border-2" @click="openModal('edit', user)">
                                    <i class="fa-solid fa-pen"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
        <div class="card-footer bg-white border-top border-dark d-flex justify-content-between align-items-center">
            <span class="small text-muted fw-bold" x-text="'Total: ' + pagination.total + ' registros'"></span>
            <div class="btn-group shadow-sm">
                <button class="btn btn-sm btn-outline-dark border-2" :disabled="pagination.current_page === 1" @click="getUsers(pagination.current_page - 1)">Anterior</button>
                <button class="btn btn-sm btn-outline-dark border-2" :disabled="pagination.current_page === pagination.last_page" @click="getUsers(pagination.current_page + 1)">Siguiente</button>
            </div>
        </div>
    </div>

    <div class="modal fade" id="modalUser" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form :action="isEdit ? `{{ url('admin/users') }}/${form.id}` : `{{ route('users.store') }}`" 
                  method="POST" class="modal-content border border-dark border-2 shadow-lg overflow-hidden">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                
                <div class="modal-header bg-dark text-white rounded-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-id-card me-2"></i>
                        <span x-text="isEdit ? 'ACTUALIZAR DATOS DE PERSONAL' : 'REGISTRAR NUEVO PERSONAL'"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="fw-bold small mb-1">Nombre Completo</label>
                            <input type="text" name="name" class="form-control border-dark border-2" x-model="form.name" required>
                        </div>
                        <div class="col-md-4">
                            <label class="fw-bold small mb-1">DNI / Cédula</label>
                            <input type="text" name="dni" class="form-control border-dark border-2" x-model="form.dni" required>
                        </div>

                        <div class="col-md-4">
                            <label class="fw-bold small mb-1">Nombre de Usuario</label>
                            <input type="text" name="username" class="form-control border-dark border-2" x-model="form.username" required>
                        </div>
                        <div class="col-md-8">
                            <label class="fw-bold small mb-1">Correo Electrónico</label>
                            <input type="email" name="email" class="form-control border-dark border-2" x-model="form.email" required>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold small mb-1 text-primary italic">Colegiatura (CMP/CEP)</label>
                            <input type="text" name="colegiatura" class="form-control border-primary border-2" x-model="form.colegiatura">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1 text-primary">RNE (Especialidad)</label>
                            <input type="text" name="rne" class="form-control border-primary border-2" x-model="form.rne">
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Área Asignada</label>
                            <select name="area_id" class="form-select border-dark border-2" x-model="form.area_id">
                                <option value="">--- Seleccione ---</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Rol de Sistema</label>
                            <select name="role" class="form-select border-dark border-2" x-model="form.role" required>
                                <option value="">--- Seleccione ---</option>
                                @foreach($roles as $role)
                                    <option value="{{ $role->name }}">{{ $role->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Contraseña</label>
                            <input type="password" name="password" class="form-control border-dark border-2" 
                                   :placeholder="isEdit ? 'Dejar vacío para mantener' : 'Mínimo 6 caracteres'" :required="!isEdit">
                        </div>
                        <div class="col-md-6">
                            <label class="fw-bold small mb-1">Estado de Cuenta</label>
                            <select name="status" class="form-select border-dark border-2" x-model="form.status">
                                <option value="1">ACTIVO</option>
                                <option value="0">INACTIVO</option>
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer bg-light border-top border-dark rounded-0">
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold border-2">
                        <i class="fa-solid fa-floppy-disk me-2"></i> GUARDAR REGISTRO
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function userApp() {
    return {
        pagination: { data: [] },
        search: '',
        isEdit: false,
        form: { id: '', name: '', dni: '', username: '', email: '', colegiatura: '', rne: '', area_id: '', role: '', status: 1 },
        
        init() { this.getUsers(1); },

        getUsers(page) {
            fetch(`{{ route('users.index') }}?page=${page}&search=${this.search}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                this.pagination = data.users;
            });
        },

        openModal(mode, user = null) {
            this.isEdit = (mode === 'edit');
            if (this.isEdit) {
                this.form = { 
                    ...user, 
                    role: user.roles[0]?.name || '',
                    colegiatura: user.colegiatura || '',
                    rne: user.rne || '',
                    area_id: user.area_id || ''
                };
            } else {
                this.form = { id: '', name: '', dni: '', username: '', email: '', colegiatura: '', rne: '', area_id: '', role: '', status: 1 };
            }
            new bootstrap.Modal(document.getElementById('modalUser')).show();
        }
    }
}
</script>
@endsection