@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="fw-bold mb-0 text-primary">
                <i class="fas fa-eye me-2"></i>Vista Previa de Plantilla
            </h3>
            <p class="text-muted">Especialidad: <span class="badge bg-secondary">{{ $template->name }}</span></p>
        </div>
        <a href="{{ route('templates.index') }}" class="btn btn-outline-secondary shadow-sm">
            <i class="fas fa-arrow-left me-1"></i> Volver al Listado
        </a>
    </div>

    <div class="row">
        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-primary text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-edit me-2"></i>Interfaz de Atención Médica</h6>
                </div>
                <div class="card-body bg-light p-4">
                    <div class="bg-white p-4 rounded shadow-sm border">
                        <h5 class="text-center fw-bold mb-4 text-uppercase border-bottom pb-2">Registro de Consulta</h5>
                        
                        @forelse($schema as $campo)
                            <div class="mb-4">
                                <div class="bg-light border border-bottom-0 rounded-top px-3 py-2">
                                    <label class="form-label fw-bold small text-primary mb-0 uppercase">
                                        {{ $campo['label'] }}
                                    </label>
                                </div>
                                @if($campo['type'] == 'textarea')
                                    <textarea class="form-control rounded-0 rounded-bottom" rows="3" placeholder="El médico redactará aquí..."></textarea>
                                @elseif($campo['type'] == 'number')
                                    <input type="number" class="form-control rounded-0 rounded-bottom">
                                @elseif($campo['type'] == 'date')
                                    <input type="date" class="form-control rounded-0 rounded-bottom">
                                @else
                                    <input type="text" class="form-control rounded-0 rounded-bottom" placeholder="Escriba aquí...">
                                @endif
                            </div>
                        @empty
                            <div class="text-center py-5">
                                <i class="fas fa-exclamation-circle fa-2x text-muted mb-2"></i>
                                <p class="text-muted">No hay campos configurados en esta plantilla.</p>
                            </div>
                        @endforelse

                        <div class="mt-4">
                            <button class="btn btn-primary w-100 fw-bold disabled">
                                <i class="fas fa-save me-2"></i>Finalizar Atención (Ejemplo)
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-6 mb-4">
            <div class="card shadow-sm border-0 h-100">
                <div class="card-header bg-dark text-white py-3">
                    <h6 class="mb-0 fw-bold"><i class="fas fa-file-pdf me-2"></i>Formato de Impresión / PDF</h6>
                </div>
                <div class="card-body bg-secondary-subtle p-4 d-flex justify-content-center">
                    
                    <div class="bg-white shadow p-5" style="width: 100%; max-width: 500px; min-height: 700px; color: #333;">
                        
                        <div class="text-center mb-5">
                            <h5 class="fw-bold mb-0">CLÍNICA MÉDICA</h5>
                            <p class="small text-muted mb-0">RUC: 20600000000 | Tel: 01 234-5678</p>
                            <div class="border-top border-2 border-dark mt-2 w-25 mx-auto"></div>
                        </div>

                        <div class="mb-4">
                            <h5 class="text-center fw-bold text-uppercase decoration-underline">{{ $template->name }}</h5>
                            <div class="mt-3 p-2 border bg-light small rounded">
                                <strong>PACIENTE:</strong> JUAN PÉREZ GARCÍA<br>
                                <strong>FECHA:</strong> {{ date('d/m/Y H:i') }}
                            </div>
                        </div>

                        @foreach($schema as $campo)
                            <div class="mb-3 border rounded overflow-hidden shadow-sm">
                                <div class="bg-gray-100 px-3 py-1 border-bottom" style="background-color: #f8f9fa;">
                                    <span class="fw-bold text-muted" style="font-size: 0.7rem; letter-spacing: 0.5px; text-transform: uppercase;">
                                        {{ $campo['label'] }}
                                    </span>
                                </div>
                                <div class="px-3 py-2 bg-white">
                                    <p class="mb-0" style="font-size: 0.9rem; min-height: 20px;">
                                        @if($campo['type'] == 'textarea')
                                            <span class="text-secondary italic">Este es un ejemplo de cómo se verá un párrafo largo dentro del reporte impreso, ocupando el espacio necesario hacia abajo...</span>
                                        @else
                                            <span class="text-secondary italic">Dato de ejemplo</span>
                                        @endif
                                    </p>
                                </div>
                            </div>
                        @endforeach

                        <div class="mt-5 pt-5 text-center">
                            <div class="border-top border-dark w-50 mx-auto mt-4"></div>
                            <p class="small fw-bold mb-0">FIRMA Y SELLO DEL MÉDICO</p>
                            <p class="text-muted" style="font-size: 0.6rem;">Generado por el Sistema de Gestión Médica</p>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* Estilos extra para la simulación del PDF */
    .bg-gray-100 { background-color: #f8f9fa; }
    .italic { font-style: italic; }
    .decoration-underline { text-decoration: underline; }
    
    /* Simular fuente de máquina de escribir o similar para el PDF si se desea */
    .pdf-body {
        font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    }
</style>
@endsection