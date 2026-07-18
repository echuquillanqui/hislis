@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-1">Logística e Inventario</h1>
            <p class="text-muted mb-0">Opciones habilitadas para la fase 1|1 del inventario.</p>
        </div>
    </div>

    <div class="row g-3">
        @foreach ([
            ['id' => 'productos', 'label' => 'Productos', 'value' => $productsCount, 'icon' => 'fa-box-open', 'text' => 'Catálogo base de productos e insumos.'],
            ['id' => 'categorias', 'label' => 'Categorías', 'value' => $categoriesCount, 'icon' => 'fa-tags', 'text' => 'Clasificación logística de reactivos, medicamentos e insumos.'],
            ['id' => 'almacenes', 'label' => 'Almacenes', 'value' => $warehousesCount, 'icon' => 'fa-warehouse', 'text' => 'Puntos físicos o áreas con stock asignado.'],
            ['id' => 'lotes', 'label' => 'Lotes', 'value' => $lotsCount, 'icon' => 'fa-vials', 'text' => 'Control por lote, vencimiento y estado.'],
            ['id' => 'saldos', 'label' => 'Saldos', 'value' => $balancesCount, 'icon' => 'fa-scale-balanced', 'text' => 'Existencias por producto, almacén y lote.'],
            ['id' => 'kardex', 'label' => 'Kardex', 'value' => $kardexCount, 'icon' => 'fa-clipboard-list', 'text' => 'Trazabilidad auditable de movimientos.'],
        ] as $card)
            <div class="col-12 col-md-6 col-xl-4">
                <div id="{{ $card['id'] }}" class="card h-100 shadow-sm border-0">
                    <div class="card-body">
                        <div class="d-flex align-items-center gap-3">
                            <div class="rounded-circle bg-primary-subtle text-primary d-flex align-items-center justify-content-center" style="width: 48px; height: 48px;">
                                <i class="fa-solid {{ $card['icon'] }}"></i>
                            </div>
                            <div>
                                <div class="text-muted small text-uppercase fw-bold">{{ $card['label'] }}</div>
                                <div class="fs-3 fw-bold">{{ $card['value'] }}</div>
                            </div>
                        </div>
                        <p class="text-muted mt-3 mb-0">{{ $card['text'] }}</p>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
