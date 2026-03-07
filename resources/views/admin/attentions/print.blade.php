<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informe Médico</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; color: #212529; }
        .header { text-align: center; margin-bottom: 16px; }
        .meta { font-size: 12px; margin-bottom: 16px; }
        .meta p { margin: 2px 0; }
        .row { border-bottom: 1px solid #e9ecef; padding: 8px 0; }
        .label { font-weight: 700; text-transform: uppercase; font-size: 12px; color: #495057; }
        .value { white-space: pre-wrap; margin-top: 2px; }
    </style>
</head>
<body onload="window.print()">
    <div class="header">
        <h2 style="margin:0">CLÍNICA HISLIS</h2>
        <p style="margin:4px 0 0 0;">INFORME DE ATENCIÓN</p>
    </div>

    <div class="meta">
        <p><strong>Paciente:</strong> {{ $item->voucher->patient->last_name }} {{ $item->voucher->patient->first_name }}</p>
        <p><strong>DNI:</strong> {{ $item->voucher->patient->dni }}</p>
        <p><strong>Servicio:</strong> {{ $item->itemable->name }}</p>
        <p><strong>Fecha:</strong> {{ $result->updated_at?->format('d/m/Y H:i') }}</p>
    </div>

    @forelse($content as $key => $value)
        <div class="row">
            <div class="label">{{ str_replace('_', ' ', $key) }}</div>
            <div class="value">{{ is_array($value) ? json_encode($value, JSON_UNESCAPED_UNICODE) : $value }}</div>
        </div>
    @empty
        <p>No hay contenido registrado.</p>
    @endforelse
</body>
</html>