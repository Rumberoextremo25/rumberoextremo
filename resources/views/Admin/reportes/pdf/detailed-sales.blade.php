<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte Detallado de Ventas</title>
    <style>
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 12px;
            color: #333;
        }
        .header {
            text-align: center;
            margin-bottom: 20px;
            border-bottom: 2px solid #8a2be2;
            padding-bottom: 10px;
        }
        .header h1 {
            color: #8a2be2;
            margin: 0;
            font-size: 24px;
        }
        .header .subtitle {
            color: #666;
            font-size: 14px;
        }
        .exchange-rate {
            background-color: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            margin: 10px 0;
            text-align: center;
            border: 1px solid #e9ecef;
        }
        .exchange-rate .label {
            font-weight: bold;
            color: #8a2be2;
        }
        .exchange-rate .value {
            font-size: 16px;
            font-weight: bold;
        }
        /* ... resto del estilo permanece igual ... */
    </style>
</head>
<body>
    <div class="header">
        <h1>Reporte Detallado de Ventas</h1>
        <div class="subtitle">Rumbero Extreme - Sistema de Gestión</div>
        <div>Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</div>
        <div>Generado el: {{ now()->format('d/m/Y H:i:s') }}</div>
        
        {{-- Mostrar tasa de cambio del BNC --}}
        <div class="exchange-rate">
            <span class="label">Tasa de cambio BNC:</span>
            <span class="value">1 USD = {{ number_format($exchangeRateVes, 2) }} VES</span>
        </div>
    </div>

    {{-- Estadísticas Resumen --}}
    <div class="section-title">Resumen General</div>
    <div class="stats-grid">
        <div class="stat-card">
            <div>Ventas Totales (USD):</div>
            <div class="stat-value">${{ number_format($stats['total_sales'], 2) }}</div>
        </div>
        <div class="stat-card">
            <div>Ventas Totales (VES):</div>
            <div class="stat-value">{{ number_format($stats['total_sales'] * $exchangeRateVes, 2) }} Bs</div>
        </div>
        <div class="stat-card">
            <div>Total de Órdenes:</div>
            <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
        </div>
        <div class="stat-card">
            <div>Valor Promedio por Orden:</div>
            <div class="stat-value">${{ number_format($stats['average_order_value'], 2) }} USD</div>
        </div>
    </div>

    {{-- ... resto del contenido del PDF ... --}}
</body>
</html>