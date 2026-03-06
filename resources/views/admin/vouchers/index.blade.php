@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-end mb-4">
        <div>
            <h2 class="fw-bold mb-0">Caja y Facturación</h2>
            <p class="text-muted">Gestión de ingresos y órdenes médicas del día</p>
        </div>
        <a href="{{ route('vouchers.create') }}" class="btn btn-primary btn-lg rounded-pill shadow-sm px-4">
            <i class="fa-solid fa-cart-plus me-2"></i>Nueva Atención
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('vouchers.index') }}" method="GET" class="row g-3">
                <div class="col-md-5">
                    <div class="input-group bg-light rounded-3 overflow-hidden">
                        <span class="input-group-text border-0 bg-transparent ps-3"><i class="fa-solid fa-magnifying-glass text-muted"></i></span>
                        <input type="text" name="search" class="form-control border-0 bg-transparent py-2 shadow-none" 
                               placeholder="Paciente o número de documento..." value="{{ request('search') }}">
                    </div>
                </div>
                <div class="col-md-4">
                    <input type="date" name="date" class="form-control border-0 bg-light py-2 shadow-none" value="{{ request('date') }}">
                </div>
                <div class="col-md-3">
                    <button class="btn btn-dark w-100 rounded-3 py-2">Aplicar Filtros</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
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
                    @foreach($vouchers as $v)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">{{ $v->series }}-{{ $v->number }}</div>
                            <small class="text-muted text-uppercase">{{ $v->type == '01' ? 'Factura' : 'Boleta' }}</small>
                        </td>
                        <td>
                            <div class="fw-bold text-dark">{{ $v->patient->last_name }} {{ $v->patient->first_name }}</div>
                            <div class="small text-muted">{{ $v->patient->dni }}</div>
                        </td>
                        <td>{{ $v->created_at->format('d/m/Y H:i') }}</td>
                        <td><span class="fw-bold text-primary">S/ {{ number_format($v->total, 2) }}</span></td>
                        <td>
                            <span class="badge rounded-pill {{ $v->status == 'paid' ? 'bg-success' : 'bg-danger' }}">
                                {{ $v->status == 'paid' ? 'Pagado' : 'Anulado' }}
                            </span>
                        </td>
                        <td class="text-end pe-4">
                            <div class="btn-group shadow-sm rounded-pill overflow-hidden">
                                <a href="{{ route('vouchers.show', $v) }}" target="_blank" class="btn btn-white btn-sm border-end" title="Imprimir Ticket">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                <a href="{{ route('vouchers.edit', $v) }}" class="btn btn-white btn-sm" title="Editar Orden">
                                    <i class="fa-solid fa-pen-to-square"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    <div class="mt-4">
        {{ $vouchers->appends(request()->query())->links() }}
    </div>
</div>
@endsection