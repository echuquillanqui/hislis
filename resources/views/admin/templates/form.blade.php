<div class="container py-4" x-data='templateBuilder(@json(old("sections", $schema)))'>
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-primary text-white d-flex justify-content-between">
            <strong>Constructor de plantillas LIS</strong>
            <a href="{{ route('templates.index') }}" class="btn btn-sm btn-light">Volver</a>
        </div>
        <div class="card-body">
            @if ($errors->any())<div class="alert alert-danger">{{ $errors->first() }}</div>@endif
            <form method="POST" action="{{ $action }}">
                @csrf @if($method !== 'POST') @method($method) @endif
                <label class="form-label fw-bold">Nombre</label>
                <input name="name" class="form-control mb-3" value="{{ old('name', $template->name) }}" required>

                <template x-for="(section, s) in sections" :key="s">
                    <div class="border rounded p-3 mb-3 bg-light">
                        <div class="d-flex gap-2 mb-3">
                            <input class="form-control fw-bold" :name="`sections[${s}][title]`" x-model="section.title" placeholder="Sección" required>
                            <button type="button" class="btn btn-outline-danger" @click="sections.splice(s,1)">Quitar sección</button>
                        </div>
                        <template x-for="(field, f) in section.fields" :key="f">
                            <div class="card mb-2">
                                <div class="card-body">
                                    <div class="row g-2 align-items-end">
                                        <div class="col-md-3"><label class="small">Etiqueta</label><input class="form-control" :name="`sections[${s}][fields][${f}][label]`" x-model="field.label" required></div>
                                        <div class="col-md-2"><label class="small">Tipo</label><select class="form-select" :name="`sections[${s}][fields][${f}][type]`" x-model="field.type"><option value="text">Texto</option><option value="textarea">Párrafo</option><option value="number">Número</option><option value="date">Fecha</option><option value="select">Select</option><option value="radio">Radio</option><option value="checkbox">Checkbox</option></select></div>
                                        <div class="col-md-2"><label class="small">Columnas</label><input type="number" min="1" max="12" class="form-control" :name="`sections[${s}][fields][${f}][column]`" x-model="field.column"></div>
                                        <div class="col-md-2 form-check"><input type="hidden" :name="`sections[${s}][fields][${f}][required]`" value="0"><input type="checkbox" class="form-check-input" :name="`sections[${s}][fields][${f}][required]`" value="1" x-model="field.required"><label class="form-check-label">Requerido</label></div>
                                        <div class="col-md-3 text-end"><button type="button" class="btn btn-outline-danger btn-sm" @click="section.fields.splice(f,1)">Quitar campo</button></div>
                                    </div>
                                    <div x-show="['select','radio','checkbox'].includes(field.type)" class="mt-3">
                                        <strong class="small">Opciones</strong>
                                        <template x-for="(option, o) in field.options" :key="o"><div class="input-group input-group-sm my-1"><input class="form-control" :name="`sections[${s}][fields][${f}][options][${o}][label]`" x-model="option.label" placeholder="Opción"><button type="button" class="btn btn-outline-danger" @click="field.options.splice(o,1)">×</button></div></template>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="field.options.push({label:''})">Añadir opción</button>
                                    </div>
                                    <div class="mt-3">
                                        <strong class="small">Reglas condicionales seguras</strong>
                                        <template x-for="(rule, r) in field.rules" :key="r"><div class="row g-1 my-1"><div class="col"><input class="form-control form-control-sm" :name="`sections[${s}][fields][${f}][rules][${r}][source_field_slug]`" x-model="rule.source_field_slug" placeholder="slug origen"></div><div class="col"><select class="form-select form-select-sm" :name="`sections[${s}][fields][${f}][rules][${r}][operator]`" x-model="rule.operator"><option value="equals">igual</option><option value="not_equals">distinto</option><option value="contains">contiene</option><option value="greater_than">mayor</option><option value="less_than">menor</option></select></div><div class="col"><input class="form-control form-control-sm" :name="`sections[${s}][fields][${f}][rules][${r}][comparison_value]`" x-model="rule.comparison_value" placeholder="valor"></div><div class="col"><select class="form-select form-select-sm" :name="`sections[${s}][fields][${f}][rules][${r}][action]`" x-model="rule.action"><option value="show">mostrar</option><option value="hide">ocultar</option><option value="require">requerir</option></select></div></div></template>
                                        <button type="button" class="btn btn-outline-secondary btn-sm" @click="field.rules.push({source_field_slug:'',operator:'equals',comparison_value:'',action:'show'})">Añadir regla</button>
                                    </div>
                                </div>
                            </div>
                        </template>
                        <button type="button" class="btn btn-success btn-sm" @click="section.fields.push(newField())">Añadir campo</button>
                    </div>
                </template>
                <button type="button" class="btn btn-outline-primary" @click="sections.push({title:'Nueva sección', fields:[newField()]})">Añadir sección</button>
                <button class="btn btn-primary float-end">Guardar versión</button>
            </form>
        </div>
    </div>
</div>
<script>function templateBuilder(initial){return{sections:initial&&initial.length?initial:[{title:'General',fields:[]}],newField(){return{label:'',type:'text',column:12,required:false,options:[],rules:[]}}}}</script>
