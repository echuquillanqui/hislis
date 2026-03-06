@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="patientApp()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark"><i class="fa-solid fa-hospital-user text-primary me-2"></i> Admisión de Pacientes</h4>
        <button class="btn btn-primary px-4 border-2 shadow" @click="openModal('create')">
            <i class="fa-solid fa-plus me-1"></i> Nuevo Registro
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success border-2 border-success fw-bold shadow-sm mb-4">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger border-2 border-danger fw-bold shadow-sm mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
        </div>
    @endif

    <div class="card border-dark shadow-sm mb-4">
        <div class="card-body bg-light">
            <div class="input-group">
                <span class="input-group-text bg-white border-dark"><i class="fa-solid fa-magnifying-glass"></i></span>
                <input type="text" class="form-control border-dark" placeholder="Buscar por DNI o Apellidos..." 
                       x-model="search" @keyup.debounce.500ms="getPatients(1)">
            </div>
        </div>
    </div>

    <div class="card border-dark shadow overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-dark">
                    <tr>
                        <th class="ps-4">DNI</th>
                        <th>Apellidos y Nombres</th>
                        <th>Edad / Sexo</th>
                        <th>Contacto</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="p in pagination.data" :key="p.id">
                        <tr>
                            <td class="ps-4 fw-bold" x-text="p.dni"></td>
                            <td class="text-uppercase fw-bold" x-text="p.last_name + ' ' + p.first_name"></td>
                            <td>
                                <span x-text="calculateAge(p.birth_date) + ' años'"></span>
                                <span :class="p.gender == 'M' ? 'badge bg-primary' : 'badge bg-danger'" x-text="p.gender" class="ms-1"></span>
                            </td>
                            <td x-text="p.phone || '---'"></td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a :href="`/admin/patients/${p.id}/history`" class="btn btn-sm btn-dark border-1" title="Ver Historial">
                                        <i class="fa-solid fa-clock-rotate-left"></i>
                                    </a>
                                    <button class="btn btn-sm btn-outline-dark border-1" @click="openModal('edit', p)">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <button class="btn btn-sm btn-outline-danger border-1" @click="confirmDelete(p.id)">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                                <form :id="'delete-form-' + p.id" :action="'/admin/patients/' + p.id" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>

    <div class="modal fade" id="modalPatient" tabindex="-1">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <form :action="isEdit ? `/admin/patients/${form.id}` : '{{ route('patients.store') }}'" method="POST" 
                  class="modal-content border border-dark border-2 shadow-lg rounded-0">
                @csrf
                <template x-if="isEdit"><input type="hidden" name="_method" value="PUT"></template>
                <div class="modal-header bg-primary text-white rounded-0">
                    <h5 class="fw-bold" x-text="isEdit ? 'ACTUALIZAR DATOS' : 'NUEVO PACIENTE'"></h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4 row g-3 bg-white">
                    <div class="col-md-4">
                        <label class="fw-bold small">DNI</label>
                        <input type="text" name="dni" class="form-control border-dark border-2" x-model="form.dni" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small">Nombres</label>
                        <input type="text" name="first_name" class="form-control border-dark border-2" x-model="form.first_name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small">Apellidos</label>
                        <input type="text" name="last_name" class="form-control border-dark border-2" x-model="form.last_name" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small">F. Nacimiento</label>
                        <input type="date" name="birth_date" class="form-control border-dark border-2" x-model="form.birth_date" required>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small">Género</label>
                        <select name="gender" class="form-select border-dark border-2" x-model="form.gender" required>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <label class="fw-bold small">Teléfono</label>
                        <input type="text" name="phone" class="form-control border-dark border-2" x-model="form.phone">
                    </div>
                </div>
                <div class="modal-footer bg-light border-top border-dark rounded-0">
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-bold">GUARDAR FICHA</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function patientApp() {
    return {
        pagination: { data: [] },
        search: '',
        isEdit: false,
        form: { id: '', dni: '', first_name: '', last_name: '', birth_date: '', gender: 'M', phone: '' },
        
        init() { this.getPatients(1); },

        getPatients(page) {
            fetch(`/admin/patients?page=${page}&search=${this.search}`, { headers: {'X-Requested-With': 'XMLHttpRequest'} })
                .then(res => res.json()).then(data => this.pagination = data);
        },

        openModal(mode, p = null) {
            this.isEdit = mode === 'edit';
            this.form = this.isEdit ? { ...p } : { id: '', dni: '', first_name: '', last_name: '', birth_date: '', gender: 'M', phone: '' };
            new bootstrap.Modal(document.getElementById('modalPatient')).show();
        },

        confirmDelete(id) {
            if (confirm('¿Seguro que desea eliminar este paciente? El sistema validará si tiene historial antes de proceder.')) {
                document.getElementById('delete-form-' + id).submit();
            }
        },

        calculateAge(birthDate) {
            if (!birthDate) return 0;
            let today = new Date();
            let birth = new Date(birthDate);
            let age = today.getFullYear() - birth.getFullYear();
            let monthDiff = today.getMonth() - birth.getMonth();
            if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birth.getDate())) {
                age--;
            }
            return age;
        }
    }
}
</script>
@endsection