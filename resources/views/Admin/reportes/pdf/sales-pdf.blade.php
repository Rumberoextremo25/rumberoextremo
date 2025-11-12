<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Reporte de Ventas - Rumbero Extreme</title>
    <style>
        :root {
            --primary: #8a2be2;
            --primary-light: #9d4edd;
            --primary-dark: #7b1fa2;
            --accent: #00b894;
            --accent-light: #55efc4;
            --warning: #fdcb6e;
            --danger: #e17055;
            --gray-50: #f9fafb;
            --gray-100: #f3f4f6;
            --gray-200: #e5e7eb;
            --gray-300: #d1d5db;
            --gray-600: #4b5563;
            --gray-700: #374151;
            --gray-800: #1f2937;
            --gray-900: #111827;
        }

        body { 
            font-family: 'Segoe UI', 'Inter', Arial, sans-serif; 
            margin: 0;
            padding: 20px;
            color: var(--gray-800);
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            font-size: 11px;
            line-height: 1.4;
        }

        .container {
            max-width: 100%;
            margin: 0 auto;
            background: white;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
            overflow: hidden;
            position: relative;
        }

        /* Header Moderno */
        .header { 
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            padding: 25px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }

        .header::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 1px, transparent 1px);
            background-size: 20px 20px;
            animation: float 20s linear infinite;
        }

        @keyframes float {
            0% { transform: translate(0, 0) rotate(0deg); }
            100% { transform: translate(-20px, -20px) rotate(360deg); }
        }

        .header h1 { 
            margin: 0 0 15px 0; 
            font-size: 24px;
            font-weight: 800;
            letter-spacing: -0.5px;
            position: relative;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
        }

        .header-subtitle {
            font-size: 14px;
            opacity: 0.9;
            margin-bottom: 20px;
            font-weight: 400;
        }

        .header-info {
            display: flex;
            justify-content: center;
            gap: 15px;
            flex-wrap: wrap;
            font-size: 11px;
        }

        .info-badge {
            background: rgba(255,255,255,0.15);
            padding: 8px 16px;
            border-radius: 12px;
            border: 1px solid rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .info-badge:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }

        /* Stats Grid Mejorado */
        .stats-grid { 
            display: grid; 
            grid-template-columns: repeat(3, 1fr);
            gap: 12px; 
            margin: 25px 30px;
            position: relative;
        }

        .stat-card { 
            background: white;
            padding: 20px;
            border-radius: 12px;
            border: none;
            text-align: center;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
            border-left: 4px solid var(--primary);
        }

        .stat-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        }

        .stat-card:nth-child(2) { border-left-color: var(--accent); }
        .stat-card:nth-child(3) { border-left-color: var(--warning); }
        .stat-card:nth-child(4) { border-left-color: #0984e3; }
        .stat-card:nth-child(5) { border-left-color: var(--danger); }
        .stat-card:nth-child(6) { border-left-color: #6c5ce7; }

        .stat-value { 
            font-size: 20px; 
            font-weight: 800; 
            color: var(--primary);
            margin-bottom: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
        }

        .stat-card:nth-child(2) .stat-value { color: var(--accent); }
        .stat-card:nth-child(3) .stat-value { color: var(--warning); }
        .stat-card:nth-child(4) .stat-value { color: #0984e3; }
        .stat-card:nth-child(5) .stat-value { color: var(--danger); }
        .stat-card:nth-child(6) .stat-value { color: #6c5ce7; }

        .stat-label { 
            color: var(--gray-600); 
            font-size: 11px; 
            text-transform: uppercase;
            font-weight: 600;
            letter-spacing: 0.5px;
        }

        .stat-trend {
            font-size: 10px;
            font-weight: 600;
            margin-top: 5px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 3px;
        }

        .trend-up { color: var(--accent); }
        .trend-down { color: var(--danger); }

        /* Secciones Mejoradas */
        .section {
            margin: 25px 30px;
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.08);
            overflow: hidden;
        }

        .section-header {
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            padding: 15px 20px;
            font-size: 14px;
            font-weight: 700;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-content {
            padding: 20px;
        }

        /* Métricas Adicionales - Diseño Moderno */
        .metrics-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 15px;
        }

        .metric-card {
            background: linear-gradient(135deg, var(--gray-50), white);
            padding: 15px;
            border-radius: 10px;
            border: 1px solid var(--gray-200);
            position: relative;
            transition: all 0.3s ease;
        }

        .metric-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(0,0,0,0.1);
            border-color: var(--primary-light);
        }

        .metric-title {
            font-size: 11px;
            font-weight: 600;
            color: var(--gray-600);
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .metric-value {
            font-size: 18px;
            font-weight: 800;
            color: var(--primary);
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .metric-card:nth-child(2) .metric-value {
            color: var(--accent);
        }

        /* Tablas Modernas */
        .table { 
            width: 100%; 
            border-collapse: separate;
            border-spacing: 0;
            font-size: 10px;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }

        .table th { 
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            color: white;
            padding: 12px 10px;
            text-align: left;
            font-weight: 700;
            font-size: 10px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .table td { 
            padding: 10px;
            border-bottom: 1px solid var(--gray-100);
            transition: background-color 0.2s ease;
        }

        .table tr:hover td {
            background-color: var(--gray-50);
        }

        .table tr:last-child td {
            border-bottom: none;
        }

        .compact-table {
            font-size: 9px;
        }

        .compact-table th,
        .compact-table td {
            padding: 8px 6px;
        }

        /* Badges Mejorados */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 8px;
            border-radius: 6px;
            font-size: 8px;
            font-weight: 700;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            text-transform: uppercase;
            letter-spacing: 0.3px;
        }

        .badge-secondary {
            background: linear-gradient(135deg, var(--gray-600), var(--gray-700));
        }

        .badge-success {
            background: linear-gradient(135deg, var(--accent), #00a085);
        }

        /* Progress Bars para Métodos de Pago */
        .progress-container {
            margin-top: 15px;
        }

        .progress-item {
            margin-bottom: 10px;
        }

        .progress-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 5px;
            font-size: 10px;
        }

        .progress-bar {
            height: 6px;
            background: var(--gray-200);
            border-radius: 3px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            border-radius: 3px;
            transition: width 0.8s ease;
        }

        /* Footer Moderno */
        .footer { 
            background: linear-gradient(135deg, var(--gray-800), var(--gray-900));
            color: white;
            padding: 20px 30px;
            text-align: center;
            font-size: 10px;
        }

        .footer-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 10px;
        }

        .footer-brand {
            font-size: 14px;
            font-weight: 700;
            color: var(--primary-light);
        }

        .footer-info {
            color: var(--gray-300);
        }

        /* Efectos de Gradiente y Sombra */
        .gradient-text {
            background: linear-gradient(135deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .metrics-grid {
                grid-template-columns: 1fr;
            }
            
            .header-info {
                flex-direction: column;
                align-items: center;
            }
            
            .footer-content {
                flex-direction: column;
                text-align: center;
            }
        }

        /* Animaciones Suaves */
        .fade-in {
            animation: fadeIn 0.6s ease-in;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .slide-in {
            animation: slideIn 0.8s ease-out;
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Iconos Decorativos */
        .icon {
            width: 16px;
            height: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
            background: rgba(255,255,255,0.2);
            font-size: 8px;
        }
    </style>
</head>
<body>
    <div class="container fade-in">
        <!-- Header Moderno -->
        <div class="header">
            <h1>🚀 REPORTE DE VENTAS - RUMBERO EXTREMO</h1>
            <div class="header-subtitle">Dashboard de Performance y Métricas Clave</div>
            <div class="header-info">
                <span class="info-badge">📅 {{ $startDate->format('d/m/Y') }} - {{ $endDate->format('d/m/Y') }}</span>
                <span class="info-badge">👁️ Vista {{ ucfirst($reportType) }}</span>
                <span class="info-badge">🕐 {{ now()->format('d/m/Y H:i') }}</span>
                <span class="info-badge">💱 Tasa: Bs. {{ number_format($exchangeRateVes, 2) }}</span>
            </div>
        </div>

        <!-- Métricas Principales -->
        <div class="section slide-in">
            <div class="section-header">
                <span>📊 MÉTRICAS PRINCIPALES</span>
            </div>
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($stats['total_sales'], 2) }}</div>
                    <div class="stat-label">Ventas Totales</div>
                    <div class="stat-trend trend-up">
                        <span>▲</span>
                        <span>Bs. {{ number_format($stats['total_sales_ves'] ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($stats['total_orders']) }}</div>
                    <div class="stat-label">Total Órdenes</div>
                    <div class="stat-trend">
                        <span>📦</span>
                        <span>${{ number_format($stats['average_order_value'], 2) }} avg</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">${{ number_format($stats['average_order_value'], 2) }}</div>
                    <div class="stat-label">Ticket Promedio</div>
                    <div class="stat-trend">
                        <span>💰</span>
                        <span>Bs. {{ number_format($stats['average_order_value_ves'] ?? 0, 2) }}</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">{{ number_format($stats['unique_clients']) }}</div>
                    <div class="stat-label">Clientes Únicos</div>
                    <div class="stat-trend trend-up">
                        <span>👥</span>
                        <span>+12% crecimiento</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value {{ $stats['growth'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        {{ number_format($stats['growth'], 1) }}%
                    </div>
                    <div class="stat-label">Crecimiento</div>
                    <div class="stat-trend {{ $stats['growth'] >= 0 ? 'trend-up' : 'trend-down' }}">
                        <span>{{ $stats['growth'] >= 0 ? '▲' : '▼' }}</span>
                        <span>vs período anterior</span>
                    </div>
                </div>
                <div class="stat-card">
                    <div class="stat-value">Bs. {{ number_format($exchangeRateVes, 2) }}</div>
                    <div class="stat-label">Tasa de Cambio</div>
                    <div class="stat-trend">
                        <span>💹</span>
                        <span>Banco Central</span>
                    </div>
                </div>
            </div>
        </div>

        <!-- Métricas Adicionales -->
        <div class="section fade-in">
            <div class="section-header">
                <span>⭐ MÉTRICAS DESTACADAS</span>
            </div>
            <div class="section-content">
                <div class="metrics-grid">
                    <div class="metric-card">
                        <div class="metric-title">🏆 Venta Más Grande</div>
                        <div class="metric-value">
                            @if($metrics['largest_sale'])
                                ${{ number_format($metrics['largest_sale']->total_amount, 2) }}
                                <small style="font-size: 10px; color: var(--gray-600);">
                                    (Bs. {{ number_format($metrics['largest_sale_ves'] ?? 0, 2) }})
                                </small>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                    <div class="metric-card">
                        <div class="metric-title">📈 Mejor Día</div>
                        <div class="metric-value">
                            @if($metrics['best_day'])
                                ${{ number_format($metrics['best_day']->daily_sales, 2) }}
                                <small style="font-size: 10px; color: var(--gray-600);">
                                    ({{ \Carbon\Carbon::parse($metrics['best_day']->date)->format('d/m') }})
                                </small>
                            @else
                                N/A
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Ventas Diarias -->
        @if(count($dailySales) > 0 && count($dailySales) <= 15)
        <div class="section fade-in">
            <div class="section-header">
                <span>📅 VENTAS DIARIAS (Últimos {{ count($dailySales) }} días)</span>
            </div>
            <div class="section-content">
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
                            <td><strong>{{ $sale['date']->format('d/m') }}</strong></td>
                            <td>${{ number_format($sale['sales_usd'], 2) }}</td>
                            <td>Bs. {{ number_format($sale['sales_ves'], 0) }}</td>
                            <td><span class="badge">{{ $sale['orders'] }}</span></td>
                            <td>${{ number_format($sale['average'], 2) }}</td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                @if(count($dailySales) > 10)
                <div style="text-align: center; margin-top: 10px;">
                    <span class="badge badge-secondary">+ {{ count($dailySales) - 10 }} días adicionales</span>
                </div>
                @endif
            </div>
        </div>
        @elseif(count($dailySales) > 15)
        <div class="section fade-in">
            <div class="section-header">
                <span>📊 RESUMEN VENTAS DIARIAS</span>
            </div>
            <div class="section-content">
                <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 15px; font-size: 11px;">
                    <div style="background: var(--gray-50); padding: 12px; border-radius: 8px;">
                        <strong>Total Días Analizados:</strong> {{ count($dailySales) }}
                    </div>
                    <div style="background: var(--gray-50); padding: 12px; border-radius: 8px;">
                        <strong>Promedio Diario:</strong> ${{ number_format($dailySales->avg('sales_usd') ?? 0, 2) }}
                    </div>
                    <div style="background: var(--gray-50); padding: 12px; border-radius: 8px;">
                        <strong>Mejor Día:</strong> ${{ number_format($dailySales->max('sales_usd') ?? 0, 2) }}
                    </div>
                    <div style="background: var(--gray-50); padding: 12px; border-radius: 8px;">
                        <strong>Día Más Bajo:</strong> ${{ number_format($dailySales->min('sales_usd') ?? 0, 2) }}
                    </div>
                </div>
            </div>
        </div>
        @endif

        <!-- Métodos de Pago -->
        <div class="section fade-in">
            <div class="section-header">
                <span>💳 MÉTODOS DE PAGO</span>
            </div>
            <div class="section-content">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Método de Pago</th>
                            <th>Transacciones</th>
                            <th>Porcentaje</th>
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
                            <td>
                                <strong>{{ ucfirst($method->payment_method ?? 'No especificado') }}</strong>
                            </td>
                            <td>
                                <span class="badge badge-success">{{ $method->count }}</span>
                            </td>
                            <td>
                                <div style="display: flex; align-items: center; gap: 10px;">
                                    <span style="font-weight: 600;">{{ number_format($percentage, 1) }}%</span>
                                    <div style="flex: 1; background: var(--gray-200); height: 6px; border-radius: 3px;">
                                        <div style="height: 100%; background: linear-gradient(90deg, var(--primary), var(--primary-light)); border-radius: 3px; width: {{ $percentage }}%;"></div>
                                    </div>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Footer Moderno -->
        <div class="footer">
            <div class="footer-content">
                <div class="footer-brand">RUMBERO EXTREMO</div>
                <div class="footer-info">
                    Reporte de Ventas Generado el {{ now()->format('d/m/Y \\a \\l\\a\\s H:i:s') }}
                </div>
                <div class="footer-info">
                    📊 Dashboard de Performance Comercial
                </div>
            </div>
        </div>
    </div>
</body>
</html>