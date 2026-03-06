@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="labExamManager()">
    <div class="row g-4">
        
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fa-solid fa-tags me-2"></i>Especialidades</h6>
                </div>
                <div class="list-group list-group-flush" style="max-height: 78vh; overflow-y: auto;">
                    <template x-for="s in specialties" :key="s.id">
                        <button @click="selectSpecialty(s)" 
                                :class="selectedSpecialty?.id === s.id ? 'active-item shadow-sm' : ''"
                                class="list-group-item list-group-item-action border-bottom py-3 transition-all">
                            <div class="d-flex justify-content-between align-items-center">
                                <div class="text-start">
                                    <span class="fw-bold d-block" :class="selectedSpecialty?.id === s.id ? 'text-primary' : 'text-dark'" x-text="s.name"></span>
                                    <small class="text-muted small" x-text="s.area.name"></small>
                                </div>
                                <span class="badge rounded-pill bg-light text-primary border" x-text="s.lab_exams.length"></span>
                            </div>
                        </button>
                    </template>
                </div>
            </div>
        </div>

        <div class="col-md-9">
            <template x-if="selectedSpecialty">
                <div x-transition:enter="animate__animated animate__fadeIn">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <div>
                            <h4 class="fw-bold text-dark mb-0" x-text="selectedSpecialty.name"></h4>
                            <nav aria-label="breadcrumb">
                                <ol class="breadcrumb mb-0">
                                    <li class="breadcrumb-item small" x-text="selectedSpecialty.area.name"></li>
                                    <li class="breadcrumb-item active small" aria-current="page">Catálogo de Exámenes</li>
                                </ol>
                            </nav>
                        </div>
                        <button class="btn btn-primary px-4 rounded-pill shadow-sm fw-bold border-2" @click="openModal('create')">
                            <i class="fa-solid fa-plus me-1"></i> Agregar Examen
                        </button>
                    </div>

                    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="bg-light text-muted small fw-bold text-uppercase">
                                    <tr>
                                        <th class="ps-4 py-3">Nombre / Descripción</th>
                                        <th>Configuración</th>
                                        <th class="text-center">Referencia</th>
                                        <th class="text-center">Precio</th>
                                        <th class="text-end pe-4">Acciones</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-for="e in selectedSpecialty.lab_exams" :key="e.id">
                                        <tr>
                                            <td class="ps-4">
                                                <div class="fw-bold text-dark fs-6" x-text="e.name"></div>
                                                <div class="small text-muted" x-show="e.description">
                                                    <i class="fa-solid fa-info-circle text-info me-1"></i>
                                                    <span x-text="e.description"></span>
                                                </div>
                                                <div class="mt-1 d-flex gap-1 flex-wrap" x-show="e.input_options">
                                                    <template x-for="opt in JSON.parse(e.input_options || '[]')" :key="opt">
                                                        <span class="badge bg-light text-primary border border-primary-subtle" style="font-size: 0.6rem;" x-text="opt"></span>
                                                    </template>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-dark-subtle text-dark fw-normal text-capitalize" x-text="e.input_type"></span>
                                                <div class="small text-muted mt-1" x-text="e.unit ? 'Unidad: ' + e.unit : ''"></div>
                                            </td>
                                            <td class="text-center">
                                                <template x-if="e.input_type === 'number'">
                                                    <span class="text-muted small fw-bold" x-text="(e.min_ref || '0') + ' - ' + (e.max_ref || '∞')"></span>
                                                </template>
                                                <template x-if="e.input_type !== 'number'">
                                                    <span class="text-muted small italic">Cualitativo</span>
                                                </template>
                                            </td>
                                            <td class="text-center fw-bold text-success" x-text="'S/ ' + parseFloat(e.price).toFixed(2)"></td>
                                            <td class="text-end pe-4">
                                                <div class="btn-group shadow-sm bg-white rounded border">
                                                    <button class="btn btn-sm text-warning border-end" @click="openModal('edit', e)">
                                                        <i class="fa-solid fa-edit"></i>
                                                    </button>
                                                    <button class="btn btn-sm text-danger" @click="confirmDelete(e.id)">
                                                        <i class="fa-solid fa-trash-alt"></i>
                                                    </button>
                                                </div>
                                                <form :id="'del-form-' + e.id" :action="'/admin/lab_exams/' + e.id" method="POST" class="d-none">
                                                    @csrf @method('DELETE')
                                                </form>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </template>

            <template x-if="!selectedSpecialty">
                <div class="card border-0 shadow-sm rounded-4 p-5 text-center">
                    <i class="fa-solid fa-vials fa-4x text-light mb-3"></i>
                    <h5 class="text-muted">Seleccione una especialidad para gestionar el catálogo</h5>
                </div>
            </template>
        </div>
    </div>

    @include('admin.lab_exams.modal_exam')
</div>

<style>
    .active-item { border-left: 5px solid #0d6efd !important; background-color: #f0f7ff !important; }
    .transition-all { transition: all 0.2s ease-in-out; }
    .cursor-pointer { cursor: pointer; }
</style>

<script>
function labExamManager() {
    return {
        specialties: @json($specialties),
        selectedSpecialty: null,
        isEdit: false,
        newOption: '',
        optionsList: [],
        form: { id: '', name: '', description: '', price: 0, unit: '', input_type: 'number', min_ref: '', max_ref: '' },

        init() {
            if(this.specialties.length > 0) this.selectSpecialty(this.specialties[0]);
        },

        selectSpecialty(s) {
            this.selectedSpecialty = s;
        },

        openModal(mode, e = null) {
            this.isEdit = mode === 'edit';
            if(this.isEdit) {
                this.form = { ...e };
                this.optionsList = e.input_options ? JSON.parse(e.input_options) : [];
            } else {
                this.form = { id: '', name: '', description: '', price: 0, unit: '', input_type: 'number', min_ref: '', max_ref: '' };
                this.optionsList = [];
            }
            this.newOption = '';
            new bootstrap.Modal(document.getElementById('modalExam')).show();
        },

        addOption() {
            if(this.newOption.trim() !== '') {
                this.optionsList.push(this.newOption.trim());
                this.newOption = '';
            }
        },

        removeOption(index) {
            this.optionsList.splice(index, 1);
        },

        confirmDelete(id) {
            Swal.fire({
                title: '¿Eliminar examen?',
                text: "Se borrará definitivamente del catálogo.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#ef4444',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) document.getElementById('del-form-' + id).submit();
            });
        }
    }
}
</script>
@endsection