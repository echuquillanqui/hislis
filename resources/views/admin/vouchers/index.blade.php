@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Caja y facturación</h2>
            <p class="text-muted mb-0">Control de vouchers emitidos y acceso rápido a impresión/edición.</p>
        </div>
        <a href="{{ route('vouchers.create') }}" class="btn btn-primary rounded-pill shadow-sm px-4">
            <i class="fa-solid fa-cart-plus me-2"></i>Nueva atención
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('vouchers.index') }}" method="GET" class="row g-3">
                <div class="col-lg-6">
                    <label class="form-label text-muted small mb-1">Paciente o DNI</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-0"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control bg-light border-0" placeholder="Ej: Quispe o 74125896" value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-lg-3">
                    <label class="form-label text-muted small mb-1">Fecha</label>
                    <input type="date" name="date" class="form-control bg-light border-0" value="{{ request('date') }}">
                </div>
                <div class="col-lg-3 d-flex align-items-end gap-2">
                    <button class="btn btn-dark w-100">Aplicar filtros</button>
                    <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary">Limpiar</a>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">Comprobante</th>
                        <th>Paciente</th>
                        <th>Emisión</th>
                        <th>Total</th>
                        <th>Estado</th>
                        <th class="text-end pe-4">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $v)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold">{{ $v->series }}-{{ $v->number }}</div>
                                <small class="text-muted text-uppercase">{{ $v->type == '01' ? 'Factura' : 'Boleta' }}</small>
                            </td>
                            <td>
                                <div class="fw-semibold">{{ $v->patient->last_name }} {{ $v->patient->first_name }}</div>
                                <small class="text-muted">DNI: {{ $v->patient->dni }}</small>
                            </td>
                            <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                            <td><span class="fw-bold text-primary">S/ {{ number_format($v->total, 2) }}</span></td>
                            <td>
                                <span class="badge rounded-pill {{ $v->status === 'paid' ? 'text-bg-success' : 'text-bg-danger' }}">
                                    {{ $v->status === 'paid' ? 'Pagado' : 'Anulado' }}
                                </span>
                            </td>
                            <td class="text-end pe-4">
                                <div class="btn-group">
                                    <a href="{{ route('vouchers.show', $v) }}" target="_blank" class="btn btn-sm btn-outline-dark" title="Ver ticket">
                                        <i class="fa-solid fa-receipt"></i>
                                    </a>
                                    <a href="{{ route('vouchers.print', $v) }}" target="_blank" class="btn btn-sm btn-outline-primary" title="Imprimir ticket">
                                        <i class="fa-solid fa-print"></i>
                                    </a>
                                    <a href="{{ route('vouchers.edit', $v) }}" class="btn btn-sm btn-outline-secondary" title="Editar orden">
                                        <i class="fa-solid fa-pen-to-square"></i>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">No se encontraron vouchers con los filtros actuales.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $vouchers->appends(request()->query())->links() }}
    </div>
</div>
@endsection