@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Inventarios mensuales</h1>
            <p class="text-muted mb-0">Conteo físico, diferencias, valorización y cierre mensual.</p>
        </div>
        <a href="{{ route('inventory.index') }}" class="btn btn-outline-secondary"><i class="fa-solid fa-arrow-left me-1"></i> Inventario</a>
    </div>

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form method="POST" action="{{ route('monthly-inventory-counts.store') }}" class="row g-3 align-items-end">
                @csrf
                <div class="col-md-5">
                    <label class="form-label fw-bold">Almacén</label>
                    <select name="warehouse_id" class="form-select" required>
                        @foreach($warehouses as $warehouse)
                            <option value="{{ $warehouse->id }}">{{ $warehouse->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label fw-bold">Mes</label>
                    <input type="month" name="period_month" class="form-control" value="{{ now()->format('Y-m') }}" required>
                </div>
                <div class="col-md-3">
                    <button class="btn btn-primary w-100"><i class="fa-solid fa-camera me-1"></i> Preparar snapshot</button>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Periodo</th><th>Almacén</th><th>Estado</th><th class="text-end">Acciones</th></tr></thead>
                <tbody>
                    @forelse($counts as $count)
                        <tr>
                            <td>{{ $count->period_month->format('Y-m') }}</td>
                            <td>{{ $count->warehouse->name }}</td>
                            <td><span class="badge bg-{{ $count->status === 'closed' ? 'success' : ($count->status === 'counted' ? 'warning' : 'secondary') }}">{{ strtoupper($count->status) }}</span></td>
                            <td class="text-end"><a class="btn btn-sm btn-outline-primary" href="{{ route('monthly-inventory-counts.show', $count) }}">Ver conteo</a></td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="text-center text-muted py-4">Sin inventarios mensuales registrados.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="card-footer">{{ $counts->links() }}</div>
    </div>
</div>
@endsection
