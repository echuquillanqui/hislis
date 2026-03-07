<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe Médico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 14px; color: #1f2937; font-size: 12px; }
        .page { min-height: 96vh; display: flex; flex-direction: column; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 20px; letter-spacing: 1px; }
        .header p { margin: 4px 0 0 0; font-size: 11px; }

        .grid-3 { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .grid-3 td { width: 33.33%; border: 1px solid #d1d5db; vertical-align: top; padding: 8px; }

        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #374151; margin-bottom: 4px; }
        .field { margin: 2px 0; }
        .field strong { display: inline-block; min-width: 72px; }

        .triage-box { border: 1px solid #9ca3af; margin-bottom: 12px; }
        .triage-header { padding: 8px 10px; border-bottom: 1px solid #d1d5db; font-weight: 700; text-transform: uppercase; }
        .triage-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .triage-item { border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 8px 10px; min-height: 56px; }
        .triage-item:nth-child(3n) { border-right: 0; }
        .triage-label { font-size: 10px; text-transform: uppercase; color: #4b5563; font-weight: 700; }
        .triage-value { margin-top: 4px; font-weight: 700; }
        .triage-notes { border-top: 1px solid #e5e7eb; padding: 8px 10px; }

        .content { border: 1px solid #9ca3af; }
        .content .row { border-bottom: 1px solid #e5e7eb; padding: 8px 10px; }
        .content .row:last-child { border-bottom: 0; }
        .label { font-weight: 700; text-transform: uppercase; font-size: 11px; color: #4b5563; }
        .value { white-space: pre-wrap; margin-top: 3px; }

        .footer-signature { margin-top: auto; padding-top: 14px; display: flex; justify-content: flex-end; }
        .stamp-box {
            width: 310px;
            border: 2px dashed #6b7280;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            min-height: 150px;
        }
        .stamp-muted { color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .signature-space { height: 56px; margin: 10px 0 12px; border-bottom: 1px solid #111827; }
        .stamp-line { font-weight: 700; text-transform: uppercase; margin: 2px 0; font-size: 11px; }
    </style>
</head>
<body onload="window.print()">

    @php
        $patient = $item->voucher->patient;
        $gender = match($patient->gender) {
            'M' => 'Masculino',
            'F' => 'Femenino',
            default => 'Otro',
        };

        $age = \Carbon\Carbon::parse($patient->birth_date)->age;

        $professionalRole = 'Profesional de Salud';
        if ($doctor?->hasRole('MEDICO')) {
            $professionalRole = 'Médico';
        } elseif ($doctor?->hasRole('ENFERMERO')) {
            $professionalRole = 'Enfermero';
        }
    @endphp
    <div class="page">
        <div>
            <div class="header">
                <h2>CLÍNICA HISLIS</h2>
                <p>INFORME DE ATENCIÓN</p>
            </div>

            <table class="grid-3">
                <tr>
                    <td>
                        <div class="section-title">Datos del paciente</div>
                        <div class="field"><strong>Nombres:</strong> {{ $patient->first_name }}</div>
                        <div class="field"><strong>Apellidos:</strong> {{ $patient->last_name }}</div>
                        <div class="field"><strong>DNI:</strong> {{ $patient->dni }}</div>
                    </td>
                    <td>
                        <div class="section-title">Datos clínicos</div>
                        <div class="field"><strong>Servicio:</strong> {{ $item->itemable->name }}</div>
                        <div class="field"><strong>Fecha:</strong> {{ $result->updated_at?->format('d/m/Y H:i') }}</div>
                        <div class="field"><strong>Sexo:</strong> {{ $gender }}</div>
                    </td>
                    <td>
                        <div class="section-title">Identificación</div>
                        <div class="field"><strong>Edad:</strong> {{ $age }} años</div>
                        <div class="field"><strong>Historia:</strong> #{{ $patient->id }}</div>
                    </td>
                </tr>
            </table>

            <div class="triage-box">
                <div class="triage-header">Datos de triaje</div>
                <div class="triage-grid">
                    <div class="triage-item">
                        <div class="triage-label">Temperatura</div>
                        <div class="triage-value">{{ $triage?->temp ? $triage->temp . ' °C' : 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Presión arterial</div>
                        <div class="triage-value">{{ $triage?->bp ?? 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Frecuencia cardíaca</div>
                        <div class="triage-value">{{ $triage?->hr ? $triage->hr . ' lpm' : 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Frecuencia respiratoria</div>
                        <div class="triage-value">{{ $triage?->rr ? $triage->rr . ' rpm' : 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Peso</div>
                        <div class="triage-value">{{ $triage?->weight ? $triage->weight . ' kg' : 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Talla</div>
                        <div class="triage-value">{{ $triage?->height ? $triage->height . ' m' : 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Saturación O₂</div>
                        <div class="triage-value">{{ isset($triage?->spo2) ? $triage->spo2 . ' %' : 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">IMC</div>
                        <div class="triage-value">{{ $triage?->bmi ?? 'No registrado' }}</div>
                    </div>
                    <div class="triage-item">
                        <div class="triage-label">Fecha triaje</div>
                        <div class="triage-value">{{ $triage?->updated_at?->format('d/m/Y H:i') ?? 'No registrado' }}</div>
                    </div>
                </div>
                <div class="triage-notes">
                    <div class="triage-label">Observaciones de triaje</div>
                    <div class="value">{{ $triage?->notes ?: 'Sin observaciones registradas.' }}</div>
                </div>
            </div>
        </div>

            <div class="content">
                @forelse($content as $key => $value)
                    <div class="row">
                        <div class="label">{{ str_replace('_', ' ', $key) }}</div>
                        <div class="value">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</div>
                    </div>
                @empty
                    <div class="row">
                        <p>No hay contenido registrado.</p>
                    </div>
                @endforelse
            </div>
    </div>

    <div class="footer-signature">
            <div class="stamp-box">
                <div class="stamp-muted">Sello y firma</div>
                <div class="signature-space"></div>
                <div class="stamp-line">DR. {{ strtoupper($doctor->name ?? 'USUARIO') }}</div>
                <div class="stamp-line">{{ strtoupper($professionalRole) }}</div>
                <div class="stamp-line">Colegiatura: {{ strtoupper($doctor->colegiatura ?? 'NO REGISTRA') }}</div>
            </div>
        </div>
    </div>
</body>
</html>