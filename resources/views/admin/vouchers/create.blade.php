@extends('layouts.app')
@section('content')
<div class="container py-4" x-data="voucherApp()">
    <form action="{{ route('vouchers.store') }}" method="POST">
        @csrf
        <div class="row">
            <div class="col-md-8">
                <div class="card border-0 shadow-sm rounded-4 p-4 mb-4">
                    <label class="fw-bold mb-2">Buscar Paciente (DNI o Nombre)</label>
                    <input type="text" class="form-control" x-model="pQ" @input.debounce.500ms="getPatients()">
                    
                    <div x-show="pRes.length > 0" class="list-group mt-2 shadow">
                        <template x-for="p in pRes" :key="p.id">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between" @click="setPatient(p)">
                                <span x-text="p.last_name + ' ' + p.first_name"></span>
                                <span class="badge bg-secondary" x-text="p.dni"></span>
                            </button>
                        </template>
                    </div>

                    <div x-show="selP" class="mt-3 p-3 bg-light border-start border-primary border-4 rounded">
                        <span class="fw-bold text-primary" x-text="'Seleccionado: ' + selP?.last_name + ' ' + selP?.first_name"></span>
                        <input type="hidden" name="patient_id" :value="selP?.id">
                    </div>
                </div>

                <div class="card border-0 shadow-sm rounded-4 p-4">
                    <input type="text" class="form-control mb-3" placeholder="Filtrar servicios..." x-model="iQ" @input="filterI()">
                    <div x-show="iRes.length > 0" class="list-group mb-3 shadow-sm">
                        <template x-for="i in iRes" :key="i.id + i.type">
                            <button type="button" class="list-group-item list-group-item-action d-flex justify-content-between" @click="addI(i)">
                                <span x-text="i.name"></span>
                                <b x-text="'S/ ' + i.price"></b>
                            </button>
                        </template>
                    </div>

                    <table class="table">
                        <thead><tr><th>Descripción</th><th class="text-end">Precio</th><th></th></tr></thead>
                        <tbody>
                            <template x-for="(c, index) in cart" :key="index">
                                <tr>
                                    <td x-text="c.name"></td>
                                    <td class="text-end" x-text="'S/ ' + c.price.toFixed(2)"></td>
                                    <td class="text-center"><button type="button" class="btn btn-sm text-danger" @click="cart.splice(index, 1); calc()"><i class="fas fa-trash"></i></button></td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="col-md-4">
                <div class="card border-0 shadow-lg bg-dark text-white p-4 rounded-4">
                    <h2 class="text-primary fw-bold">S/ <span x-text="total.toFixed(2)"></span></h2>
                    <select name="type" class="form-select bg-secondary border-0 text-white my-3">
                        <option value="03">Boleta</option>
                        <option value="01">Factura</option>
                    </select>
                    
                    <template x-for="(i, idx) in cart">
                        <div>
                            <input type="hidden" :name="'items['+idx+'][id]'" :value="i.id">
                            <input type="hidden" :name="'items['+idx+'][type]'" :value="i.type">
                            <input type="hidden" :name="'items['+idx+'][price]'" :value="i.price">
                        </div>
                    </template>
                    <input type="hidden" name="total" :value="total">

                    <button class="btn btn-primary w-100 py-3 fw-bold" :disabled="!selP || cart.length == 0">REGISTRAR</button>
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
            if(this.pQ.length < 2) return this.pRes = [];
            const r = await fetch(`{{ url('admin/vouchers/search-patients') }}?q=${this.pQ}`);
            this.pRes = await r.json();
        },
        setPatient(p) { this.selP = p; this.pRes = []; this.pQ = ''; },
        filterI() {
            this.iRes = this.iQ.length < 2 ? [] : this.allItems.filter(i => i.name.toLowerCase().includes(this.iQ.toLowerCase())).slice(0, 5);
        },
        addI(i) { this.cart.push({...i}); this.iRes = []; this.iQ = ''; this.calc(); },
        calc() { this.total = this.cart.reduce((sum, i) => sum + i.price, 0); }
    }
}
</script>
@endsection