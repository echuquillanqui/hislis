@extends('layouts.app')

@section('content')
<div class="container-fluid py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Editar orden</h4>
            <p class="text-muted mb-0">{{ $voucher->series }}-{{ $voucher->number }}</p>
        </div>
        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-4">
            <div class="mb-3">
                <span class="text-muted">Paciente</span>
                <div class="fw-bold">{{ $voucher->patient->last_name }} {{ $voucher->patient->first_name }}</div>
            </div>

            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead>
                        <tr>
                            <th>Ítem</th>
                            <th>Tipo</th>
                            <th class="text-end">Precio</th>
                            <th class="text-center">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($voucher->orderItems as $item)
                            <tr id="item-{{ $item->id }}">
                                <td>{{ $item->itemable->name }}</td>
                                <td>
                                    <span class="badge {{ $item->itemable_type == 'App\Models\LabExam' ? 'text-bg-info' : 'text-bg-secondary' }}">
                                        {{ $item->itemable_type == 'App\Models\LabExam' ? 'LAB' : 'CONSULTA' }}
                                    </span>
                                </td>
                                <td class="text-end">S/ {{ number_format($item->price, 2) }}</td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="removeItem('{{ $item->id }}')">
                                        <i class="fas fa-trash-alt me-1"></i>Eliminar
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="text-center text-muted py-4">No hay ítems en esta orden.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
    function removeItem(id) {
        Swal.fire({
            title: '¿Eliminar este ítem?',
            text: 'Esta acción no se puede revertir.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (!result.isConfirmed) {
                return;
            }
            fetch(`{{ url('admin/vouchers/item') }}/${id}`, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        })
        .then(async (res) => {
            const data = await res.json();
            if (res.ok) {
                Swal.fire('Eliminado', 'El ítem fue retirado de la orden.', 'success');
                document.getElementById(`item-${id}`)?.remove();
                return;
            }

            Swal.fire('No se pudo eliminar', data.message ?? 'Ocurrió un error.', 'error');
        })
        .catch(() => Swal.fire('Error', 'No se pudo completar la solicitud.', 'error'));
        });
    }
</script>
@endsection