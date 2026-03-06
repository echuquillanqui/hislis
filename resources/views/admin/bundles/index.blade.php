@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="bundleApp()">
    
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">
            <i class="fas fa-layer-group text-primary me-2"></i>Paquetes y Perfiles Médicos
        </h4>
        <button class="btn btn-primary px-4 shadow-sm border-2" @click="openModal('create')">
            <i class="fas fa-plus me-1"></i> Nuevo Paquete
        </button>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-white rounded-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fas fa-search text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-light" 
                       placeholder="Buscar perfil (Ej: Perfil Hepático, Prenatal...)" 
                       x-model="search" @keyup.debounce.300ms="filter()">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-4 py-3">Nombre del Paquete</th>
                        <th>Composición (Servicios y Exámenes)</th>
                        <th>Precio Paquete</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="bundle in filtered" :key="bundle.id">
                        <tr>
                            <td class="ps-4 fw-bold text-dark" x-text="bundle.name"></td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <template x-for="item in bundle.items" :key="item.id">
                                        <span :class="item.itemable_type.includes('Service') ? 'badge bg-primary-subtle text-primary border' : 'badge bg-info-subtle text-info border'" 
                                              class="fw-normal py-1 px-2">
                                            <i :class="item.itemable_type.includes('Service') ? 'fas fa-user-md' : 'fas fa-flask'" class="me-1"></i>
                                            <span x-text="item.itemable.name"></span>
                                            
                                            <template x-if="item.itemable.specialty_lab">
                                                <small class="fw-bold" x-text="' [' + item.itemable.specialty_lab.name + ']'"></small>
                                            </template>
                                        </span>
                                    </template>
                                </div>
                            </td>
                            <td class="fw-bold text-primary">S/ <span x-text="parseFloat(bundle.price).toFixed(2)"></span></td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-white border shadow-sm" @click="openModal('edit', bundle)">
                                    <i class="fas fa-edit text-warning"></i>
                                </button>
                                <button class="btn btn-sm btn-white border shadow-sm" @click="confirmDelete(bundle.id)">
                                    <i class="fas fa-trash text-danger"></i>
                                </button>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalBundle" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form :action="isEdit ? `{{ url('admin/bundles') }}/${form.id}` : '{{ route('bundles.store') }}'" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                @csrf
                <template x-if="isEdit">@method('PUT')</template>

                <div class="modal-header bg-dark text-white border-0">
                    <h5 class="fw-bold mb-0" x-text="isEdit ? 'Editar Perfil Médico' : 'Crear Nuevo Perfil Médico'"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-8">
                            <label class="small fw-bold text-secondary">Nombre del Perfil</label>
                            <input type="text" name="name" x-model="form.name" class="form-control form-control-lg border-2" placeholder="Ej: Perfil Lipídico" required>
                        </div>
                        <div class="col-md-4">
                            <label class="small fw-bold text-secondary">Precio Final (S/)</label>
                            <input type="number" step="0.01" name="price" x-model="form.price" class="form-control form-control-lg border-2 fw-bold text-primary" required>
                        </div>
                    </div>

                    <div class="bg-light p-3 rounded-3 border mb-3">
                        <label class="small fw-bold text-uppercase text-muted mb-2 d-block">Agregar Exámenes o Consultas</label>
                        <div class="input-group">
                            <select class="form-select border-2" x-model="tempItem">
                                <option value="">Seleccione un item...</option>
                                <optgroup label="CONSULTAS / SERVICIOS">
                                    @foreach($services as $s)
                                        <option value="{{ json_encode(['id' => $s->id, 'name' => $s->name, 'type' => 'service', 'spec' => '']) }}">
                                            {{ $s->name }}
                                        </option>
                                    @endforeach
                                </optgroup>
                                <optgroup label="LABORATORIO (POR ESPECIALIDAD)">
                                    @foreach($labExams as $l)
                                        <option value="{{ json_encode(['id' => $l->id, 'name' => $l->name, 'type' => 'lab', 'spec' => $l->specialtyLab ? $l->specialtyLab->name : '']) }}">
                                            {{ $l->name }} {{ $l->specialtyLab ? '['.$l->specialtyLab->name.']' : '' }}
                                        </option>
                                    @endforeach
                                </optgroup>
                            </select>
                            <button type="button" class="btn btn-success px-4" @click="addItem()">
                                <i class="fas fa-plus"></i>
                            </button>
                        </div>
                    </div>

                    <div class="list-group shadow-sm border rounded-3 overflow-hidden">
                        <template x-for="(it, index) in selected" :key="index">
                            <div class="list-group-item d-flex justify-content-between align-items-center py-2">
                                <div>
                                    <i :class="it.type === 'service' ? 'fas fa-user-md text-primary' : 'fas fa-flask text-info'" class="me-2"></i>
                                    <span class="fw-bold small" x-text="it.name"></span>
                                    <template x-if="it.spec">
                                        <span class="text-muted small fw-bold" x-text="' [' + it.spec + ']'"></span>
                                    </template>
                                </div>
                                <input type="hidden" :name="`items[${index}][id]`" :value="it.id">
                                <input type="hidden" :name="`items[${index}][type]`" :value="it.type">
                                <button type="button" class="btn btn-link text-danger p-0" @click="selected.splice(index, 1)">
                                    <i class="fas fa-times-circle"></i>
                                </button>
                            </div>
                        </template>
                        <div x-show="selected.length === 0" class="p-3 text-center text-muted small bg-white italic">
                            No has añadido items a este paquete.
                        </div>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0">
                    <button type="submit" class="btn btn-primary w-100 py-3 fw-bold shadow">
                        <i class="fas fa-save me-2"></i>GUARDAR PERFIL
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function bundleApp() {
    return {
        bundles: @json($bundles),
        filtered: [],
        search: '',
        isEdit: false,
        tempItem: '',
        selected: [],
        form: { id: '', name: '', price: '' },

        init() {
            this.filtered = this.bundles;
        },

        filter() {
            this.filtered = this.bundles.filter(b => 
                b.name.toLowerCase().includes(this.search.toLowerCase())
            );
        },

        addItem() {
            if(!this.tempItem) return;
            const item = JSON.parse(this.tempItem);
            // Evitar duplicados
            if(!this.selected.find(x => x.id == item.id && x.type == item.type)) {
                this.selected.push(item);
            }
            this.tempItem = '';
        },

        openModal(mode, bundle = null) {
            this.isEdit = mode === 'edit';
            if(bundle) {
                this.form = { ...bundle };
                // Mapear items existentes para Alpine
                this.selected = bundle.items.map(i => ({
                    id: i.itemable.id,
                    name: i.itemable.name,
                    type: i.itemable_type.includes('Service') ? 'service' : 'lab',
                    spec: (i.itemable.specialty_lab) ? i.itemable.specialty_lab.name : ''
                }));
            } else {
                this.form = { id: '', name: '', price: '0.00' };
                this.selected = [];
            }
            new bootstrap.Modal(document.getElementById('modalBundle')).show();
        },

        confirmDelete(id) {
            if(confirm('¿Estás seguro de eliminar este paquete permanentemente?')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `{{ url('admin/bundles') }}/${id}`;
                form.innerHTML = `@csrf @method('DELETE')`;
                document.body.appendChild(form);
                form.submit();
            }
        }
    }
}
</script>
@endsection