@extends('layouts.app')

@section('content')
<div class="container-fluid py-4" x-data="voucherEditApp()">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-1">Editar orden</h4>
            <p class="text-muted mb-0">{{ $voucher->series }}-{{ $voucher->number }}</p>
        </div>
        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Volver
        </a>
    </div>
    @if(session('success'))
        <div class="alert alert-success border-0 shadow-sm rounded-4">{{ session('success') }}</div>
    @endif

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm border-0">
            <strong>No se pudo actualizar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('vouchers.update', $voucher) }}" method="POST">
        @csrf
        @method('PUT')

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="card border-0 shadow-sm rounded-4 mb-4">
                    <div class="card-body p-4">
                        <div class="mb-3">
                            <span class="text-muted">Paciente</span>
                            <div class="fw-bold">{{ $voucher->patient->last_name }} {{ $voucher->patient->first_name }}</div>
                        </div>

                        <h5 class="fw-bold mb-3">Agregar examen/servicio</h5>
                        <input type="text" class="form-control form-control-lg mb-3" placeholder="Buscar examen o servicio..." x-model="iQ" @input="filterI()">

                        <div x-show="iRes.length > 0" class="list-group mb-3 shadow-sm">
                            <template x-for="i in iRes" :key="i.id + i.type">
                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" @click="addI(i)">
                                    <div>
                                        <div class="fw-semibold" x-text="i.name"></div>
                                        <small class="text-muted" x-text="i.type === 'lab' ? 'Laboratorio' : 'Consulta/Servicio'"></small>
                                    </div>
                                    <strong x-text="'S/ ' + Number(i.price).toFixed(2)"></strong>
                                </button>
                            </template>
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
                                    <template x-if="cart.length === 0">
                                        <tr>
                                            <td colspan="4" class="text-center text-muted py-4">No hay ítems en esta orden.</td>
                                        </tr>
                                    </template>

                                    <template x-for="(c, index) in cart" :key="c.uid">
                                        <tr>
                                            <td x-text="c.name"></td>
                                            <td>
                                                <span class="badge" :class="c.type === 'lab' ? 'text-bg-info' : 'text-bg-secondary'" x-text="c.type === 'lab' ? 'LAB' : 'CONSULTA'"></span>
                                            </td>
                                            <td class="text-end" x-text="'S/ ' + Number(c.price).toFixed(2)"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeI(index)">
                                                    <i class="fas fa-trash-alt me-1"></i>Quitar
                                                </button>
                                            </td>
                                        </tr>
                                    </template>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card border-0 shadow rounded-4 sticky-top" style="top: 90px;">
                    <div class="card-body p-4">
                        <h6 class="text-muted text-uppercase mb-2">Resumen</h6>
                        <h1 class="fw-bold text-primary mb-3">S/ <span x-text="total.toFixed(2)"></span></h1>

                        <template x-for="(i, idx) in cart" :key="i.uid">
                            <div>
                                <input type="hidden" :name="'items['+idx+'][id]'" :value="i.id">
                                <input type="hidden" :name="'items['+idx+'][type]'" :value="i.type">
                                <input type="hidden" :name="'items['+idx+'][price]'" :value="i.price">
                                <input type="hidden" :name="'items['+idx+'][order_item_id]'" :value="i.order_item_id ?? ''">
                            </div>
                        </template>
                        <input type="hidden" name="total" :value="total">

                        <button class="btn btn-primary btn-lg w-100 fw-bold" :disabled="cart.length === 0">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Guardar cambios
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    </form>
</div>

<script>
    function voucherEditApp() {
    const existingItems = @js($voucher->orderItems->map(function ($item) {
        return [
            'uid' => 'existing-' . $item->id,
            'order_item_id' => $item->id,
            'id' => $item->itemable_id,
            'type' => $item->itemable_type === App\Models\LabExam::class ? 'lab' : 'service',
            'name' => $item->itemable->name,
            'price' => (float) $item->price,
        ];
    })->values());

    return {
        iQ: '',
        iRes: [],
        allItems: @js($allItems),
        cart: existingItems,
        total: 0,
        nextUid: 1,
        init() {
            this.calc();
        },
        filterI() {
            if (this.iQ.length < 2) {
                this.iRes = [];
                return;
            }

           const q = this.iQ.toLowerCase();
            this.iRes = this.allItems
                .filter(i => i.name.toLowerCase().includes(q))
                .slice(0, 10);
        },
        addI(i) {
            this.cart.push({
                uid: `new-${this.nextUid++}`,
                order_item_id: null,
                id: i.id,
                type: i.type,
                name: i.name,
                price: Number(i.price),
            });

            this.iRes = [];
            this.iQ = '';
            this.calc();
        },
        removeI(index) {
            this.cart.splice(index, 1);
            this.calc();
        },
        calc() {
            this.total = this.cart.reduce((sum, i) => sum + Number(i.price), 0);
        }
    }
}
</script>
@endsection