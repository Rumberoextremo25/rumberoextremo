<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas - Rumbero Extreme</title>
    <style>
        :root {
            --primary: #8a2be2;
            --primary-light: #9d4edd;
            --gray-50: #f9fafb;
            --gray-200: #e5e7eb;
            --gray-600: #4b5563;
            --gray-800: #1f2937;
        }

        body { 
            font-family: 'DejaVu Sans', Arial, sans-serif; 
            margin: 0;
            padding: 15px;
            color: var(--gray-800);
            background: white;
            font-size: 10px;
            line-height: 1.2;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
        }

        .header { 
            text-align: center;
            margin-bottom: 15px;
            padding-bottom: 10px;
            border-bottom: 2px solid var(--primary);
        }

        .header h1 { 
            color: var(--primary);
            margin: 0 0 5px 0; 
            font-size: 16px;
            font-weight: bold;
        }

        .header-info {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 9px;
            color: var(--gray-600);
        }

        .info-badge {
            background: var(--gray-50);
            padding: 3px 8px;
            border-radius: 8px;
            border: 1px solid var(--gray-200);
        }

        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr);
            gap: 8px; 
            margin-bottom: 12px;
        }

        .stat-card { 
            background: white;
            padding: 8px;
            border-radius: 6px;
            border: 1px solid var(--gray-200);
            text-align: center;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .stat-value { 
            font-size: 12px; 
            font-weight: bold; 
            color: var(--primary);
            margin-bottom: 2px;
        }

        .stat-label { 
            color: var(--gray-600); 
            font-size: 8px; 
            text-transform: uppercase;
            font-weight: 500;
        }

        .section {
            margin-bottom: 12px;
        }

        .section-header {
            background: var(--primary);
            color: white;
            padding: 5px 8px;
            border-radius: 4px;
            margin-bottom: 6px;
            font-size: 9px;
            font-weight: bold;
        }

        .table { 
            width: 100%; 
            border-collapse: collapse;
            font-size: 8px;
            margin-bottom: 8px;
        }

        .table th { 
            background: var(--primary-light);
            color: white;
            padding: 4px 6px;
            text-align: left;
            font-weight: bold;
            border: 1px solid var(--primary);
        }

        .table td { 
            padding: 3px 6px;
            border: 1px solid var(--gray-200);
        }

        .table tr:nth-child(even) {
            background-color: var(--gray-50);
        }

        .compact-table {
            font-size: 7px;
        }

        .compact-table th,
        .compact-table td {
            padding: 2px 4px;
        }

        .badge {
            display: inline-block;
            padding: 1px 4px;
            border-radius: 3px;
            font-size: 7px;
            font-weight: bold;
            background: var(--primary);
            color: white;
        }

        .footer { 
            text-align: center;
            margin-top: 10px;
            padding-top: 8px;
            border-top: 1px solid var(--gray-200);
            font-size: 8px;
            color: var(--gray-600);
        }

        .metrics-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 8px;
            margin-bottom: 10px;
        }

        .metric-card {
            background: var(--gray-50);
            padding: 6px;
            border-radius: 4px;
            border: 1px solid var(--gray-200);
        }

        .metric-title {
            font-size: 8px;
            font-weight: bold;
            color: var(--primary);
            margin-bottom: 2px;
        }

        .metric-value {
            font-size: 9px;
            font-weight: bold;
        }

        /* Optimizaciones para una sola página */
        .page-break {
            page-break-inside: avoid;
        }

        .no-break {
            page-break-inside: avoid;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header no-break">
            <h1>📊 REPORTE DE VENTAS - RUMBERO EXTREMO</h1>
            <div class="header-info">
                <span class="info-badge">Período: {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</span>
                <span class="info-badge">Vista: {{ ucfirst($reportType) }}</span>
                <span class="info-badge">Generado: {{ now()->format('d/m/Y H:i') }}</span>
            </div>
        </div>

        <!-- Métricas Principales -->
        <div class="section no-break">
            <div class="section-header">MÉTRICAS PRINCIPALES</div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($stats['total_sales'], 2) }}</div>
                    <div class="stat-label">Ventas Totales</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
                    <div class="stat-label">Total Órdenes</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($stats['average_order_value'], 2) }}</div>
                    <div class="stat-label">Ticket Promedio</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($stats['unique_clients']) }}</div>
                    <div class="stat-label">Clientes Únicos</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($stats['growth'], 1) }}%</div>
                    <div class="stat-label">Crecimiento</div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Bs. {{ number_format($exchangeRateVes, 2) }}</div>
                    <div class="stat-label">Tasa de Cambio</div>
                </div>
            </div>
        </div>

        <!-- Métricas Adicionales -->
        <div class="section no-break">
            <div class="section-header">MÉTRICAS ADICIONALES</div>
            <div class="metrics-grid">
                <div class="metric-card">
                    <div class="metric-title">Venta Más Grande</div>
                    <div class="metric-value">
                        @if($metrics['largest_sale'])
                            ${{ number_format($metrics['largest_sale']->total_amount, 2) }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
                <div class="metric-card">
                    <div class="metric-title">Mejor Día</div>
                    <div class="metric-value">
                        @if($metrics['best_day'])
                            ${{ number_format($metrics['best_day']->daily_sales, 2) }}
                        @else
                            N/A
                        @endif
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas Diarias (Compacto) -->
        @if(count($dailySales) > 0 && count($dailySales) <= 15)
        <div class="section no-break">
            <div class="section-header">VENTAS DIARIAS (Últimos {{ count($dailySales) }} días)</div>
            <table class="table compact-table">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Ventas USD</th>
                        <th>Ventas VES</th>
                        <th>Órdenes</th>
                        <th>Promedio</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($dailySales->take(10) as $sale)
                    <tr>
                        <td>{{ $sale['date']->format('d/m') }}</td>
                        <td>${{ number_format($sale['sales_usd'], 2) }}</td>
                        <td>Bs. {{ number_format($sale['sales_ves'], 0) }}</td>
                        <td><span class="badge">{{ $sale['orders'] }}</span></td>
                        <td>${{ number_format($sale['average'], 2) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @if(count($dailySales) > 10)
            <div style="text-align: center; font-size: 7px; color: var(--gray-600);">
                + {{ count($dailySales) - 10 }} días adicionales
            </div>
            @endif
        </div>
        @elseif(count($dailySales) > 15)
        <div class="section no-break">
            <div class="section-header">RESUMEN VENTAS DIARIAS</div>
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 5px; font-size: 8px;">
                <div><strong>Total Días:</strong> {{ count($dailySales) }}</div>
                <div><strong>Promedio Diario:</strong> ${{ number_format($dailySales->avg('sales_usd') ?? 0, 2) }}</div>
                <div><strong>Mejor Día:</strong> ${{ number_format($dailySales->max('sales_usd') ?? 0, 2) }}</div>
                <div><strong>Día Más Bajo:</strong> ${{ number_format($dailySales->min('sales_usd') ?? 0, 2) }}</div>
            </div>
        </div>
        @endif

        <!-- Métodos de Pago -->
        <div class="section no-break">
            <div class="section-header">MÉTODOS DE PAGO</div>
            <table class="table compact-table">
                <thead>
                    <tr>
                        <th>Método</th>
                        <th>Transacciones</th>
                        <th>%</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $totalTransactions = $stats['payment_methods']->sum('count');
                    @endphp
                    @foreach($stats['payment_methods'] as $method)
                    @php
                        $percentage = $totalTransactions > 0 ? ($method->count / $totalTransactions) * 100 : 0;
                    @endphp
                    <tr>
                        <td>{{ $method->payment_method ?? 'No especificado' }}</td>
                        <td>{{ $method->count }}</td>
                        <td>{{ number_format($percentage, 1) }}%</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Footer -->
        <div class="footer no-break">
            <div><strong>RUMBERO EXTREMO</strong> - Reporte de Ventas</div>
            <div>Reporte generado el {{ now()->format('d/m/Y \\a \\l\\a\\s H:i:s') }}</div>
        </div>
    </div>
</body>
</html>