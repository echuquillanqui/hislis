@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="specialtyApp()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">
            <i class="fa-solid fa-microscope text-primary me-2"></i> Especialidades de Laboratorio
        </h4>
        <button class="btn btn-primary px-4 shadow-sm border-2" @click="openModal('create')">
            <i class="fa-solid fa-plus me-1"></i> Nueva Especialidad
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-white rounded-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-light" placeholder="Buscar especialidad..." x-model="search">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <table class="table table-hover align-middle mb-0">
            <thead class="bg-primary text-white">
                <tr>
                    <th class="ps-4 py-3">Nombre de Especialidad</th>
                    <th>Área General</th>
                    <th>Estado</th>
                    <th class="text-end pe-4">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <template x-for="s in filteredSpecialties" :key="s.id">
                    <tr class="border-bottom">
                        <td class="ps-4 fw-bold text-dark" x-text="s.name"></td>
                        <td>
                            <span class="badge bg-light text-primary border border-primary-subtle" x-text="s.area.name"></span>
                        </td>
                        <td>
                            <span :class="s.status ? 'badge bg-success' : 'badge bg-danger'" x-text="s.status ? 'Activo' : 'Inactivo'"></span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-outline-warning" @click="openModal('edit', s)">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-danger" @click="confirmDelete(s.id)">
                                    <i class="fa-solid fa-trash"></i>
                                </button>
                            </div>
                            <form :id="'delete-form-' + s.id" :action="'/admin/specialty_labs/' + s.id" method="POST" class="d-none">
                                @csrf @method('DELETE')
                            </form>
                        </td>
                    </tr>
                </template>
            </tbody>
        </table>
    </div>

    <div class="modal fade" id="modalSpecialty" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg rounded-4">
                <form :action="isEdit ? `/admin/specialty_labs/${form.id}` : '{{ route('specialty_labs.store') }}'" method="POST">
                    @csrf
                    <template x-if="isEdit">
                        @method('PUT')
                    </template>

                    <div class="modal-header bg-light border-0">
                        <h5 class="fw-bold mb-0" x-text="isEdit ? 'Editar Especialidad' : 'Nueva Especialidad'"></h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Nombre de la Especialidad Técnica</label>
                            <input type="text" name="name" x-model="form.name" class="form-control border-2" placeholder="Ej: Bioquímica" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold small text-muted">Área de Dependencia</label>
                            <select name="area_id" x-model="form.area_id" class="form-select border-2" required>
                                <option value="">Seleccione el área principal...</option>
                                @foreach($areas as $area)
                                    <option value="{{ $area->id }}">{{ $area->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" name="status" value="1" x-model="form.status">
                            <label class="form-check-label small fw-bold">Especialidad Activa</label>
                        </div>
                    </div>
                    <div class="modal-footer border-0 p-4 pt-0">
                        <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-primary rounded-pill px-4 fw-bold">Guardar Cambios</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
function specialtyApp() {
    return {
        specialties: @json($specialties),
        search: '',
        isEdit: false,
        form: { id: '', name: '', area_id: '', status: true },

        get filteredSpecialties() {
            return this.specialties.filter(s => 
                s.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        openModal(mode, s = null) {
            this.isEdit = mode === 'edit';
            if (this.isEdit) {
                this.form = { ...s };
            } else {
                this.form = { id: '', name: '', area_id: '', status: true };
            }
            new bootstrap.Modal(document.getElementById('modalSpecialty')).show();
        },

        confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "No podrás revertir esto si la especialidad tiene datos asociados.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            })
        }
    }
}
</script>
@endsection