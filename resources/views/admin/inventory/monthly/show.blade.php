@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Conteo {{ $count->period_month->format('Y-m') }} - {{ $count->warehouse->name }}</h1>
            <p class="text-muted mb-0">Snapshot contra conteo físico y diferencia valorizada.</p>
        </div>
        <a href="{{ route('monthly-inventory-counts.index') }}" class="btn btn-outline-secondary">Volver</a>
    </div>

    <form method="POST" action="{{ route('monthly-inventory-counts.update', $count) }}" class="card shadow-sm border-0">
        @csrf @method('PUT')
        <div class="table-responsive">
            <table class="table align-middle mb-0">
                <thead class="table-light"><tr><th>Producto</th><th>Lote</th><th class="text-end">Snapshot</th><th class="text-end">Conteo físico</th><th class="text-end">Diferencia</th><th class="text-end">Valorización</th></tr></thead>
                <tbody>
                    @foreach($count->lines as $line)
                        <tr>
                            <td>{{ $line->product->name }}</td>
                            <td>{{ $line->lot->lot_number ?? 'SIN-LOTE' }}</td>
                            <td class="text-end">{{ number_format($line->snapshot_quantity, 4) }}</td>
                            <td class="text-end"><input name="counts[{{ $line->id }}]" class="form-control form-control-sm text-end" type="number" step="0.0001" min="0" value="{{ $line->counted_quantity }}" @disabled($count->status === 'closed')></td>
                            <td class="text-end {{ $line->difference_quantity < 0 ? 'text-danger' : 'text-success' }}">{{ number_format($line->difference_quantity, 4) }}</td>
                            <td class="text-end">{{ number_format($line->valuation_amount, 4) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="card-footer d-flex justify-content-end gap-2">
            @if($count->status !== 'closed')
                <button class="btn btn-primary"><i class="fa-solid fa-save me-1"></i> Guardar conteo</button>
            @endif
        </div>
    </form>
    @if($count->status !== 'closed')
        <form method="POST" action="{{ route('monthly-inventory-counts.close', $count) }}" class="text-end mt-3">@csrf<button class="btn btn-success"><i class="fa-solid fa-lock me-1"></i> Cerrar inventario mensual</button></form>
    @endif
</div>
@endsection
