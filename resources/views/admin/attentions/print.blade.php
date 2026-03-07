<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe Médico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 18px; color: #1f2937; font-size: 12px; }
        .header { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; }
        .header h2 { margin: 0; font-size: 20px; letter-spacing: 1px; }
        .header p { margin: 4px 0 0 0; font-size: 11px; }

        .grid-3 { width: 100%; border-collapse: collapse; margin-bottom: 12px; }
        .grid-3 td { width: 33.33%; border: 1px solid #d1d5db; vertical-align: top; padding: 8px; }

        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #374151; margin-bottom: 4px; }
        .field { margin: 2px 0; }
        .field strong { display: inline-block; min-width: 72px; }

        .content { border: 1px solid #9ca3af; }
        .content .row { border-bottom: 1px solid #e5e7eb; padding: 8px 10px; }
        .content .row:last-child { border-bottom: 0; }
        .label { font-weight: 700; text-transform: uppercase; font-size: 11px; color: #4b5563; }
        .value { white-space: pre-wrap; margin-top: 3px; }

        .signature-wrap { margin-top: 30px; display: flex; justify-content: flex-end; }
        .stamp-box {
            width: 280px;
            border: 2px dashed #6b7280;
            border-radius: 8px;
            padding: 14px;
            text-align: center;
            min-height: 110px;
        }
        .stamp-line { font-weight: 700; text-transform: uppercase; margin: 2px 0; }
        .stamp-muted { color: #6b7280; font-size: 11px; }
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

    <div class="signature-wrap">
        <div class="stamp-box">
            <div class="stamp-muted">SELLO Y FIRMA</div>
            <div class="stamp-line">DR. {{ strtoupper($doctor->name ?? 'USUARIO') }}</div>
            <div class="stamp-line">{{ strtoupper($professionalRole) }}</div>
            <div class="stamp-line">COLEGIATURA: {{ strtoupper($doctor->colegiatura ?? 'NO REGISTRA') }}</div>
        </div>
    </div>
</body>
</html>