@extends('layouts.app')

@section('content')
<div class="container py-4" x-data="templateEditor()">
    <div class="row justify-content-center">
        <div class="col-md-10">
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('templates.index') }}">Plantillas</a></li>
                    <li class="breadcrumb-item active">Editar Plantilla</li>
                </ol>
            </nav>

            <div class="card shadow-sm border-0">
                <div class="card-header bg-dark text-white d-flex justify-content-between align-items-center">
                    <h5 class="mb-0 font-weight-bold">
                        <i class="fas fa-edit me-2"></i>Editar Plantilla: <span class="text-info">{{ $template->name }}</span>
                    </h5>
                    <a href="{{ route('templates.index') }}" class="btn btn-sm btn-outline-light">Cancelar</a>
                </div>

                <div class="card-body p-4">
                    <form action="{{ route('templates.update', $template->id) }}" method="POST">
                        @csrf
                        @method('PUT')
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold text-secondary">Nombre de la Especialidad / Servicio</label>
                            <input type="text" name="name" value="{{ old('name', $template->name) }}" 
                                   class="form-control form-control-lg border-primary-subtle" required>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mb-3 mt-4">
                            <h6 class="fw-bold text-primary mb-0 uppercase small">Estructura de la Historia Clínica</h6>
                            <button type="button" @click="addField()" class="btn btn-success btn-sm shadow-sm">
                                <i class="fas fa-plus-circle me-1"></i>Añadir Campo
                            </button>
                        </div>

                        <div class="fields-container border rounded p-3 bg-light">
                            <template x-for="(field, index) in fields" :key="index">
                                <div class="card mb-2 border-0 shadow-sm overflow-hidden animate__animated animate__fadeIn">
                                    <div class="card-body py-2 px-3">
                                        <div class="row g-2 align-items-center">
                                            <div class="col-auto text-muted">
                                                <i class="fas fa-grip-vertical"></i>
                                            </div>
                                            
                                            <div class="col-md-5">
                                                <input type="text" :name="`fields[${index}][label]`" x-model="field.label" 
                                                       class="form-control form-control-sm" placeholder="Nombre del campo" required>
                                            </div>

                                            <div class="col-md-3">
                                                <select :name="`fields[${index}][type]`" x-model="field.type" class="form-select form-select-sm">
                                                    <option value="text">Texto Corto</option>
                                                    <option value="textarea">Texto Largo</option>
                                                    <option value="number">Numérico</option>
                                                    <option value="date">Fecha</option>
                                                </select>
                                            </div>
                                            <div class="col-md-3">
                                                <select :name="`fields[${index}][column]`" x-model.number="field.column" class="form-select form-select-sm">
                                                    <template x-for="col in 12" :key="col">
                                                        <option :value="col" x-text="`Columnas: ${col}`"></option>
                                                    </template>
                                                </select>
                                            </div>

                                            <div class="col-auto">
                                                <button type="button" @click="removeField(index)" class="btn btn-link text-danger p-1">
                                                    <i class="fas fa-times-circle fa-lg"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </template>

                            <div x-show="fields.length === 0" class="text-center py-5">
                                <i class="fas fa-folder-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">No hay campos en esta plantilla. ¡Añade uno nuevo!</p>
                            </div>
                        </div>

                        <div class="mt-4 d-grid gap-2 d-md-flex justify-content-md-end">
                            <button type="submit" class="btn btn-primary px-5 fw-bold shadow">
                                <i class="fas fa-save me-2"></i>Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function templateEditor() {
    return {
        // Cargamos los datos existentes desde el JSON del backend hacia Alpine
        fields: (@json($template->schema ?? []) || []).map(field => ({
            ...field,
            column: Number(field.column ?? 12)
        })),
        
        addField() {
            this.fields.push({ label: '', type: 'text', column: 12 });
        },
        
        removeField(index) {
            if(confirm('¿Seguro que deseas quitar este campo?')) {
                this.fields.splice(index, 1);
            }
        }
    }
}
</script>

<style>
    .border-dashed { border-style: dashed !important; }
    .fields-container { max-height: 500px; overflow-y: auto; }
</style>
@endsection