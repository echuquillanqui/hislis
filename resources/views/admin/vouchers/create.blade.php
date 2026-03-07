@extends('layouts.app')
@section('content')
<div class="container-fluid py-4" x-data="voucherApp()">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <div>
            <h2 class="fw-bold mb-1">Nueva venta</h2>
            <p class="text-muted mb-0">Registra servicios y exámenes para emitir el voucher.</p>
        </div>
        <a href="{{ route('vouchers.index') }}" class="btn btn-outline-secondary rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i>Volver
        </a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger rounded-4 shadow-sm border-0">
            <strong>No se pudo registrar:</strong>
            <ul class="mb-0 mt-2">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif
    <form action="{{ route('vouchers.store') }}" method="POST">
        @csrf
            <div class="row g-4">
                <div class="col-lg-8">
                    <div class="card border-0 shadow-sm rounded-4 mb-4">
                        <div class="card-body p-4">
                            <h5 class="fw-bold mb-3">1) Paciente</h5>
                            <label class="form-label text-muted">Buscar por DNI o nombre</label>
                            <input type="text" class="form-control form-control-lg" x-model="pQ" @input.debounce.400ms="getPatients()" placeholder="Ej: 74125896 o Pérez">
                            <div x-show="pRes.length > 0" class="list-group mt-2 shadow-sm">
                                <template x-for="p in pRes" :key="p.id">
                                    <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" @click="setPatient(p)">
                                        <div>
                                            <div class="fw-semibold" x-text="p.last_name + ' ' + p.first_name"></div>
                                        </div>
                                        <span class="badge text-bg-secondary" x-text="p.dni"></span>
                                    </button>
                                </template>
                            </div>

                            <div x-show="selP" class="mt-3 p-3 bg-light border rounded-3 d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="small text-muted">Paciente seleccionado</div>
                                    <div class="fw-bold text-primary" x-text="selP?.last_name + ' ' + selP?.first_name"></div>
                                </div>
                                <span class="badge text-bg-dark" x-text="selP?.dni"></span>
                                <input type="hidden" name="patient_id" :value="selP?.id">
                            </div>
                    </div>
                </div>
            

                <div class="card border-0 shadow-sm rounded-4">
                    <div class="card-body p-4">
                        <h5 class="fw-bold mb-3">2) Conceptos</h5>
                        <input type="text" class="form-control form-control-lg mb-3" placeholder="Buscar examen o servicio..." x-model="iQ" @input="filterI()">

                        <div x-show="iRes.length > 0" class="list-group mb-3 shadow-sm">
                            <template x-for="i in iRes" :key="i.id + i.type">
                                <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between align-items-center" @click="addI(i)">
                                    <div>
                                        <div class="fw-semibold" x-text="i.name"></div>
                                        <small class="text-muted" x-text="i.type === 'lab' ? 'Laboratorio' : 'Consulta/Servicio'"></small>
                                    </div>
                                    <strong x-text="'S/ ' + i.price.toFixed(2)"></strong>
                                </button>
                            </template>
                         </div>

                        <div class="table-responsive">
                            <table class="table align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th>Descripción</th>
                                        <th class="text-end">Precio</th>
                                        <th class="text-center" style="width: 60px;">Quitar</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <template x-if="cart.length === 0">
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-4">Aún no agregaste conceptos.</td>
                                        </tr>
                                    </template>
                                    <template x-for="(c, index) in cart" :key="index">
                                        <tr>
                                            <td>
                                                <div class="fw-semibold" x-text="c.name"></div>
                                                <small class="text-muted" x-text="c.type === 'lab' ? 'Laboratorio' : 'Servicio'"></small>
                                            </td>
                                            <td class="text-end" x-text="'S/ ' + c.price.toFixed(2)"></td>
                                            <td class="text-center">
                                                <button type="button" class="btn btn-sm btn-outline-danger" @click="removeI(index)">
                                                    <i class="fas fa-trash"></i>
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

                        <label class="form-label fw-semibold">Tipo de comprobante</label>
                        <select name="type" class="form-select form-select-lg mb-4" required>
                            <option value="03">Boleta</option>
                            <option value="01">Factura</option>
                        </select>

                        <template x-for="(i, idx) in cart" :key="idx">
                            <div>
                                <input type="hidden" :name="'items['+idx+'][id]'" :value="i.id">
                                <input type="hidden" :name="'items['+idx+'][type]'" :value="i.type">
                                <input type="hidden" :name="'items['+idx+'][price]'" :value="i.price">
                            </div>
                        </template>
                        <input type="hidden" name="total" :value="total">

                    <button class="btn btn-primary btn-lg w-100 fw-bold" :disabled="!selP || cart.length === 0">
                            <i class="fa-solid fa-floppy-disk me-2"></i>Registrar voucher
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function voucherApp() {
    return {
        pQ: '', pRes: [], selP: null,
        iQ: '', iRes: [], cart: [], total: 0,
        allItems: @js($allItems),
        async getPatients() {
            if (this.pQ.length < 2) {
                this.pRes = [];
                return;
            }

            const r = await fetch(`{{ route('vouchers.search-patients') }}?q=${encodeURIComponent(this.pQ)}`);
            this.pRes = await r.json();
        },
        setPatient(p) {
            this.selP = p;
            this.pRes = [];
            this.pQ = `${p.last_name} ${p.first_name}`;
        },
        filterI() {
            if (this.iQ.length < 2) {
                this.iRes = [];
                return;
            }

            const q = this.iQ.toLowerCase();
            this.iRes = this.allItems
                .filter(i => i.name.toLowerCase().includes(q))
                .slice(0, 8);
        },
        addI(i) {
            this.cart.push({ ...i });
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