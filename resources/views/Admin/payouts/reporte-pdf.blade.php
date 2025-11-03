<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Pagos - {{ date('d/m/Y') }}</title>
    <style>
        /* Estilos para PDF */
        body {
            font-family: 'DejaVu Sans', sans-serif;
            margin: 0;
            padding: 20px;
            color: #333;
            font-size: 12px;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
            border-bottom: 2px solid #8a2be2;
            padding-bottom: 20px;
        }

        .header h1 {
            color: #8a2be2;
            margin: 0;
            font-size: 24px;
        }

        .header .subtitle {
            color: #6c757d;
            margin: 5px 0 0 0;
            font-size: 14px;
        }

        .metadata {
            display: flex;
            justify-content: space-between;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
        }

        .metadata-item {
            display: flex;
            flex-direction: column;
        }

        .metadata-label {
            font-weight: bold;
            color: #6c757d;
            font-size: 10px;
        }

        .metadata-value {
            color: #333;
            font-size: 11px;
        }

        .summary-cards {
            display: flex;
            justify-content: space-between;
            margin-bottom: 25px;
            gap: 15px;
        }

        .summary-card {
            flex: 1;
            padding: 15px;
            border-radius: 5px;
            text-align: center;
            background: #f8f9fa;
            border-left: 4px solid #8a2be2;
        }

        .summary-card h3 {
            margin: 0 0 10px 0;
            font-size: 12px;
            color: #6c757d;
        }

        .summary-value {
            font-size: 18px;
            font-weight: bold;
            color: #8a2be2;
        }

        .summary-label {
            font-size: 10px;
            color: #6c757d;
            margin-top: 5px;
        }

        .table-container {
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }

        th {
            background: #8a2be2;
            color: white;
            padding: 8px;
            text-align: left;
            font-size: 10px;
            font-weight: bold;
        }

        td {
            padding: 8px;
            border-bottom: 1px solid #dee2e6;
            font-size: 9px;
        }

        tr:nth-child(even) {
            background: #f8f9fa;
        }

        .text-right {
            text-align: right;
        }

        .text-center {
            text-align: center;
        }

        .text-success {
            color: #28a745;
        }

        .text-danger {
            color: #dc3545;
        }

        .badge {
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 8px;
            font-weight: bold;
        }

        .badge-success {
            background: #d4edda;
            color: #155724;
        }

        .badge-warning {
            background: #fff3cd;
            color: #856404;
        }

        .badge-danger {
            background: #f8d7da;
            color: #721c24;
        }

        .badge-info {
            background: #d1ecf1;
            color: #0c5460;
        }

        .status-section {
            margin-bottom: 25px;
        }

        .status-grid {
            display: flex;
            justify-content: space-between;
            gap: 10px;
        }

        .status-item {
            flex: 1;
            padding: 10px;
            background: #f8f9fa;
            border-radius: 5px;
            text-align: center;
        }

        .status-count {
            font-size: 16px;
            font-weight: bold;
            color: #8a2be2;
        }

        .status-label {
            font-size: 10px;
            color: #6c757d;
        }

        .footer {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #dee2e6;
            text-align: center;
            color: #6c757d;
            font-size: 10px;
        }

        .page-break {
            page-break-before: always;
        }

        .signature-section {
            margin-top: 40px;
            display: flex;
            justify-content: space-between;
        }

        .signature-line {
            width: 200px;
            border-top: 1px solid #333;
            text-align: center;
            padding-top: 5px;
            font-size: 10px;
        }

        .notes {
            margin-top: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 5px;
            font-size: 10px;
        }

        .notes h4 {
            margin: 0 0 10px 0;
            color: #8a2be2;
        }
    </style>
</head>
<body>
    {{-- Encabezado --}}
    <div class="header">
        <h1>Reporte de Pagos a Aliados</h1>
        <div class="subtitle">Generado el {{ date('d/m/Y H:i') }}</div>
    </div>

    {{-- Metadatos --}}
    <div class="metadata">
        <div class="metadata-item">
            <span class="metadata-label">Período:</span>
            <span class="metadata-value">{{ \Carbon\Carbon::parse($fecha_inicio)->format('d/m/Y') }} - {{ \Carbon\Carbon::parse($fecha_fin)->format('d/m/Y') }}</span>
        </div>
        <div class="metadata-item">
            <span class="metadata-label">Total Pagos:</span>
            <span class="metadata-value">{{ count($reporte['pagos'] ?? []) }}</span>
        </div>
        <div class="metadata-item">
            <span class="metadata-label">Monto Total:</span>
            <span class="metadata-value">Bs. {{ number_format($reporte['monto_total'] ?? 0, 2, ',', '.') }}</span>
        </div>
        <div class="metadata-item">
            <span class="metadata-label">Aliados Involucrados:</span>
            <span class="metadata-value">{{ $reporte['total_aliados'] ?? 0 }}</span>
        </div>
    </div>

    {{-- Resumen --}}
    <div class="summary-cards">
        <div class="summary-card">
            <h3>PAGOS PROCESADOS</h3>
            <div class="summary-value">{{ $reporte['pagos_procesados'] ?? 0 }}</div>
            <div class="summary-label">Completados exitosamente</div>
        </div>
        <div class="summary-card">
            <h3>PAGOS PENDIENTES</h3>
            <div class="summary-value">{{ $reporte['pagos_pendientes'] ?? 0 }}</div>
            <div class="summary-label">Por procesar</div>
        </div>
        <div class="summary-card">
            <h3>COMISIONES TOTALES</h3>
            <div class="summary-value">Bs. {{ number_format($reporte['comisiones_totales'] ?? 0, 2, ',', '.') }}</div>
            <div class="summary-label">En concepto de comisiones</div>
        </div>
        <div class="summary-card">
            <h3>PAGOS FALLIDOS</h3>
            <div class="summary-value">{{ $reporte['pagos_fallidos'] ?? 0 }}</div>
            <div class="summary-label">Requieren atención</div>
        </div>
    </div>

    {{-- Distribución por Estado --}}
    <div class="status-section">
        <h3 style="color: #8a2be2; margin-bottom: 15px;">Distribución por Estado</h3>
        <div class="status-grid">
            @foreach($reporte['distribucion_estados'] ?? [] as $estado => $datos)
            <div class="status-item">
                <div class="status-count">{{ $datos['count'] }}</div>
                <div class="status-label">{{ strtoupper($estado) }}</div>
                <div style="font-size: 9px; color: #6c757d; margin-top: 5px;">
                    Bs. {{ number_format($datos['monto'], 2, ',', '.') }}
                </div>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Tabla de Pagos --}}
    <div class="table-container">
        <h3 style="color: #8a2be2; margin-bottom: 15px;">Detalle de Pagos</h3>
        <table>
            <thead>
                <tr>
                    <th>ID Pago</th>
                    <th>Aliado</th>
                    <th>ID Venta</th>
                    <th>Monto Venta</th>
                    <th>Comisión</th>
                    <th>Neto a Pagar</th>
                    <th>Estado</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                @forelse($reporte['pagos'] ?? [] as $pago)
                <tr>
                    <td>#{{ $pago['id'] }}</td>
                    <td>{{ $pago['aliado']['nombre'] }}</td>
                    <td>#{{ $pago['venta']['id'] }}</td>
                    <td class="text-right">Bs. {{ number_format($pago['montos']['monto_venta'], 2, ',', '.') }}</td>
                    <td class="text-center">
                        <span class="badge badge-info">{{ number_format($pago['montos']['comision_porcentaje'], 1) }}%</span>
                        <br>
                        <small>Bs. {{ number_format($pago['montos']['comision_monto'], 2, ',', '.') }}</small>
                    </td>
                    <td class="text-right text-success">
                        <strong>Bs. {{ number_format($pago['montos']['neto'], 2, ',', '.') }}</strong>
                    </td>
                    <td class="text-center">
                        @if($pago['estado'] == 'processed')
                            <span class="badge badge-success">PROCESADO</span>
                        @elseif($pago['estado'] == 'pending')
                            <span class="badge badge-warning">PENDIENTE</span>
                        @elseif($pago['estado'] == 'failed')
                            <span class="badge badge-danger">FALLIDO</span>
                        @else
                            <span class="badge badge-info">{{ strtoupper($pago['estado']) }}</span>
                        @endif
                    </td>
                    <td class="text-center">
                        {{ \Carbon\Carbon::parse($pago['fechas']['generacion'])->format('d/m/Y') }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center">No hay pagos en el período seleccionado</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Resumen por Aliado --}}
    <div class="table-container">
        <h3 style="color: #8a2be2; margin-bottom: 15px;">Resumen por Aliado</h3>
        <table>
            <thead>
                <tr>
                    <th>Aliado</th>
                    <th>Total Pagos</th>
                    <th>Monto Total</th>
                    <th>Comisiones</th>
                    <th>Comisión Promedio</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reporte['resumen_aliados'] ?? [] as $aliado)
                <tr>
                    <td>{{ $aliado['nombre'] }}</td>
                    <td class="text-center">{{ $aliado['total_pagos'] }}</td>
                    <td class="text-right">Bs. {{ number_format($aliado['monto_total'], 2, ',', '.') }}</td>
                    <td class="text-right">Bs. {{ number_format($aliado['comisiones_totales'], 2, ',', '.') }}</td>
                    <td class="text-center">{{ number_format($aliado['comision_promedio'], 1) }}%</td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- Notas --}}
    <div class="notes">
        <h4>NOTAS DEL REPORTE</h4>
        <p>• Este reporte incluye todos los pagos generados en el período especificado.</p>
        <p>• Los montos están expresados en Bolivianos (Bs.).</p>
        <p>• Las comisiones se calculan sobre el monto de venta después de aplicar descuentos.</p>
        <p>• Los pagos marcados como "PENDIENTE" requieren confirmación manual.</p>
        <p>• Para consultas o aclaraciones, contactar al departamento de administración.</p>
    </div>

    {{-- Firmas --}}
    <div class="signature-section">
        <div class="signature-line">
            Generado por Sistema<br>
            {{ date('d/m/Y H:i') }}
        </div>
        <div class="signature-line">
            Responsable de Pagos
        </div>
    </div>

    {{-- Pie de página --}}
    <div class="footer">
        Reporte generado automáticamente por el Sistema de Pagos - Página <span class="page-number"></span>
    </div>

    <script>
        // Numeración de páginas
        document.addEventListener('DOMContentLoaded', function() {
            const pages = document.querySelectorAll('.page-number');
            pages.forEach((page, index) => {
                page.textContent = (index + 1);
            });
        });
    </script>
</body>
</html>