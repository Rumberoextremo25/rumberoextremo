<!DOCTYPE html>
<html>
<head>
    <title>Reporte de Ventas</title>
    <style>
        /* Estilos básicos para el PDF */
        body {
            font-family: sans-serif;
            font-size: 12px;
        }
        h1 {
            text-align: center;
            color: #FF4B4B; /* Rojo Rumbero Extremo */
        }
        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 20px;
        }
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .summary {
            margin-top: 30px;
            text-align: right;
            font-size: 14px;
        }
        .footer {
            text-align: center;
            margin-top: 50px;
            font-size: 10px;
            color: #888;
        }
    </style>
</head>
<body>
    <h1>Reporte de Ventas por {{ ucfirst($reportType) }}</h1>
    <p><strong>Período:</strong> {{ $startDate }} al {{ $endDate }}</p>

    <table>
        <thead>
            <tr>
                <th>Período</th>
                <th>Ingresos (USD)</th>
            </tr>
        </thead>
        <tbody>
            @php
                $totalIngresos = 0;
            @endphp
            @foreach($labels as $index => $label)
                <tr>
                    <td>{{ $label }}</td>
                    <td>${{ number_format($chartData[$index], 2) }}</td>
                </tr>
                @php
                    $totalIngresos += $chartData[$index];
                @endphp
            @endforeach
        </tbody>
    </table>

    <div class="summary">
        <strong>Total de Ingresos en el período:</strong> ${{ number_format($totalIngresos, 2) }}
    </div>

    <div class="footer">
        Generado el: {{ \Carbon\Carbon::now()->format('d/m/Y H:i') }}
        <br>
        Rumbero Extremo - Reporte de Ventas
    </div>
</body>
</html>