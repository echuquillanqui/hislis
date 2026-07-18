@extends('layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold mb-1"><i class="fa-solid fa-chart-line text-primary me-2"></i>Dashboard gerencial</h1>
            <p class="text-muted mb-0">KPIs, comparaciones, alertas y exportación para la fase 13.</p>
        </div>
        <a class="btn btn-outline-primary" href="{{ route('dashboard.management.export', request()->query()) }}"><i class="fa-solid fa-file-csv me-1"></i> Exportar CSV</a>
    </div>

    <form class="card border-0 shadow-sm mb-4" method="GET">
        <div class="card-body row g-3 align-items-end">
            <div class="col-md-3"><label class="form-label fw-semibold">Desde</label><input type="date" name="from" value="{{ $metrics['period']['from'] }}" class="form-control"></div>
            <div class="col-md-3"><label class="form-label fw-semibold">Hasta</label><input type="date" name="to" value="{{ $metrics['period']['to'] }}" class="form-control"></div>
            <div class="col-md-2"><button class="btn btn-primary w-100"><i class="fa-solid fa-filter me-1"></i> Filtrar</button></div>
        </div>
    </form>

    <ul class="nav nav-tabs mb-3" role="tablist">
        <li class="nav-item"><button class="nav-link active" data-bs-toggle="tab" data-bs-target="#kpis" type="button">KPIs</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#comparaciones" type="button">Comparaciones</button></li>
        <li class="nav-item"><button class="nav-link" data-bs-toggle="tab" data-bs-target="#alertas" type="button">Alertas</button></li>
    </ul>

    <div class="tab-content">
        <div class="tab-pane fade show active" id="kpis">
            <div class="row g-3">
                @foreach($metrics['kpis'] as $key => $value)
                    <div class="col-md-3"><div class="card border-0 shadow-sm h-100"><div class="card-body"><div class="text-muted small text-uppercase">{{ str_replace('_', ' ', $key) }}</div><div class="fs-3 fw-bold">{{ is_float($value) ? number_format($value, 2) : $value }}</div></div></div></div>
                @endforeach
            </div>
        </div>
        <div class="tab-pane fade" id="comparaciones">
            <div class="card border-0 shadow-sm"><div class="card-body"><h5>Ingresos vs egresos</h5><p class="mb-1">Ingresos: S/ {{ number_format($metrics['income_vs_expenses']['income'], 2) }}</p><p>Egresos: S/ {{ number_format($metrics['income_vs_expenses']['expenses'], 2) }}</p><h5>Órdenes por estado</h5>@forelse($metrics['orders_by_status'] as $status => $count)<span class="badge bg-secondary me-1">{{ $status }}: {{ $count }}</span>@empty <span class="text-muted">Sin órdenes en el periodo.</span>@endforelse</div></div>
        </div>
        <div class="tab-pane fade" id="alertas">
            <div class="card border-0 shadow-sm"><div class="card-body"><h5>Stock bajo</h5>@forelse($metrics['alerts']['low_stock'] as $product)<div class="alert alert-warning py-2 mb-2">{{ $product }}</div>@empty <p class="text-muted">Sin alertas de stock bajo.</p>@endforelse <p class="mb-0">Inventarios pendientes de cierre: <strong>{{ $metrics['alerts']['inventory_counts_pending_close'] }}</strong></p></div></div>
        </div>
    </div>
</div>
@endsection
