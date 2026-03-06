<div class="modal fade" id="modalExam" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <form :action="isEdit ? `/admin/lab_exams/${form.id}` : '{{ route('lab_exams.store') }}'" method="POST">
                @csrf
                <template x-if="isEdit">@method('PUT')</template>
                <input type="hidden" name="specialty_lab_id" :value="selectedSpecialty?.id">

                <div class="modal-header bg-primary text-white p-4">
                    <h5 class="fw-bold mb-0">
                        <i class="fa-solid fa-flask-vial me-2"></i>
                        <span x-text="isEdit ? 'Editar Examen' : 'Nuevo Examen: ' + selectedSpecialty?.name"></span>
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>

                <div class="modal-body p-4 bg-white">
                    <div class="row g-3">
                        <div class="col-md-8">
                            <label class="form-label small fw-bold">Nombre de la Prueba</label>
                            <input type="text" name="name" x-model="form.name" class="form-control border-2 shadow-sm" placeholder="Ej: Hemograma Completo" required>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Precio (S/)</label>
                            <input type="number" step="0.01" name="price" x-model="form.price" class="form-control border-2 shadow-sm" required>
                        </div>

                        <div class="col-12">
                            <label class="form-label small fw-bold text-muted">Instrucciones / Descripción (Ayunas, tipo de muestra...)</label>
                            <textarea name="description" x-model="form.description" class="form-control border-2 shadow-sm" rows="2" placeholder="Ej: Requiere 8 horas de ayuno..."></textarea>
                        </div>

                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Tipo de Respuesta</label>
                            <select name="input_type" x-model="form.input_type" class="form-select border-2 shadow-sm">
                                <option value="number">Numérico (Decimal)</option>
                                <option value="text">Texto Corto</option>
                                <option value="select">Lista Desplegable (Select)</option>
                                <option value="radio">Botones de Opción (Radio)</option>
                                <option value="textarea">Observación Larga</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-bold">Unidad de Medida</label>
                            <input type="text" name="unit" x-model="form.unit" class="form-control border-2 shadow-sm" placeholder="Ej: mg/dL, %">
                        </div>

                        <template x-if="['select', 'radio'].includes(form.input_type)">
                            <div class="col-12 mt-3 p-3 bg-light rounded-3 border animate__animated animate__fadeIn">
                                <label class="form-label small fw-bold text-primary">Configurar Opciones (Ej: Positivo, Negativo)</label>
                                <div class="input-group mb-2 shadow-sm">
                                    <input type="text" x-model="newOption" @keydown.enter.prevent="addOption()" class="form-control" placeholder="Escriba opción y pulse +">
                                    <button type="button" class="btn btn-primary" @click="addOption()"><i class="fa-solid fa-plus"></i></button>
                                </div>
                                <div class="d-flex flex-wrap gap-2">
                                    <template x-for="(opt, index) in optionsList" :key="index">
                                        <span class="badge bg-white text-dark border p-2 shadow-sm d-flex align-items-center">
                                            <span x-text="opt"></span>
                                            <i class="fa-solid fa-times ms-2 text-danger cursor-pointer" @click="removeOption(index)"></i>
                                        </span>
                                    </template>
                                </div>
                                <input type="hidden" name="input_options" :value="JSON.stringify(optionsList)">
                            </div>
                        </template>

                        <template x-if="form.input_type === 'number'">
                            <div class="col-12 mt-3 p-3 bg-light rounded-3 border animate__animated animate__fadeIn">
                                <label class="form-label small fw-bold text-primary">Valores de Referencia</label>
                                <div class="row g-2">
                                    <div class="col-md-6">
                                        <label class="small text-muted">Mínimo Normal</label>
                                        <input type="number" step="0.01" name="min_ref" x-model="form.min_ref" class="form-control border-0 shadow-sm" placeholder="0.00">
                                    </div>
                                    <div class="col-md-6">
                                        <label class="small text-muted">Máximo Normal</label>
                                        <input type="number" step="0.01" name="max_ref" x-model="form.max_ref" class="form-control border-0 shadow-sm" placeholder="0.00">
                                    </div>
                                </div>
                            </div>
                        </template>
                    </div>
                </div>

                <div class="modal-footer border-0 p-4 pt-0 bg-white shadow-sm">
                    <button type="button" class="btn btn-light rounded-pill px-4 border" data-bs-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary px-5 shadow rounded-pill fw-bold">
                        <i class="fa-solid fa-save me-2"></i>Guardar Examen
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>