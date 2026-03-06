@extends('layouts.app')

@section('content')
<div class="container-fluid" x-data="{ editing: false, imagePreview: '{{ $setting->logo_path ? asset('storage/'.$setting->logo_path) : '' }}' }">
    <div class="row">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm text-center p-4">
                <div class="mb-3">
                    <template x-if="imagePreview">
                        <img :src="imagePreview" class="img-fluid rounded shadow-sm" style="max-height: 150px;">
                    </template>
                    <template x-if="!imagePreview">
                        <div class="bg-light py-5 rounded">
                            <i class="fa-solid fa-image fa-3x text-muted"></i>
                            <p class="small text-muted mt-2">Sin Logo cargado</p>
                        </div>
                    </template>
                </div>
                <h4 class="fw-bold">{{ $setting->hospital_name }}</h4>
                <p class="text-muted small">RUC/NIT: {{ $setting->ruc_nit }}</p>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between">
                    <h5 class="mb-0 fw-bold"><i class="fa-solid fa-gears text-primary"></i> Datos del Establecimiento</h5>
                    <button class="btn btn-sm btn-outline-primary" @click="editing = !editing">
                        <i :class="editing ? 'fa-solid fa-times' : 'fa-solid fa-edit'"></i>
                        <span x-text="editing ? ' Cancelar' : ' Editar Datos'"></span>
                    </button>
                </div>
                <div class="card-body">
                    <form action="{{ route('settings.update', $setting->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Nombre del Hospital</label>
                                <input type="text" name="hospital_name" class="form-control" value="{{ $setting->hospital_name }}" :disabled="!editing" required>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">RUC / NIT</label>
                                <input type="text" name="ruc_nit" class="form-control" value="{{ $setting->ruc_nit }}" :disabled="!editing" required>
                            </div>
                            <div class="col-md-12">
                                <label class="form-label small fw-bold">Dirección Física</label>
                                <input type="text" name="address" class="form-control" value="{{ $setting->address }}" :disabled="!editing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Teléfono de Contacto</label>
                                <input type="text" name="phone" class="form-control" value="{{ $setting->phone }}" :disabled="!editing">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label small fw-bold">Moneda del Sistema</label>
                                <select name="currency" class="form-select" :disabled="!editing">
                                    <option value="PEN" {{ $setting->currency == 'PEN' ? 'selected' : '' }}>Soles (PEN)</option>
                                    <option value="USD" {{ $setting->currency == 'USD' ? 'selected' : '' }}>Dólares (USD)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-12" x-show="editing" x-transition>
                                <label class="form-label small fw-bold text-primary">Actualizar Logo</label>
                                <input type="file" name="logo" class="form-control" @change="
                                    let reader = new FileReader();
                                    reader.onload = (e) => imagePreview = e.target.result;
                                    reader.readAsDataURL($event.target.files[0]);
                                ">
                            </div>
                        </div>

                        <div class="mt-4" x-show="editing">
                            <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                <i class="fa-solid fa-save me-2"></i> Guardar Cambios
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection