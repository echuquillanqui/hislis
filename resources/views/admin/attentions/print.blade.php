<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe Médico</title>
    <style>
        @page { size: A4; margin: 2mm; }
        body { font-family: Arial, sans-serif; margin: 14px; color: #1f2937; font-size: 12px; }
        .page { min-height: 96vh; display: flex; flex-direction: column; }
        .header { border-bottom: 2px solid #111827; padding-bottom: 8px; margin-bottom: 12px; display: flex; align-items: center; justify-content: space-between; gap: 14px; }
        .header-logo { width: 80px; height: 80px; object-fit: contain; flex-shrink: 0; }
        .header-content { text-align: center; flex: 1; }
        .header h2 { margin: 0; font-size: 20px; letter-spacing: 1px; }
        .header p { margin: 4px 0 0 0; font-size: 11px; }

        .patient-data {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 10px;
            margin-bottom: 12px;
            page-break-inside: avoid;
        }
        .patient-card {
            border: 1px solid #d6d9de;
            border-radius: 8px;
            background: linear-gradient(180deg, #f9fafb 0%, #ffffff 100%);
            padding: 10px;
            box-shadow: inset 0 1px 0 #ffffff;
        }

        .section-title { font-size: 11px; font-weight: 700; text-transform: uppercase; color: #374151; margin-bottom: 4px; }
        .field { margin: 2px 0; }
        .field strong { display: inline-block; min-width: 72px; }

        .triage-box { border: 1px solid #9ca3af; margin-bottom: 12px; page-break-inside: avoid; }
        .triage-header { padding: 8px 10px; border-bottom: 1px solid #d1d5db; font-weight: 700; text-transform: uppercase; }
        .triage-grid { display: grid; grid-template-columns: repeat(3, minmax(0, 1fr)); }
        .triage-item { border-right: 1px solid #e5e7eb; border-bottom: 1px solid #e5e7eb; padding: 8px 10px; min-height: 0; height: auto; }
        .triage-item:nth-child(3n) { border-right: 0; }
        .triage-label { font-size: 10px; text-transform: uppercase; color: #4b5563; font-weight: 700; }
        .triage-value { margin-top: 4px; font-weight: 700; line-height: 1.35; word-break: break-word; }
        .triage-notes { border-top: 1px solid #e5e7eb; padding: 8px 10px; }

        .content { border: 1px solid #9ca3af; margin-bottom: 14px; }
        .content .row { border-bottom: 1px solid #e5e7eb; padding: 8px 10px; }
        .content .row:last-child { border-bottom: 0; }
        .label { font-weight: 700; text-transform: uppercase; font-size: 11px; color: #4b5563; }
        .value { white-space: pre-wrap; margin-top: 3px; }

        .footer-signature { margin-top: auto; padding-top: 14px; display: flex; justify-content: center; }
        .stamp-box {
            width: 340px;
            border: 2px dashed #6b7280;
            border-radius: 8px;
            padding: 16px;
            text-align: center;
            min-height: 150px;
        }
        .stamp-muted { color: #6b7280; font-size: 11px; font-weight: 700; text-transform: uppercase; }
        .signature-space { height: 56px; margin: 10px 0 12px; border-bottom: 1px solid #111827; }
        .stamp-line { font-weight: 700; text-transform: uppercase; margin: 2px 0; font-size: 11px; }

        .company-data {
            border-top: 1px solid #d1d5db;
            margin-top: 10px;
            padding-top: 8px;
            text-align: center;
            font-size: 10px;
            color: #374151;
            line-height: 1.35;
        }

        .single-page-print {
            min-height: calc(297mm - 20mm);
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            page-break-inside: avoid;
            break-inside: avoid;
        }
        @media print {
            html, body { width: 210mm; height: 297mm; overflow: hidden; }
            .single-page-print { height: calc(297mm - 20mm); overflow: hidden; }
            .footer-signature { page-break-inside: avoid; }
        }
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

        $hospitalName = $setting?->hospital_name ?: 'CLÍNICA HISLIS';
        $companyDocument = $setting?->ruc_nit ?: 'SIN RUC/NIT';
        $companyAddress = $setting?->address ?: 'Dirección no registrada';
        $companyPhone = $setting?->phone ?: 'Teléfono no registrado';
        $logoPath = $setting?->logo_path ? public_path('storage/' . $setting->logo_path) : null;

        $professionalRole = 'Profesional de Salud';
        if ($doctor?->hasRole('MEDICO')) {
            $professionalRole = 'Médico';
        } elseif ($doctor?->hasRole('ENFERMERO')) {
            $professionalRole = 'Enfermero';
        }
    @endphp
    <div class="page single-page-print">
        <div>
            <div class="header">
                @if($logoPath && file_exists($logoPath))
                    <img src="{{ $logoPath }}" alt="Logo" class="header-logo">
                @endif
                <div class="header-content">
                    <h2>{{ strtoupper($hospitalName) }}</h2>
                    <p>INFORME DE ATENCIÓN</p>
                </div>
            </div>

             <div class="patient-data">
                <div class="patient-card">
                    <div class="section-title">Datos del paciente</div>
                    <div class="field"><strong>Nombres:</strong> {{ $patient->first_name }}</div>
                    <div class="field"><strong>Apellidos:</strong> {{ $patient->last_name }}</div>
                    <div class="field"><strong>DNI:</strong> {{ $patient->dni }}</div>
                </div>
                <div class="patient-card">
                    <div class="section-title">Datos clínicos</div>
                    <div class="field"><strong>Servicio:</strong> {{ $item->itemable->name }}</div>
                    <div class="field"><strong>Fecha:</strong> {{ $result->updated_at?->format('d/m/Y H:i') }}</div>
                    <div class="field"><strong>Sexo:</strong> {{ $gender }}</div>
                </div>
                <div class="patient-card">
                    <div class="section-title">Identificación</div>
                    <div class="field"><strong>Edad:</strong> {{ $age }} años</div>
                    <div class="field"><strong>Historia:</strong> #{{ $patient->id }}</div>
                </div>
            </div>

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