@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="templateManager()">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-primary text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">Nueva Plantilla Médica</h5>
                    <a href="{{ route('templates.index') }}" class="btn btn-sm btn-light">Volver</a>
                </div>
                <div class="card-body p-4">
                    <form action="{{ route('templates.store') }}" method="POST">
                        @csrf
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Nombre de la Especialidad / Servicio</label>
                            <input type="text" name="name" class="form-control form-control-lg" placeholder="Ej: Ginecología, Pediatría..." required>
                        </div>

                        <hr>

                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <h6 class="fw-bold text-secondary mb-0">CONFIGURACIÓN DE CAMPOS DINÁMICOS</h6>
                            <button type="button" @click="addField()" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Añadir Campo
                            </button>
                        </div>

                        <div class="fields-list">
                            <template x-for="(field, index) in fields" :key="index">
                                <div class="card mb-3 border-light bg-light">
                                    <div class="card-body py-2 px-3">
                                        <div class="row g-2 align-items-end">
                                            <div class="col-md-6">
                                                <label class="small fw-bold text-muted">Etiqueta del Campo (Label)</label>
                                                <input type="text" :name="`fields[${index}][label]`" x-model="field.label" 
                                                       class="form-control form-control-sm" placeholder="Ej: Motivo de consulta" required>
                                            </div>
                                            <div class="col-md-4">
                                                <label class="small fw-bold text-muted">Tipo de Entrada</label>
                                                <select :name="`fields[${index}][type]`" x-model="field.type" class="form-select form-select-sm">
                                                    <option value="text">Texto Corto</option>
                                                    <option value="textarea">Texto Largo (Párrafo)</option>
                                                    <option value="number">Numérico</option>
                                                    <option value="date">Fecha</option>
                                                </select>
                                            </div>
                                            <div class="col-md-2 text-end">
                                                <button type="button" @click="removeField(index)" class="btn btn-outline-danger btn-sm border-0">
                                                    <i class="fas fa-trash"></i> Quitar
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>

                        <div x-show="fields.length === 0" class="text-center py-4 border rounded bg-light border-dashed">
                            <p class="text-muted mb-0">No has añadido campos. Haz clic en "Añadir Campo" para empezar.</p>
                        </div>

                        <div class="mt-4 pt-3 border-top text-end">
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow-sm">
                                Guardar Plantilla
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function templateManager() {
    return {
        fields: [
            { label: '', type: 'text' } // Un campo por defecto
        ],
        addField() {
            this.fields.push({ label: '', type: 'text' });
        },
        removeField(index) {
            this.fields.splice(index, 1);
        }
    }
}
</script>
@endsection