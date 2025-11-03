@extends('layouts.admin')

@section('page_title_toolbar', 'Resumen por Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Resumen por</span>
            <span class="text-purple">Aliado</span>
        </h2>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success" role="alert">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error" role="alert">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        {{-- Filtros --}}
        <div class="stats-card mb-4">
            <form action="{{ route('admin.payouts.resumen-aliado') }}" method="GET" class="filters-form">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio:</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" 
                               value="{{ request('fecha_inicio', date('Y-m-01')) }}">
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin:</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" 
                               value="{{ request('fecha_fin', date('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label for="orden">Ordenar por:</label>
                        <select name="orden" id="orden" class="form-control">
                            <option value="monto_total" {{ request('orden') == 'monto_total' ? 'selected' : '' }}>Monto Total</option>
                            <option value="total_pagos" {{ request('orden') == 'total_pagos' ? 'selected' : '' }}>Total Pagos</option>
                            <option value="nombre" {{ request('orden') == 'nombre' ? 'selected' : '' }}>Nombre</option>
                            <option value="comision_promedio" {{ request('orden') == 'comision_promedio' ? 'selected' : '' }}>Comisión Promedio</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>&nbsp;</label>
                        <div class="filter-actions">
                            <button type="submit" class="action-button">
                                <i class="fas fa-filter"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.payouts.resumen-aliado') }}" class="action-button secondary">
                                <i class="fas fa-redo"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Estadísticas Generales --}}
        <div class="stats-grid mb-6">
            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-user-friends"></i>
                </div>
                <div class="stat-value">{{ count($resumen) }}</div>
                <div class="stat-label">Aliados Activos</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="stat-value">Bs. {{ number_format(collect($resumen)->sum('monto_total'), 2, ',', '.') }}</div>
                <div class="stat-label">Monto Total Pagado</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-receipt"></i>
                </div>
                <div class="stat-value">{{ collect($resumen)->sum('total_pagos') }}</div>
                <div class="stat-label">Total Pagos</div>
            </div>

            <div class="stat-item">
                <div class="stat-icon">
                    <i class="fas fa-percentage"></i>
                </div>
                <div class="stat-value">{{ number_format(collect($resumen)->avg('comision_promedio'), 1) }}%</div>
                <div class="stat-label">Comisión Promedio</div>
            </div>
        </div>

        {{-- Gráfico de Distribución --}}
        <div class="dashboard-grid mb-6">
            <div class="dashboard-card large">
                <div class="card-header">
                    <h3>Distribución de Pagos por Aliado</h3>
                    <div class="card-actions">
                        <select id="chart-type" class="form-control-sm">
                            <option value="bar">Barras</option>
                            <option value="pie">Circular</option>
                        </select>
                    </div>
                </div>
                <div class="chart-container">
                    <canvas id="distribucionAliadosChart" height="300"></canvas>
                </div>
            </div>
        </div>

        {{-- Lista de Aliados --}}
        <div class="stats-card">
            <div class="card-header">
                <h3>Resumen por Aliado</h3>
                <div class="card-actions">
                    <span class="text-muted">Mostrando {{ count($resumen) }} aliados</span>
                </div>
            </div>

            @if(empty($resumen))
                <div class="no-data-message">
                    <i class="fas fa-user-friends"></i>
                    <h4>No se encontraron aliados</h4>
                    <p>No hay aliados con pagos en el período seleccionado.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th>Aliado</th>
                                <th>Contacto</th>
                                <th>Total Pagos</th>
                                <th>Monto Total</th>
                                <th>Comisiones</th>
                                <th>Comisión Promedio</th>
                                <th>Último Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($resumen as $aliado)
                                <tr>
                                    <td>
                                        <div class="aliado-info-compact">
                                            <div class="aliado-avatar-small">
                                                {{ substr($aliado['nombre'], 0, 1) }}
                                            </div>
                                            <div class="aliado-details-compact">
                                                <div class="aliado-name">{{ $aliado['nombre'] }}</div>
                                                <div class="aliado-id">ID: {{ $aliado['id'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="contact-info">
                                            <div class="contact-email">{{ $aliado['email'] }}</div>
                                            <div class="contact-phone">{{ $aliado['telefono'] ?? 'N/A' }}</div>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ $aliado['total_pagos'] }}</span>
                                    </td>
                                    <td class="text-success">
                                        <strong>Bs. {{ number_format($aliado['monto_total'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td class="text-danger">
                                        <strong>Bs. {{ number_format($aliado['comisiones_totales'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="comision-badge">{{ number_format($aliado['comision_promedio'], 1) }}%</span>
                                    </td>
                                    <td>
                                        @if($aliado['ultimo_pago'])
                                            <div class="date-info">
                                                <div class="date">{{ \Carbon\Carbon::parse($aliado['ultimo_pago'])->format('d/m/Y') }}</div>
                                                <div class="time">{{ \Carbon\Carbon::parse($aliado['ultimo_pago'])->format('H:i') }}</div>
                                            </div>
                                        @else
                                            <span class="text-muted">N/A</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <a href="{{ route('admin.payouts.por-aliado', $aliado['id']) }}" 
                                               class="btn-action btn-info" 
                                               title="Ver pagos del aliado">
                                                <i class="fas fa-eye"></i>
                                            </a>

                                            <button class="btn-action btn-warning" 
                                                    title="Contactar aliado"
                                                    onclick="contactarAliado({{ json_encode($aliado) }})">
                                                <i class="fas fa-envelope"></i>
                                            </button>

                                            <button class="btn-action btn-success" 
                                                    title="Generar reporte"
                                                    onclick="generarReporteAliado({{ $aliado['id'] }})">
                                                <i class="fas fa-file-export"></i>
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Resumen por Bancos --}}
        <div class="dashboard-grid mt-6">
            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Distribución por Banco</h3>
                </div>
                <div class="chart-container">
                    <canvas id="bancosChart" height="250"></canvas>
                </div>
            </div>

            <div class="dashboard-card">
                <div class="card-header">
                    <h3>Top 5 Aliados</h3>
                </div>
                <div class="top-aliados-list">
                    @foreach(array_slice($resumen, 0, 5) as $index => $aliado)
                        <div class="top-aliado-item">
                            <div class="top-position">{{ $index + 1 }}</div>
                            <div class="top-aliado-info">
                                <div class="top-aliado-name">{{ $aliado['nombre'] }}</div>
                                <div class="top-aliado-amount">Bs. {{ number_format($aliado['monto_total'], 2, ',', '.') }}</div>
                            </div>
                            <div class="top-aliado-stats">
                                <span class="top-pagos">{{ $aliado['total_pagos'] }} pagos</span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    {{-- Modal de Contacto --}}
    <div id="contactoModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Contactar Aliado</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <div class="aliado-contact-info">
                    <div class="contact-item">
                        <label>Nombre:</label>
                        <span id="contact-nombre"></span>
                    </div>
                    <div class="contact-item">
                        <label>Email:</label>
                        <span id="contact-email"></span>
                    </div>
                    <div class="contact-item">
                        <label>Teléfono:</label>
                        <span id="contact-telefono"></span>
                    </div>
                </div>
                <div class="form-group mt-4">
                    <label for="mensaje">Mensaje:</label>
                    <textarea id="mensaje" class="form-control" rows="4" placeholder="Escribe tu mensaje aquí..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <button type="button" class="btn btn-primary" onclick="enviarMensaje()">
                    <i class="fas fa-paper-plane"></i> Enviar Mensaje
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Datos para gráficos
        const resumenData = @json($resumen);

        // Gráfico de distribución por aliado
        const distribucionCtx = document.getElementById('distribucionAliadosChart').getContext('2d');
        const distribucionChart = new Chart(distribucionCtx, {
            type: 'bar',
            data: {
                labels: resumenData.map(a => a.nombre.substring(0, 15) + (a.nombre.length > 15 ? '...' : '')),
                datasets: [{
                    label: 'Monto Total (Bs.)',
                    data: resumenData.map(a => a.monto_total),
                    backgroundColor: 'rgba(138, 43, 226, 0.8)',
                    borderColor: 'rgba(138, 43, 226, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false
                    },
                    title: {
                        display: true,
                        text: 'Monto Total por Aliado'
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Bs. ' + value.toLocaleString();
                            }
                        }
                    }
                }
            }
        });

        // Gráfico de distribución por bancos
        const bancosData = {};
        resumenData.forEach(aliado => {
            const banco = aliado.banco || 'No especificado';
            bancosData[banco] = (bancosData[banco] || 0) + aliado.monto_total;
        });

        const bancosCtx = document.getElementById('bancosChart').getContext('2d');
        new Chart(bancosCtx, {
            type: 'doughnut',
            data: {
                labels: Object.keys(bancosData),
                datasets: [{
                    data: Object.values(bancosData),
                    backgroundColor: [
                        'rgba(138, 43, 226, 0.8)',
                        'rgba(255, 193, 7, 0.8)',
                        'rgba(40, 167, 69, 0.8)',
                        'rgba(220, 53, 69, 0.8)',
                        'rgba(23, 162, 184, 0.8)',
                        'rgba(108, 117, 125, 0.8)'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        position: 'bottom',
                    }
                },
                cutout: '60%'
            }
        });

        // Cambiar tipo de gráfico
        document.getElementById('chart-type').addEventListener('change', function() {
            distribucionChart.config.type = this.value;
            distribucionChart.update();
        });

        // Cerrar modales
        document.querySelectorAll('.close-modal-btn, .cancel-modal-btn').forEach(button => {
            button.addEventListener('click', function() {
                document.getElementById('contactoModal').style.display = 'none';
            });
        });

        // Cerrar alertas
        document.querySelectorAll('.close-alert').forEach(button => {
            button.addEventListener('click', function() {
                this.parentElement.style.display = 'none';
            });
        });
    });

    function contactarAliado(aliado) {
        const modal = document.getElementById('contactoModal');
        
        document.getElementById('contact-nombre').textContent = aliado.nombre;
        document.getElementById('contact-email').textContent = aliado.email;
        document.getElementById('contact-telefono').textContent = aliado.telefono || 'No disponible';
        
        modal.style.display = 'flex';
    }

    function enviarMensaje() {
        const mensaje = document.getElementById('mensaje').value;
        const email = document.getElementById('contact-email').textContent;
        
        if (!mensaje.trim()) {
            Swal.fire('Error', 'Por favor escribe un mensaje.', 'error');
            return;
        }

        // Aquí iría la lógica para enviar el mensaje
        Swal.fire('Enviado', 'El mensaje ha sido enviado correctamente.', 'success');
        document.getElementById('contactoModal').style.display = 'none';
        document.getElementById('mensaje').value = '';
    }

    function generarReporteAliado(aliadoId) {
        Swal.fire({
            title: 'Generar Reporte',
            text: '¿Quieres generar un reporte detallado para este aliado?',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#8a2be2',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Generar Reporte',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                // Aquí iría la lógica para generar el reporte
                window.open(`/admin/payouts/aliado/${aliadoId}/reporte`, '_blank');
            }
        });
    }
</script>

<style>
    .aliado-info-compact {
        display: flex;
        align-items: center;
        gap: 12px;
    }

    .aliado-avatar-small {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: linear-gradient(135deg, var(--primary-color), #6f42c1);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .aliado-details-compact {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .aliado-name {
        font-weight: 600;
        color: var(--text-dark);
    }

    .aliado-id {
        font-size: 11px;
        color: var(--text-muted);
    }

    .contact-info {
        display: flex;
        flex-direction: column;
        gap: 2px;
    }

    .contact-email {
        font-size: 14px;
        color: var(--text-dark);
    }

    .contact-phone {
        font-size: 12px;
        color: var(--text-muted);
    }

    .comision-badge {
        background: rgba(23, 162, 184, 0.1);
        color: var(--info-color);
        padding: 4px 8px;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
    }

    .action-buttons {
        display: flex;
        gap: 4px;
        justify-content: center;
    }

    .btn-action {
        padding: 6px 8px;
        border: none;
        border-radius: 4px;
        cursor: pointer;
        font-size: 14px;
        transition: all 0.3s;
        display: flex;
        align-items: center;
        justify-content: center;
        width: 32px;
        height: 32px;
        text-decoration: none;
    }

    .btn-info {
        background: var(--info-color);
        color: white;
    }

    .btn-info:hover {
        background: #138496;
    }

    .btn-warning {
        background: var(--warning-color);
        color: #000;
    }

    .btn-warning:hover {
        background: #e0a800;
    }

    .btn-success {
        background: var(--success-color);
        color: white;
    }

    .btn-success:hover {
        background: #218838;
    }

    .top-aliados-list {
        display: flex;
        flex-direction: column;
        gap: 12px;
        padding: 16px;
    }

    .top-aliado-item {
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 12px;
        background: var(--bg-light);
        border-radius: 8px;
        transition: background 0.3s;
    }

    .top-aliado-item:hover {
        background: #e9ecef;
    }

    .top-position {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        background: var(--primary-color);
        color: white;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: 600;
        font-size: 14px;
    }

    .top-aliado-info {
        flex: 1;
    }

    .top-aliado-name {
        font-weight: 600;
        margin-bottom: 2px;
    }

    .top-aliado-amount {
        font-size: 14px;
        color: var(--success-color);
        font-weight: 600;
    }

    .top-aliado-stats {
        display: flex;
        align-items: center;
    }

    .top-pagos {
        font-size: 12px;
        color: var(--text-muted);
        background: white;
        padding: 4px 8px;
        border-radius: 12px;
    }

    .aliado-contact-info {
        display: flex;
        flex-direction: column;
        gap: 12px;
        margin-bottom: 16px;
    }

    .contact-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 8px 0;
        border-bottom: 1px solid var(--border-color);
    }

    .contact-item:last-child {
        border-bottom: none;
    }

    .contact-item label {
        font-weight: 600;
        color: var(--text-dark);
    }

    .contact-item span {
        color: var(--text-muted);
    }

    @media (max-width: 768px) {
        .aliado-info-compact {
            flex-direction: column;
            text-align: center;
            gap: 8px;
        }
        
        .action-buttons {
            flex-direction: column;
        }
        
        .btn-action {
            width: 100%;
        }
        
        .top-aliado-item {
            flex-direction: column;
            text-align: center;
        }
    }
</style>
@endpush