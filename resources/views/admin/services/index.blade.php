@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="serviceApp()">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold"><i class="fa-solid fa-stethoscope text-primary me-2"></i>Catálogo de Servicios</h4>
        <button class="btn btn-primary px-4 shadow" @click="openModal('create')">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Servicio
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <input type="text" class="form-control border-0 bg-light" placeholder="Buscar servicio o área..." x-model="search">
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-4">SERVICIO</th>
                        <th>ÁREA</th>
                        <th>PLANTILLA ACTIVA</th>
                        <th>PRECIO</th>
                        <th class="text-end pe-4">ACCIONES</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="service in filteredServices()" :key="service.id">
                        <tr>
                            <td class="ps-4 fw-bold" x-text="service.name"></td>
                            <td>
                                <span class="badge bg-info-subtle text-info" x-text="service.area.name"></span>
                                <template x-if="service.area.parent">
                                    <small class="text-muted d-block" x-text="'Padre: ' + service.area.parent.name"></small>
                                </template>
                            </td>
                            <td>
                                <span class="badge" :class="getTemplateClass(service)" x-text="getTemplateLabel(service)"></span>
                            </td>
                            <td class="fw-bold text-primary" x-text="'S/ ' + parseFloat(service.price).toFixed(2)"></td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <button class="btn btn-sm btn-light border" @click="openModal('edit', service)">
                                        <i class="fa-solid fa-pen text-warning"></i>
                                    </button>
                                    <button class="btn btn-sm btn-light border" @click="deleteService(service.id)">
                                        <i class="fa-solid fa-trash text-danger"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalService" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <form :action="isEdit ? `/admin/services/${form.id}` : '{{ route('services.store') }}'" method="POST" class="modal-content border-0 shadow-lg">
                @csrf
                <template x-if="isEdit">@method('PUT')</template>
                
                <div class="modal-header bg-dark text-white p-4">
                    <h5 x-text="isEdit ? 'Editar Servicio' : 'Nuevo Servicio'"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label fw-bold">Nombre del Servicio</label>
                            <input type="text" name="name" x-model="form.name" class="form-control border-2" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-bold">Precio (S/)</label>
                            <input type="number" step="0.01" name="price" x-model="form.price" class="form-control border-2" required>
                        </div>
                        
                        <div class="col-12">
                            <label class="form-label fw-bold">Área / Especialidad</label>
                            <select name="area_id" x-model="form.area_id" class="form-select border-2" @change="syncAreaInfo()" required>
                                <option value="">Seleccione un área...</option>
                                <template x-for="area in allAreas" :key="area.id">
                                    <option :value="area.id" x-text="area.name"></option>
                                </template>
                            </select>
                        </div>

                        <div class="col-12 mt-4 p-3 bg-light rounded-3 border">
                            <h6 class="fw-bold text-secondary mb-3 small uppercase">Configuración de Jerarquía del Área</h6>
                            <div class="row g-2">
                                <div class="col-md-6">
                                    <label class="small fw-bold">Área Superior (Padre)</label>
                                    <select name="area_parent_id" x-model="form.area_parent_id" class="form-select form-select-sm border-2">
                                        <option value="">-- Sin Padre --</option>
                                        <template x-for="a in allAreas" :key="a.id">
                                            <option :value="a.id" x-text="a.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="small fw-bold">Plantilla Base del Área</label>
                                    <select name="area_template_id" x-model="form.area_template_id" class="form-select form-select-sm border-2">
                                        <option value="">-- Sin plantilla --</option>
                                        @foreach($templates as $t)
                                            <option value="{{ $t->id }}">{{ $t->name }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="col-12">
                            <label class="form-label fw-bold text-primary">Plantilla Específica del Servicio (Hereda si está vacía)</label>
                            <select name="template_id" x-model="form.template_id" class="form-select border-primary border-2">
                                <option value="">-- Usar jerarquía de área --</option>
                                @foreach($templates as $t)
                                    <option value="{{ $t->id }}">{{ $t->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <div class="modal-footer p-4 pt-0 border-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold">GUARDAR SERVICIO</button>
                </div>
            </form>
        </div>
    </div>

    <form id="deleteForm" method="POST" style="display:none;">
        @csrf
        @method('DELETE')
    </form>
</div>

<script>
function serviceApp() {
    return {
        services: @js($services),
        allAreas: @js($allAreas),
        search: '',
        isEdit: false,
        form: { id: '', name: '', price: '', area_id: '', template_id: '', area_parent_id: '', area_template_id: '' },

        filteredServices() {
            if(!this.search) return this.services;
            return this.services.filter(s => 
                s.name.toLowerCase().includes(this.search.toLowerCase()) || 
                s.area.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        openModal(mode, service = null) {
            this.isEdit = mode === 'edit';
            if (service) {
                this.form = { 
                    id: service.id,
                    name: service.name,
                    price: service.price,
                    area_id: service.area_id,
                    template_id: service.template_id || '',
                    area_parent_id: service.area.parent_id || '',
                    area_template_id: service.area.template_id || ''
                };
            } else {
                this.form = { id: '', name: '', price: '', area_id: '', template_id: '', area_parent_id: '', area_template_id: '' };
            }
            new bootstrap.Modal(document.getElementById('modalService')).show();
        },

        syncAreaInfo() {
            const area = this.allAreas.find(a => a.id == this.form.area_id);
            if (area) {
                this.form.area_parent_id = area.parent_id || '';
                this.form.area_template_id = area.template_id || '';
            }
        },

        getTemplateLabel(s) {
            if (s.template_id) return 'Personalizada';
            if (s.area.template_id) return 'Del Área';
            if (s.area.parent && s.area.parent.template_id) return 'Del Padre';
            return 'Sin Plantilla';
        },

        getTemplateClass(s) {
            if (s.template_id) return 'bg-success-subtle text-success';
            if (s.area.template_id || (s.area.parent && s.area.parent.template_id)) return 'bg-primary-subtle text-primary';
            return 'bg-secondary-subtle text-secondary';
        },

        deleteService(id) {
            if(confirm('¿Estás seguro de eliminar este servicio?')) {
                const form = document.getElementById('deleteForm');
                form.action = `/admin/services/${id}`;
                form.submit();
            }
        }
    }
}
</script>
@endsection