<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas - Rumbero Extremo</title>
    {{-- Google Fonts link is here, but for PDF generation, you often need to
         embed fonts or use a locally accessible font. Check your PDF library's
         documentation (e.g., Dompdf font installation). If issues arise,
         fall back to a generic font like 'sans-serif'. --}}
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">

    {{-- Link to your dedicated CSS file for the PDF report --}}
    <link rel="stylesheet" href="{{ public_path('css/pdf/sales-report.css') }}">
    {{-- IMPORTANT: Use public_path() for CSS in PDF views if your PDF generator
       needs a direct file path, instead of asset() which provides a URL.
       If your PDF generator handles URLs correctly, asset() might work. --}}

</head>
<body>
    <div class="header">
        {{-- If you have a logo, ensure its path is accessible for the PDF generator --}}
        {{-- Example using public_path: --}}
        {{-- <img src="{{ public_path('images/logo-rumbero-extremo.png') }}" alt="Rumbero Extremo Logo" class="logo"> --}}
        <h1>Reporte de Ventas</h1>
        <h2>Visión General Detallada</h2>
        <p class="period-info">
            Período: {{ \Carbon\Carbon::parse($startDate)->format('d/m/Y') }} al {{ \Carbon\Carbon::parse($endDate)->format('d/m/Y') }}
            @if ($reportType && $reportType != 'monthly')
                <br>Desglosado por: {{ ucfirst($reportType) }}
            @endif
        </p>
    </div>

    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th>Ingresos (USD)</th>
                <th>Ingresos (VES)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalIngresosUsd = 0;
            @endphp
            @foreach($labels as $index => $label)
                @php
                    $ingresoUsd = $chartData[$index];
                    $ingresoVes = $ingresoUsd * $exchangeRateVes; // Convertir a VES
                    $totalIngresosUsd += $ingresoUsd;
                @endphp
                <tr>
                    <td>{{ $label }}</td>
                    <td>${{ number_format($ingresoUsd, 2, ',', '.') }}</td>
                    <td>Bs. {{ number_format($ingresoVes, 2, ',', '.') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-box">
        <strong>Total de Ingresos en el Período:</strong>
        <div class="total-amount-usd">${{ number_format($totalIngresosUsd, 2, ',', '.') }}</div>
        <div class="total-amount-ves">Bs. {{ number_format($totalIngresosUsd * $exchangeRateVes, 2, ',', '.') }}</div>
        <div class="exchange-rate">
            (Tasa de cambio: 1 USD = Bs. {{ number_format($exchangeRateVes, 2, ',', '.') }})
        </div>
    </div>

    <div class="footer">
        Reporte generado por Rumbero Extremo el {{ \Carbon\Carbon::now()->format('d/m/Y') }} a las {{ \Carbon\Carbon::now()->format('H:i') }} (Hora de Venezuela).
    </div>
</body>
</html>