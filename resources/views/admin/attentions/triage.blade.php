@extends('layouts.app')

@section('content')
<div class="container mx-auto py-8 px-4">
    <div class="max-w-4xl mx-auto bg-white shadow-lg rounded-xl overflow-hidden">
        
        <div class="bg-gradient-to-r from-blue-700 to-blue-500 p-6 text-white">
            <h2 class="text-2xl font-bold uppercase tracking-wide">Formulario de Triaje</h2>
            <p class="text-blue-100">
                Paciente: <span class="font-semibold">{{ $triage->patient->first_name }} {{ $triage->patient->last_name }}</span> | 
                DNI: {{ $triage->patient->dni }}
            </p>
        </div>

        <form action="{{ route('triage.update', $triage->id) }}" method="POST" class="p-8">
            @csrf
            @method('PUT')

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                
                <div class="col-span-full border-b border-gray-200 pb-2">
                    <h3 class="text-lg font-bold text-gray-700">Funciones Vitales</h3>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">Temperatura (°C)</label>
                    <input type="number" name="temp" step="0.1" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">Presión Arterial (PA)</label>
                    <input type="text" name="bp" placeholder="120/80" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">Frec. Cardíaca (LPM)</label>
                    <input type="number" name="hr" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">Frec. Respiratoria (RPM)</label>
                    <input type="number" name="rr" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="col-span-full border-b border-gray-200 pb-2 mt-4">
                    <h3 class="text-lg font-bold text-gray-700">Antropometría</h3>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">Peso (Kg)</label>
                    <input type="number" id="weight" name="weight" step="0.01" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="flex flex-col">
                    <label class="text-sm font-semibold text-gray-600 mb-1">Talla (Metros)</label>
                    <input type="number" id="height" name="height" step="0.01" placeholder="Ej: 1.75" class="border-gray-300 rounded-lg shadow-sm focus:ring-blue-500 focus:border-blue-500" required>
                </div>

                <div class="col-span-2 bg-blue-50 p-4 rounded-lg flex items-center justify-around border border-blue-200">
                    <div class="text-center">
                        <span class="block text-xs font-bold text-blue-600 uppercase">IMC (BMI)</span>
                        <span id="bmi_val" class="text-2xl font-black text-blue-800">0.00</span>
                    </div>
                    <div id="bmi_tag" class="px-3 py-1 rounded text-xs font-bold bg-gray-200 text-gray-600">
                        SIN DATOS
                    </div>
                </div>

            </div>

            <div class="mt-10 pt-6 border-t flex justify-between items-center">
                <a href="{{ route('attentions.index') }}" class="text-gray-500 hover:text-gray-700 font-medium">
                    ← Volver al Monitor
                </a>
                <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white px-8 py-3 rounded-xl font-bold shadow-lg transition duration-200 transform hover:-translate-y-1">
                    Guardar Triaje y Finalizar
                </button>
            </div>
        </form>
    </div>
</div>



<script>
    const w = document.getElementById('weight');
    const h = document.getElementById('height');
    const b = document.getElementById('bmi_val');
    const t = document.getElementById('bmi_tag');

    function calc() {
        let weight = parseFloat(w.value);
        let height = parseFloat(h.value);
        
        if (weight > 0 && height > 0) {
            let imc = (weight / (height * height)).toFixed(2);
            b.innerText = imc;
            
            if(imc < 18.5) { t.innerText = "Bajo Peso"; t.className = "px-3 py-1 rounded text-xs font-bold bg-yellow-100 text-yellow-700"; }
            else if(imc < 25) { t.innerText = "Normal"; t.className = "px-3 py-1 rounded text-xs font-bold bg-green-100 text-green-700"; }
            else if(imc < 30) { t.innerText = "Sobrepeso"; t.className = "px-3 py-1 rounded text-xs font-bold bg-orange-100 text-orange-700"; }
            else { t.innerText = "Obesidad"; t.className = "px-3 py-1 rounded text-xs font-bold bg-red-100 text-red-700"; }
        }
    }

    w.addEventListener('input', calc);
    h.addEventListener('input', calc);
</script>
@endsection