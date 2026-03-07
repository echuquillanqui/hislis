@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="attentionMonitor()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold text-dark mb-0">
                <i class="fa-solid fa-hospital-user text-primary me-2"></i> MONITOR DE ATENCIÓN CLÍNICA
            </h4>
            <small class="text-muted fw-bold text-uppercase">Solo servicios con plantilla activa</small>
        </div>
        <div class="bg-white border border-dark px-3 py-2 fw-bold shadow-sm rounded-0">
            <i class="fa-regular fa-clock text-primary me-1"></i> <span x-text="currentTime"></span>
        </div>
    </div>

    <div class="card border-dark border-2 mb-4 shadow-sm rounded-0">
        <div class="card-body py-3 bg-white">
            <div class="row g-3">
                <div class="col-md-5">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">Búsqueda de Paciente</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-dark border-end-0"><i class="fa-solid fa-search"></i></span>
                        <input type="text" class="form-control border-dark rounded-0 shadow-sm ps-2"
                            placeholder="DNI o Apellidos..." x-model="filters.search" @input.debounce.300ms="getAtenciones()">
                    </div>
                </div>
                <div class="col-md-4">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">Fecha de Atención</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-dark border-end-0"><i class="fa-solid fa-calendar-day"></i></span>
                        <input type="date" class="form-control border-dark rounded-0 shadow-sm ps-2"
                            x-model="filters.date" @change="getAtenciones()">
                    </div>
                </div>
                <div class="col-md-3">
                    <label class="small fw-bold text-muted mb-1 text-uppercase">Visualización</label>
                    <select class="form-select border-dark rounded-0 shadow-sm" x-model="filters.status" @change="getAtenciones()">
                        <option value="pending">PENDIENTES</option>
                        <option value="all">HISTORIAL COMPLETO</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card border-dark border-2 shadow-sm rounded-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0 text-center">
                <thead style="background-color: #f8f9fa;">
                    <tr class="border-bottom border-dark border-2">
                        <th class="ps-4 text-start py-3" style="min-width: 300px;">
                            <span class="small fw-bold text-secondary text-uppercase">Paciente</span>
                        </th>
                        @foreach($areas as $area)
                            <th class="p-0 border-start border-dark border-opacity-25 header-cell" style="width: 110px;" title="{{ strtoupper($area->name) }}">
                            <div class="py-2 px-1 cell-content">
                                <i class="fa-solid fa-notes-medical text-primary fs-5 mb-1 d-block"></i>
                                <span class="d-block text-dark fw-bold text-truncate mx-auto text-uppercase" style="font-size: 0.6rem; max-width: 85px;">
                                    {{ Str::limit($area->name, 10, '') }}
                                </span>
                            </div>
                        </th>
                        @endforeach
                    </tr>
                </thead>
                <tbody class="bg-white">
                    <template x-for="p in patients" :key="p.id">
                        <tr class="border-bottom border-light">
                            <td class="ps-4 text-start py-3">
                                <div class="fw-bold text-dark text-uppercase small lh-1 mb-1" x-text="p.last_name + ' ' + p.first_name"></div>
                                <div class="text-muted fw-bold" style="font-size: 0.7rem;">
                                    DNI: <span class="text-primary" x-text="p.dni"></span>
                                </div>
                            </td>

                            @foreach($areas as $area)
                            <td class="p-2 border-start border-light text-center">
                                <template x-if="isAreaEnabled(p, {{ $area->id }}, '{{ $area->slug }}')">
                                    <button class="btn btn-sm w-100 py-2 border-2 shadow-sm position-relative rounded-0"
                                            :title="'Abrir: {{ strtoupper($area->name) }}'"
                                            :class="getBtnStyle(p, '{{ $area->slug }}')"
                                            @click="openAttention(p, {{ $area->id }}, '{{ $area->name }}', '{{ $area->slug }}')">
                                        
                                        <i class="fa-solid d-block mb-1" :class="'{{ $area->slug }}' === 'triaje' ? 'fa-stethoscope' : 'fa-hand-holding-medical'"></i>
                                        <span class="small fw-bold" x-text="'{{ $area->slug }}' === 'triaje' ? 'TRIAJE' : 'ATENDER'"></span>
                                        
                                        <template x-if="!p.is_triaged && '{{ $area->slug }}' !== 'triaje'">
                                            <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle shadow-sm"></span>
                                        </template>
                                    </button>
                                </template>

                                <template x-if="!isAreaEnabled(p, {{ $area->id }}, '{{ $area->slug }}')">
                                    <span class="text-muted opacity-25 small">-</span>
                                </template>
                            </td>
                            @endforeach
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalAttention" tabindex="-1" data-bs-backdrop="static">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content border-dark border-2 rounded-0 shadow-lg">
                <div class="modal-header bg-light border-bottom border-dark py-2">
                    <h6 class="modal-title fw-bold text-dark text-uppercase">
                        <i class="fa-solid fa-folder-open text-primary me-2"></i>
                        <span x-text="currentAreaName"></span> - <span x-text="selectedPatient.last_name + ' ' + selectedPatient.first_name"></span>
                    </h6>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <ul class="nav nav-tabs bg-light px-3 pt-2 border-bottom border-dark" x-show="currentAreaSlug !== 'triaje'">
                        <template x-for="(exam, index) in activeExams" :key="index">
                            <li class="nav-item">
                                <button class="nav-link border-dark border-bottom-0 fw-bold me-1 rounded-0 small text-uppercase"
                                    :class="activeTab === index ? 'active bg-white text-primary border-bottom-0' : 'text-muted'"
                                    @click="activeTab = index" style="margin-bottom: -1px;">
                                    <i class="fa-solid fa-file-waveform me-1"></i>
                                    <span x-text="exam.service_name"></span>
                                </button>
                            </li>
                        </template>
                    </ul>

                    <div class="p-4 bg-white" style="min-height: 450px;">

                        <template x-if="currentAreaSlug === 'triaje'">
                            <div>
                                <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark">REGISTRO DE TRIAJE</h5>
                                <div class="row g-3">
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Temperatura (°C)</label>
                                        <input type="number" step="0.1" class="form-control border-dark rounded-0" x-model="triage.temp">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Presión Arterial</label>
                                        <input type="text" class="form-control border-dark rounded-0" placeholder="120/80" x-model="triage.bp">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Frec. Cardíaca</label>
                                        <input type="number" class="form-control border-dark rounded-0" x-model="triage.hr">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Frec. Respiratoria</label>
                                        <input type="number" class="form-control border-dark rounded-0" x-model="triage.rr">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Peso (kg)</label>
                                        <input type="number" step="0.01" class="form-control border-dark rounded-0" x-model="triage.weight">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">Talla (m)</label>
                                        <input type="number" step="0.01" class="form-control border-dark rounded-0" x-model="triage.height">
                                    </div>
                                    <div class="col-md-3">
                                        <label class="small fw-bold mb-1">SpO2</label>
                                        <input type="number" class="form-control border-dark rounded-0" x-model="triage.spo2">
                                    </div>
                                    <div class="col-md-12">
                                        <label class="small fw-bold mb-1">Notas</label>
                                        <textarea class="form-control border-dark rounded-0" rows="4" x-model="triage.notes"></textarea>
                                    </div>
                                </div>
                                <div class="d-flex justify-content-between mt-4">
                                    <template x-if="selectedPatient.triage_id">
                                        <a class="btn btn-outline-secondary rounded-0" :href="`{{ url('admin/triage') }}/${selectedPatient.triage_id}/edit`">Abrir vista completa</a>
                                    </template>
                                    <button class="btn btn-primary fw-bold px-4 border-2 border-dark rounded-0 shadow ms-auto" @click="saveTriage()" :disabled="saving">
                                        <i class="fa-solid fa-save me-1"></i> GUARDAR TRIAJE
                                    </button>
                                </div>
                            </div>
                        </template>


                        <template x-for="(exam, index) in activeExams" :key="index">
                            <div x-show="currentAreaSlug !== 'triaje' && activeTab === index" x-transition>
                                <template x-if="!selectedPatient.is_triaged">
                                    <div class="alert alert-warning rounded-0 border-dark mb-4 py-2 small fw-bold shadow-sm d-flex justify-content-between align-items-center">
                                        <span><i class="fa-solid fa-triangle-exclamation me-2"></i> PACIENTE PENDIENTE DE TRIAJE</span>
                                        <button class="btn btn-sm btn-dark rounded-0" @click="goToTriage(selectedPatient)">Ir a triaje</button>
                                    </div>
                                </template>

                                <h5 class="fw-bold border-bottom pb-2 mb-3 text-dark">
                                    REGISTRO DE: <span class="text-primary" x-text="exam.service_name"></span>
                                </h5>
                                
                                <div class="row g-3">
                                    <template x-if="exam.template_schema && exam.template_schema.length">
                                        <template x-for="(field, fieldIndex) in exam.template_schema" :key="fieldIndex">
                                            <div :class="`col-md-${field.column || 12}`">
                                                <label class="small fw-bold mb-1 text-uppercase" x-text="field.label"></label>

                                                <template x-if="field.type === 'textarea'">
                                                    <textarea class="form-control border-dark rounded-0 shadow-sm" rows="6" placeholder="Completar campo..." :value="getFieldValue(exam.order_item_id, field)" @input="setFieldValue(exam.order_item_id, field, $event.target.value)"></textarea>
                                                </template>

                                                <template x-if="field.type === 'number'">
                                                    <input type="number" class="form-control border-dark rounded-0 shadow-sm" :value="getFieldValue(exam.order_item_id, field)" @input="setFieldValue(exam.order_item_id, field, $event.target.value)">
                                                </template>

                                                <template x-if="field.type === 'date'">
                                                    <input type="date" class="form-control border-dark rounded-0 shadow-sm" :value="getFieldValue(exam.order_item_id, field)" @input="setFieldValue(exam.order_item_id, field, $event.target.value)">
                                                </template>

                                                <template x-if="field.type !== 'textarea' && field.type !== 'number' && field.type !== 'date'">
                                                   <input type="text" class="form-control border-dark rounded-0 shadow-sm" :value="getFieldValue(exam.order_item_id, field)" @input="setFieldValue(exam.order_item_id, field, $event.target.value)">
                                                </template>
                                            </div>
                                        </template>
                                    </template>

                                    <template x-if="!exam.template_schema || !exam.template_schema.length">
                                        <div class="col-12">
                                            <label class="small fw-bold mb-1">HALLAZGOS MÉDICOS / INFORME</label>
                                            <textarea class="form-control border-dark rounded-0 shadow-sm" rows="12" placeholder="Escriba los resultados..."></textarea>
                                        </div>
                                    </template>
                                </div>

                                <div class="text-end mt-4">
                                    <button class="btn btn-primary fw-bold px-4 border-2 border-dark rounded-0 shadow" @click="saveExam(exam)" :disabled="saving || !selectedPatient.is_triaged">
                                        <i class="fa-solid fa-save me-1"></i> GUARDAR RESULTADO
                                    </button>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .table-hover tbody tr:hover { background-color: #f1f8ff !important; }
    .form-control, .form-select, .input-group-text, .btn { border-radius: 0 !important; }
    
    .btn-outline-primary { border-color: #212529 !important; color: #0d6efd; background: #fff; }
    .btn-outline-primary:hover { background: #0d6efd; color: #fff; }
    
    .btn-outline-warning { border-color: #212529 !important; background: #fffdf5; color: #997404; }
    .btn-outline-warning:hover { background: #ffc107; color: #000; }

    .btn-success { border-color: #212529 !important; background: #198754; color: #fff; }
    .btn-primary { border-color: #212529 !important; }

    .header-cell { position: relative; }
    .cell-content { pointer-events: none; }
    .nav-tabs .nav-link.active { border-color: #212529 #212529 #fff !important; }
</style>

<script>
const registerAttentionMonitor = () => {
    Alpine.data('attentionMonitor', () => ({
        patients: [],
        filters: {
            search: '', 
            status: 'pending',
            date: new Date().toLocaleDateString('en-CA')
        },
        selectedPatient: {},
        currentAreaName: '',
        currentAreaSlug: '',
        activeExams: [],
        activeTab: 0,
        currentTime: '',
        formResults: {},
        saving: false,
        triage: {
            temp: '', bp: '', hr: '', rr: '', weight: '', height: '', spo2: '', notes: ''
        },

        init() {
            this.getAtenciones();
            this.startTime();
        },

        startTime() {
            const update = () => {
                this.currentTime = new Date().toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            };
            update(); setInterval(update, 60000);
        },

        isAreaEnabled(patient, areaId, slug) {
            return slug === 'triaje' || patient.paid_area_ids.includes(areaId);
        },

        getAtenciones() {
            const params = new URLSearchParams(this.filters);
            fetch(`{{ route('attentions.index') }}?${params.toString()}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => { this.patients = data.patients; });
        },

        getBtnStyle(p, slug) {
            if (slug === 'triaje') return p.is_triaged ? 'btn-success' : 'btn-primary';
            return p.is_triaged ? 'btn-outline-primary' : 'btn-outline-warning';
        },

        openAttention(patient, areaId, areaName, areaSlug) {
            this.selectedPatient = patient;
            this.currentAreaName = areaName;
            this.currentAreaSlug = areaSlug;
            
            if (areaSlug === 'triaje') {
                this.resetTriage();
                this.activeExams = [];
            } else {
                // Filtramos las órdenes que corresponden solo a esta área
                this.activeExams = patient.medical_orders[areaId] || [];
                this.activeTab = 0;
            }

            new bootstrap.Modal(document.getElementById('modalAttention')).show();
        },

        goToTriage(patient) {
            const triageArea = @json($areas->firstWhere('slug', 'triaje')?->id ?? null);
            if (!triageArea) return;
            this.openAttention(patient, triageArea, 'TRIAJE', 'triaje');
        },

        resetTriage() {
            this.triage = { temp: '', bp: '', hr: '', rr: '', weight: '', height: '', spo2: '', notes: '' };
        },

        getFieldKey(field) {
            return field.key || field.name || field.label;
        },

        getFieldValue(orderItemId, field) {
            const key = this.getFieldKey(field);
            return this.formResults[orderItemId]?.[key] || '';
        },

        setFieldValue(orderItemId, field, value) {
            const key = this.getFieldKey(field);
            if (!this.formResults[orderItemId]) {
                this.formResults[orderItemId] = {};
            }
            this.formResults[orderItemId][key] = value;
        },

        async saveExam(exam) {
            this.saving = true;
            try {
                const response = await fetch(`{{ route('attentions.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        order_item_id: exam.order_item_id,
                        template_data: this.formResults[exam.order_item_id] || {},
                    })
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || data.error || 'No se pudo guardar el resultado.');
                }

                toastr.success(data.message || 'Resultado guardado.');
                this.getAtenciones();
            } catch (error) {
                toastr.error(error.message);
            } finally {
                this.saving = false;
            }
        },

        async saveTriage() {
            this.saving = true;
            try {
                const response = await fetch(`{{ route('triage.store') }}`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({
                        patient_id: this.selectedPatient.id,
                        ...this.triage,
                    })
                });

                const data = await response.json();
                if (!response.ok) {
                    throw new Error(data.message || data.error || 'No se pudo guardar el triaje.');
                }

                toastr.success(data.message || 'Triaje guardado.');
                this.selectedPatient.is_triaged = true;
                this.selectedPatient.triage_id = data.triage_id;
                this.getAtenciones();
            } catch (error) {
                toastr.error(error.message);
            } finally {
                this.saving = false;
            }
        }  
    }));
};

if (window.Alpine) {
    registerAttentionMonitor();
} else {
    document.addEventListener('alpine:init', registerAttentionMonitor);
}
</script>
@endsection