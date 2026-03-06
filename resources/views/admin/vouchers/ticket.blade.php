<!DOCTYPE html>
<html>
<head>
    <style>
        body { width: 80mm; font-family: monospace; font-size: 12px; margin: 0; padding: 10px; }
        .c { text-align: center; }
        .r { text-align: right; }
        .sep { border-top: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
    </style>
</head>
<body onload="window.print()">
    <div class="c">
        <h3 style="margin:0">CLÍNICA HISLIS</h3>
        <p>{{ $voucher->series }}-{{ $voucher->number }}</p>
    </div>
    <div class="sep"></div>
    <p>PACIENTE: {{ $voucher->patient->last_name }} {{ $voucher->patient->first_name }}</p>
    <p>FECHA: {{ $voucher->created_at->format('d/m/Y H:i') }}</p>
    <div class="sep"></div>
    <table>
        @foreach($voucher->orderItems as $item)
        <tr>
            <td>{{ $item->itemable->name }}</td>
            <td class="r">S/ {{ number_format($item->price, 2) }}</td>
        </tr>
        @endforeach
    </table>
    <div class="sep"></div>
    <div class="r fw-bold">TOTAL: S/ {{ number_format($voucher->total, 2) }}</div>
    <div class="c" style="margin-top:20px">*** Ticket de Control ***</div>
</body>
</html>