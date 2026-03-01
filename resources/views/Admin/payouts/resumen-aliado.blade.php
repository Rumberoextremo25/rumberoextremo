@extends('layouts.admin')

@section('page_title_toolbar', 'Resumen de Pagos por Aliado')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/resumen-aliado.css') }}">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
@endpush

@section('content')
    <div class="main-content resumen-container">
        {{-- Header --}}
        <div class="page-header">
            <h1>
                <span class="text-gray-900">Resumen de Pagos</span>
                <span class="text-purple">por Aliado</span>
            </h1>
            <div class="header-actions">
                <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'pdf', 'tipo' => 'resumen-aliado']) }}"
                    class="btn-export">
                    <i class="fas fa-file-pdf"></i> Exportar PDF
                </a>
                <a href="{{ route('admin.payouts.exportar-reporte', ['formato' => 'excel', 'tipo' => 'resumen-aliado']) }}"
                    class="btn-export">
                    <i class="fas fa-file-excel"></i> Exportar Excel
                </a>
            </div>
        </div>

        {{-- Alertas --}}
        @if (session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                <span>{{ session('success') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-error">
                <i class="fas fa-exclamation-circle"></i>
                <span>{{ session('error') }}</span>
                <button type="button" class="close-alert">&times;</button>
            </div>
        @endif

        {{-- Resumen Global --}}
        @php
            $totalAliados = count($resumen);
            $totalPagos = collect($resumen)->sum('total_payouts');
            $totalMonto = collect($resumen)->sum('total_monto');
            $promedioPorAliado = $totalAliados > 0 ? $totalMonto / $totalAliados : 0;
        @endphp

        <div class="resumen-global">
            <div class="resumen-card">
                <div class="resumen-icon">
                    <i class="fas fa-handshake"></i>
                </div>
                <div class="resumen-content">
                    <div class="resumen-label">Total Aliados</div>
                    <div class="resumen-value">{{ number_format($totalAliados) }}</div>
                    <div class="resumen-sub">con pagos registrados</div>
                </div>
            </div>
            <div class="resumen-card">
                <div class="resumen-icon">
                    <i class="fas fa-money-bill-wave"></i>
                </div>
                <div class="resumen-content">
                    <div class="resumen-label">Total Pagos</div>
                    <div class="resumen-value">{{ number_format($totalPagos) }}</div>
                    <div class="resumen-sub">en total</div>
                </div>
            </div>
            <div class="resumen-card">
                <div class="resumen-icon">
                    <i class="fas fa-coins"></i>
                </div>
                <div class="resumen-content">
                    <div class="resumen-label">Monto Total</div>
                    <div class="resumen-value">Bs. {{ number_format($totalMonto, 2, ',', '.') }}</div>
                    <div class="resumen-sub">pagado a aliados</div>
                </div>
            </div>
            <div class="resumen-card">
                <div class="resumen-icon">
                    <i class="fas fa-chart-line"></i>
                </div>
                <div class="resumen-content">
                    <div class="resumen-label">Promedio x Aliado</div>
                    <div class="resumen-value">Bs. {{ number_format($promedioPorAliado, 2, ',', '.') }}</div>
                    <div class="resumen-sub">monto promedio</div>
                </div>
            </div>
        </div>

        {{-- Filtros --}}
        <div class="filtros-section">
            <div class="filtros-titulo">
                <i class="fas fa-filter"></i>
                <span>Filtrar Aliados</span>
            </div>
            <form action="{{ route('admin.payouts.resumen-aliado') }}" method="GET" class="filtros-grid">
                <div class="filtro-group">
                    <label for="busqueda">Buscar Aliado</label>
                    <input type="text" name="busqueda" id="busqueda" class="form-control"
                        placeholder="Nombre o email..." value="{{ request('busqueda') }}">
                </div>
                <div class="filtro-group">
                    <label for="fecha_inicio">Fecha Inicio</label>
                    <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                        value="{{ request('fecha_inicio', now()->subYear()->format('Y-m-d')) }}">
                </div>
                <div class="filtro-group">
                    <label for="fecha_fin">Fecha Fin</label>
                    <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                        value="{{ request('fecha_fin', now()->format('Y-m-d')) }}">
                </div>
                <div class="filtro-actions">
                    <button type="submit" class="btn-filtro primary">
                        <i class="fas fa-search"></i> Filtrar
                    </button>
                    <a href="{{ route('admin.payouts.resumen-aliado') }}" class="btn-filtro secondary">
                        <i class="fas fa-times"></i> Limpiar
                    </a>
                </div>
            </form>
        </div>

        {{-- Tabla de Aliados --}}
        <div class="aliados-table-container">
            <div class="table-header">
                <h3>
                    <i class="fas fa-list"></i>
                    Listado de Aliados
                </h3>
                <span class="badge">{{ $totalAliados }} registros</span>
            </div>

            @if (empty($resumen))
                <div class="no-payouts-message" style="text-align: center; padding: 4rem;">
                    <i class="fas fa-handshake" style="font-size: 4rem; color: #d1d5db; margin-bottom: 1rem;"></i>
                    <h3 style="color: #6b7280;">No hay datos de aliados</h3>
                    <p style="color: #9ca3af;">No se encontraron pagos para los filtros seleccionados.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="aliados-table">
                        <thead>
                            <tr>
                                <th>Aliado</th>
                                <th>Email</th>
                                <th>Total Pagos</th>
                                <th>Monto Total</th>
                                <th>Distribución por Estado</th>
                                <th>Último Pago</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($resumen as $index => $aliado)
                                <tr onclick="seleccionarAliado({{ $aliado['aliado_id'] }})"
                                    id="row-{{ $aliado['aliado_id'] }}">
                                    <td>
                                        <div class="aliado-info">
                                            <div class="aliado-avatar">
                                                {{ substr($aliado['aliado_nombre'], 0, 1) }}
                                            </div>
                                            <div>
                                                <div class="aliado-nombre">{{ $aliado['aliado_nombre'] }}</div>
                                                <div class="aliado-email">ID: {{ $aliado['aliado_id'] }}</div>
                                            </div>
                                        </div>
                                    </td>
                                    <td>{{ $aliado['email'] ?? 'N/A' }}</td>
                                    <td class="text-success">{{ number_format($aliado['total_payouts']) }}</td>
                                    <td class="text-success">Bs. {{ number_format($aliado['total_monto'], 2, ',', '.') }}
                                    </td>
                                    <td>
                                        <div class="estados-badge">
                                            @foreach ($aliado['estados'] ?? [] as $estado => $count)
                                                @php
                                                    $badgeClass = match ($estado) {
                                                        'pending' => 'pending',
                                                        'processing' => 'processing',
                                                        'completed' => 'completed',
                                                        'reverted' => 'reverted',
                                                        default => 'pending',
                                                    };
                                                    $estadoTexto = match ($estado) {
                                                        'pending' => 'Pend',
                                                        'processing' => 'Proc',
                                                        'completed' => 'Comp',
                                                        'reverted' => 'Rev',
                                                        default => $estado,
                                                    };
                                                @endphp
                                                <span class="badge-mini {{ $badgeClass }}"
                                                    title="{{ $estado }}">
                                                    {{ $estadoTexto }}: {{ $count }}
                                                </span>
                                            @endforeach
                                        </div>
                                    </td>
                                    <td>
                                        @if (isset($aliado['ultimo_pago']))
                                            {{ \Carbon\Carbon::parse($aliado['ultimo_pago'])->format('d/m/Y') }}
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                    <td>
                                        <a href="/admin/payouts/por-aliado/{{ $aliado['aliado_id'] }}"
                                            class="btn-sm" onclick="event.stopPropagation()">
                                            <i class="fas fa-eye"></i> Ver Pagos
                                        </a>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>

        {{-- Detalle del Aliado Seleccionado --}}
        <div id="aliado-detalle-container" style="display: none;">
            <div class="aliado-detalle">
                <div class="detalle-header">
                    <h3>
                        <i class="fas fa-handshake"></i>
                        <span id="detalle-nombre"></span>
                    </h3>
                    <div class="detalle-actions">
                        <a href="#" id="detalle-ver-pagos" class="btn-sm">
                            <i class="fas fa-eye"></i> Ver Todos los Pagos
                        </a>
                    </div>
                </div>

                <div class="detalle-grid">
                    <div class="detalle-card">
                        <h4><i class="fas fa-chart-pie"></i> Estadísticas Generales</h4>
                        <div class="detalle-row">
                            <span class="detalle-label">Total Pagos:</span>
                            <span class="detalle-value success" id="detalle-total-pagos">0</span>
                        </div>
                        <div class="detalle-row">
                            <span class="detalle-label">Monto Total:</span>
                            <span class="detalle-value success" id="detalle-monto-total">Bs. 0,00</span>
                        </div>
                        <div class="detalle-row">
                            <span class="detalle-label">Monto Promedio:</span>
                            <span class="detalle-value" id="detalle-monto-promedio">Bs. 0,00</span>
                        </div>
                    </div>

                    <div class="detalle-card">
                        <h4><i class="fas fa-clock"></i> Distribución por Estado</h4>
                        <div id="detalle-estados"></div>
                    </div>

                    <div class="detalle-card">
                        <h4><i class="fas fa-calendar"></i> Últimos Pagos</h4>
                        <div id="detalle-ultimos-pagos">
                            <p class="text-muted">Cargando...</p>
                        </div>
                    </div>
                </div>

                <div class="aliado-graficas">
                    <div class="grafica-card">
                        <div class="grafica-header">
                            <h5>Evolución Mensual</h5>
                            <span class="badge">Últimos 6 meses</span>
                        </div>
                        <div class="grafica-container">
                            <canvas id="evolucionChart"></canvas>
                        </div>
                    </div>
                    <div class="grafica-card">
                        <div class="grafica-header">
                            <h5>Distribución por Estado</h5>
                            <span class="badge">porcentajes</span>
                        </div>
                        <div class="grafica-container">
                            <canvas id="estadosChart"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        let evolucionChart = null;
        let estadosChart = null;
        let selectedAliadoId = null;

        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas
            document.querySelectorAll('.close-alert').forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });

            // Verificar si hay un aliado seleccionado en la URL
            const urlParams = new URLSearchParams(window.location.search);
            const aliadoId = urlParams.get('aliado_id');
            if (aliadoId) {
                seleccionarAliado(aliadoId);
            }
        });

        function seleccionarAliado(aliadoId) {
            // Quitar selección anterior
            if (selectedAliadoId) {
                document.getElementById(`row-${selectedAliadoId}`)?.classList.remove('selected');
            }

            // Marcar nueva selección
            const row = document.getElementById(`row-${aliadoId}`);
            if (row) {
                row.classList.add('selected');
                selectedAliadoId = aliadoId;
            }

            // Cargar datos del aliado
            cargarDetalleAliado(aliadoId);
        }

        function cargarDetalleAliado(aliadoId) {
            fetch(`/admin/payouts/resumen-aliado/${aliadoId}/detalle`)
                .then(response => response.json())
                .then(data => {
                    mostrarDetalleAliado(data);
                })
                .catch(error => {
                    console.error('Error cargando detalle:', error);
                });
        }

        function mostrarDetalleAliado(data) {
            const container = document.getElementById('aliado-detalle-container');
            container.style.display = 'block';

            // Información básica
            document.getElementById('detalle-nombre').textContent = data.aliado_nombre;
            document.getElementById('detalle-ver-pagos').href = `/admin/payouts/aliado/${data.aliado_id}`;

            // Estadísticas
            document.getElementById('detalle-total-pagos').textContent = data.total_pagos;
            document.getElementById('detalle-monto-total').textContent = 'Bs. ' + data.monto_total;

            const promedio = data.monto_total / (data.total_pagos || 1);
            document.getElementById('detalle-monto-promedio').textContent = 'Bs. ' + promedio.toLocaleString('es-ES', {
                minimumFractionDigits: 2,
                maximumFractionDigits: 2
            });

            // Estados
            const estadosHtml = Object.entries(data.estados || {}).map(([estado, count]) => {
                const badgeClass = {
                    'pending': 'pending',
                    'processing': 'processing',
                    'completed': 'completed',
                    'reverted': 'reverted'
                } [estado] || 'pending';

                const estadoTexto = {
                    'pending': 'Pendientes',
                    'processing': 'En Proceso',
                    'completed': 'Completados',
                    'reverted': 'Revertidos'
                } [estado] || estado;

                return `
                    <div class="detalle-row">
                        <span class="detalle-label">${estadoTexto}:</span>
                        <span class="detalle-value ${estado === 'completed' ? 'success' : ''}">${count}</span>
                    </div>
                `;
            }).join('');

            document.getElementById('detalle-estados').innerHTML = estadosHtml;

            // Últimos pagos
            const ultimosPagosHtml = (data.ultimos_pagos || []).map(pago => `
                <div class="detalle-row">
                    <span class="detalle-label">${pago.fecha}:</span>
                    <span class="detalle-value success">Bs. ${pago.monto}</span>
                </div>
            `).join('') || '<p class="text-muted">No hay pagos recientes</p>';

            document.getElementById('detalle-ultimos-pagos').innerHTML = ultimosPagosHtml;

            // Actualizar gráficas
            actualizarGraficas(data);
        }

        function actualizarGraficas(data) {
            // Gráfica de evolución mensual
            const evolucionCtx = document.getElementById('evolucionChart').getContext('2d');

            if (evolucionChart) {
                evolucionChart.destroy();
            }

            evolucionChart = new Chart(evolucionCtx, {
                type: 'line',
                data: {
                    labels: data.evolucion_mensual?.labels || ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'],
                    datasets: [{
                        label: 'Monto Pagado (Bs.)',
                        data: data.evolucion_mensual?.data || [0, 0, 0, 0, 0, 0],
                        borderColor: '#8a2be2',
                        backgroundColor: 'rgba(138, 43, 226, 0.1)',
                        tension: 0.4,
                        fill: true
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                callback: value => 'Bs. ' + value.toLocaleString('es-ES')
                            }
                        }
                    }
                }
            });

            // Gráfica de distribución por estado
            const estadosCtx = document.getElementById('estadosChart').getContext('2d');

            if (estadosChart) {
                estadosChart.destroy();
            }

            const estadosData = data.estados_grafica || {
                labels: ['Pendientes', 'Procesando', 'Completados', 'Revertidos'],
                data: [0, 0, 0, 0]
            };

            estadosChart = new Chart(estadosCtx, {
                type: 'doughnut',
                data: {
                    labels: estadosData.labels,
                    datasets: [{
                        data: estadosData.data,
                        backgroundColor: ['#f59e0b', '#3b82f6', '#10b981', '#ef4444'],
                        borderWidth: 0
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            position: 'bottom'
                        }
                    }
                }
            });
        }
    </script>
@endpush
