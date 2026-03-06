@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="areaApp()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark"><i class="fa-solid fa-layer-group text-primary me-2"></i> Gestión de Áreas y Unidades</h4>
        <button class="btn btn-primary px-4 shadow border-2" @click="openModal('create')">
            <i class="fa-solid fa-plus me-1"></i> Nueva Área
        </button>
    </div>

    <div class="card border-dark shadow-sm mb-4">
        <div class="card-body bg-light">
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="small fw-bold mb-1">Buscar por nombre</label>
                    <input type="text" class="form-control border-dark shadow-sm" placeholder="Ej. Ginecología..." x-model="filters.name" @keyup.debounce.300ms="getAreas(1)">
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold mb-1">Tipo de Área</label>
                    <select class="form-select border-dark shadow-sm" x-model="filters.is_medical" @change="getAreas(1)">
                        <option value="">Todos los tipos</option>
                        <option value="1">Médicas (Asistencial)</option>
                        <option value="0">Administrativas</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold mb-1">Estado</label>
                    <select class="form-select border-dark shadow-sm" x-model="filters.status" @change="getAreas(1)">
                        <option value="">Todos los estados</option>
                        <option value="1">Activos</option>
                        <option value="0">Inactivos</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-dark shadow-sm">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">Nombre del Área</th>
                        <th>Tipo de Unidad</th>
                        <th>Slug / Ruta</th>
                        <th class="text-center">Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="area in pagination.data" :key="area.id">
                        <tr>
                            <td class="ps-4 fw-bold text-dark text-uppercase" x-text="area.name"></td>
                            <td>
                                <span :class="area.is_medical ? 'badge bg-primary text-white border border-dark' : 'badge bg-secondary text-white'" 
                                      x-text="area.is_medical ? 'MÉDICA / ASISTENCIAL' : 'ADMINISTRATIVA'"></span>
                            </td>
                            <td class="text-muted small" x-text="'/' + area.slug"></td>
                            <td class="text-center">
                                <span :class="area.status ? 'badge bg-success border border-dark' : 'badge bg-danger border border-dark'" 
                                      x-text="area.status ? 'ACTIVO' : 'INACTIVO'"></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm">
                                    <button class="btn btn-sm btn-outline-dark border-2" @click="openModal('edit', area)">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger border-2" @click="deleteArea(area.id, area.name)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>

        <div class="card-footer d-flex justify-content-between align-items-center bg-white border-top border-dark">
            <span class="small text-muted fw-bold">
                Mostrando <span x-text="pagination.from || 0"></span> - <span x-text="pagination.to || 0"></span> de <span x-text="pagination.total"></span> registros
            </span>
            <nav>
                <ul class="pagination pagination-sm mb-0">
                    <li class="page-item" :class="pagination.current_page === 1 ? 'disabled' : ''">
                        <button class="page-link border-dark text-dark" @click="getAreas(pagination.current_page - 1)">«</button>
                    </li>
                    <li class="page-item" :class="pagination.current_page === pagination.last_page ? 'disabled' : ''">
                        <button class="page-link border-dark text-dark" @click="getAreas(pagination.current_page + 1)">»</button>
                    </li>
                </ul>
            </nav>
        </div>
    </div>

    <div class="modal fade" id="modalArea" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <form :action="isEdit ? `{{ url('admin/areas') }}/${form.id}` : `{{ route('areas.store') }}`" method="POST" class="modal-content border border-dark border-2 shadow-lg">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                
                <div class="modal-header bg-dark text-white rounded-0">
                    <h5 class="modal-title fw-bold" x-text="isEdit ? 'ACTUALIZAR ÁREA' : 'REGISTRAR ÁREA'"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                
                <div class="modal-body p-4 bg-white">
                    <div class="mb-3">
                        <label class="form-label fw-bold small">NOMBRE DE LA UNIDAD</label>
                        <input type="text" name="name" class="form-control border-dark border-2" x-model="form.name" required placeholder="Ej. Ginecología">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold small">TIPO DE ÁREA / FUNCIÓN</label>
                        <select name="is_medical" class="form-select border-dark border-2" x-model="form.is_medical" required>
                            <option value="1">Atención Médica (Genera historial clínico)</option>
                            <option value="0">Administrativa / Otros (No genera historial)</option>
                        </select>
                        <div class="form-text text-primary small fw-bold">Solo las áreas médicas aparecerán en el monitor de atenciones.</div>
                    </div>
                    
                    <template x-if="isEdit">
                        <div class="mb-0">
                            <label class="form-label fw-bold small">ESTADO OPERATIVO</label>
                            <select name="status" class="form-select border-dark border-2" x-model="form.status">
                                <option value="1">Activo / Operativo</option>
                                <option value="0">Inactivo / Fuera de servicio</option>
                            </select>
                        </div>
                    </template>
                </div>
                
                <div class="modal-footer bg-light border-top border-dark rounded-0">
                    <button type="submit" class="btn btn-dark w-100 py-2 fw-bold shadow">
                        <i class="fa-solid fa-save me-2"></i> GUARDAR CAMBIOS
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function areaApp() {
    return {
        pagination: { data: [] },
        filters: { name: '', status: '', is_medical: '' },
        isEdit: false,
        form: { id: '', name: '', status: 1, is_medical: 1 },
        
        init() { this.getAreas(1); },
        
        getAreas(page) {
            let params = new URLSearchParams({
                page: page,
                name: this.filters.name,
                status: this.filters.status,
                is_medical: this.filters.is_medical
            });
            
            fetch(`{{ route('areas.index') }}?${params.toString()}`, { 
                headers: { 'X-Requested-With': 'XMLHttpRequest' } 
            })
            .then(res => res.json())
            .then(data => this.pagination = data);
        },
        
        openModal(mode, area = null) {
            this.isEdit = (mode === 'edit');
            // Al crear, forzamos valores por defecto; al editar, cargamos el objeto
            this.form = this.isEdit ? { ...area } : { id: '', name: '', status: 1, is_medical: 1 };
            
            // Forzar a entero para que los selectores lo reconozcan si vienen como string o bool
            this.form.is_medical = this.form.is_medical ? 1 : 0;
            this.form.status = this.form.status ? 1 : 0;

            let myModal = new bootstrap.Modal(document.getElementById('modalArea'));
            myModal.show();
        },
        
        deleteArea(id, name) {
            if (confirm(`¿Está seguro de eliminar el área "${name}"? El sistema no permitirá borrarla si tiene personal vinculado.`)) {
                let f = document.createElement('form');
                f.action = `{{ url('admin/areas') }}/${id}`;
                f.method = 'POST';
                f.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(f);
                f.submit();
            }
        }
    }
}
</script>
@endsection