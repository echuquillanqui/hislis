@extends('layouts.app')
@section('content')
<div class="container py-4">
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white py-3">
            <h5 class="fw-bold mb-0">Editar Orden: {{ $voucher->series }}-{{ $voucher->number }}</h5>
        </div>
        <div class="card-body">
            <p><b>Paciente:</b> {{ $voucher->patient->last_name }} {{ $voucher->patient->first_name }}</p>
            
            <table class="table align-middle">
                <thead><tr><th>Ítem</th><th>Tipo</th><th class="text-end">Precio</th><th class="text-center">Acción</th></tr></thead>
                <tbody>
                    @foreach($voucher->orderItems as $item)
                    <tr id="item-{{ $item->id }}">
                        <td>{{ $item->itemable->name }}</td>
                        <td>{{ $item->itemable_type == 'App\Models\LabExam' ? 'LAB' : 'CONSULTA' }}</td>
                        <td class="text-end">S/ {{ number_format($item->price, 2) }}</td>
                        <td class="text-center">
                            <button class="btn btn-outline-danger btn-sm border-0" onclick="removeItem('{{ $item->id }}')">
                                <i class="fas fa-trash-alt"></i> </button>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function removeItem(id) {
    Swal.fire({
        title: '¿Eliminar este ítem?',
        text: "Esta acción no se puede revertir.",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`{{ url('admin/vouchers/item') }}/${id}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Accept': 'application/json' }
            })
            .then(async res => {
                const data = await res.json();
                if (res.ok) {
                    Swal.fire('¡Eliminado!', '', 'success');
                    document.getElementById(`item-${id}`).remove();
                } else {
                    Swal.fire('Error', data.message, 'error');
                }
            });
        }
    });
}
</script>
@endsection