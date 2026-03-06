@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="templateApp()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h4 class="fw-bold text-dark">
            <i class="fa-solid fa-file-medical text-primary me-2"></i> Plantillas de Historias Clínicas
        </h4>
        <a href="{{ route('templates.create') }}" class="btn btn-primary px-4 border-2 shadow">
            <i class="fa-solid fa-plus me-1"></i> Nueva Plantilla
        </a>
    </div>

    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body bg-white rounded-3">
            <div class="input-group">
                <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                <input type="text" class="form-control border-0 bg-light" 
                       placeholder="Buscar por nombre de especialidad o plantilla..." 
                       x-model="search" @keyup.debounce.300ms="filterTemplates()">
            </div>
        </div>
    </div>

    <div class="card border-0 shadow-sm overflow-hidden rounded-4">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-primary text-white">
                    <tr>
                        <th class="ps-4 py-3">Nombre de Plantilla</th>
                        <th>Estructura (Campos)</th>
                        <th class="text-center">Cant. Campos</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <template x-for="t in filteredTemplates" :key="t.id">
                        <tr class="border-bottom">
                            <td class="ps-4 py-3">
                                <div class="fw-bold text-dark" x-text="t.name"></div>
                                <small class="text-muted" x-text="'Creado: ' + formatDate(t.created_at)"></small>
                            </td>
                            <td>
                                <div class="d-flex flex-wrap gap-1">
                                    <template x-for="(field, index) in getFields(t.schema).slice(0, 3)" :key="index">
                                        <span class="badge bg-light text-dark border fw-normal" x-text="field.label"></span>
                                    </template>
                                    <template x-if="getFields(t.schema).length > 3">
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle fw-bold" 
                                              x-text="'+' + (getFields(t.schema).length - 3)"></span>
                                    </template>
                                </div>
                            </td>
                            <td class="text-center">
                                <span class="badge rounded-pill bg-dark px-3" x-text="getFields(t.schema).length"></span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group shadow-sm rounded">
                                    <a :href="`/admin/templates/${t.id}/preview`" class="btn btn-sm btn-white border" title="Vista Previa">
                                        <i class="fa-solid fa-eye text-info"></i>
                                    </a>
                                    <a :href="`/admin/templates/${t.id}/edit`" class="btn btn-sm btn-white border" title="Editar">
                                        <i class="fa-solid fa-pen-to-square text-warning"></i>
                                    </a>
                                    <button class="btn btn-sm btn-white border" @click="confirmDelete(t.id)" title="Eliminar">
                                        <i class="fa-solid fa-trash-can text-danger"></i>
                                    </button>
                                </div>
                                <form :id="'delete-form-' + t.id" :action="'/admin/templates/' + t.id" method="POST" class="d-none">
                                    @csrf @method('DELETE')
                                </form>
                            </td>
                        </tr>
                    </template>
                    <template x-if="filteredTemplates.length === 0">
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-folder-open fa-3x mb-3 opacity-25"></i>
                                <p class="mb-0">No se encontraron plantillas con ese nombre.</p>
                            </td>
                        </tr>
                    </template>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
function templateApp() {
    return {
        // Pasamos las plantillas desde PHP a JSON para Alpine
        templates: @json($templates),
        filteredTemplates: [],
        search: '',

        init() {
            this.filteredTemplates = this.templates;
        },

        filterTemplates() {
            const query = this.search.toLowerCase();
            this.filteredTemplates = this.templates.filter(t => 
                t.name.toLowerCase().includes(query)
            );
        },

        getFields(schema) {
            // Maneja tanto string JSON como array por el problema de casting previo
            try {
                return typeof schema === 'string' ? JSON.parse(schema) : (schema || []);
            } catch (e) {
                return [];
            }
        },

        formatDate(dateString) {
            if(!dateString) return '---';
            const date = new Date(dateString);
            return date.toLocaleDateString('es-ES');
        },

        confirmDelete(id) {
            Swal.fire({
                title: '¿Estás seguro?',
                text: "Esta acción eliminará la plantilla permanentemente.",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#d33',
                cancelButtonColor: '#3085d6',
                confirmButtonText: 'Sí, eliminar',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('delete-form-' + id).submit();
                }
            });
        }
    }
}
</script>
@endsection