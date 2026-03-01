@extends('layouts.admin')

@section('page_title_toolbar', 'Gestión de Pagos a Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/payouts-modern.css') }}">
@endpush

@section('content')
    <div class="main-content">
        {{-- Header con efecto glassmorphism y gradiente animado --}}
        <div class="page-header">
            <h1 class="page-title">
                <span class="text-gray-900">Gestión de</span>
                <span class="text-purple">Pagos a Aliados</span>
            </h1>
            <div class="page-actions">
                <a href="{{ route('admin.payouts.pendientes') }}" class="action-button primary">
                    <i class="fas fa-clock"></i> Ver Pendientes
                </a>
                <a href="{{ route('admin.payouts.estadisticas') }}" class="action-button secondary">
                    <i class="fas fa-chart-bar"></i> Estadísticas
                </a>
            </div>
        </div>

        {{-- Filtros y Búsqueda con diseño moderno --}}
        <div class="filters-card">
            <form action="{{ route('admin.payouts.index') }}" method="GET" class="filters-form">
                <div class="filters-grid">
                    <div class="form-group">
                        <label for="status">Estado</label>
                        <select name="status" id="status" class="form-control">
                            <option value="all" {{ request('status') == 'all' ? 'selected' : '' }}>Todos</option>
                            <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                            <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Procesando</option>
                            <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completado</option>
                            <option value="reverted" {{ request('status') == 'reverted' ? 'selected' : '' }}>Revertido</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="ally_id">Aliado</label>
                        <select name="ally_id" id="ally_id" class="form-control">
                            <option value="">Todos los aliados</option>
                            @foreach($aliados ?? [] as $aliado)
                                @php
                                    $aliadoId = is_object($aliado) ? $aliado->id : ($aliado['id'] ?? null);
                                    $aliadoNombre = is_object($aliado) ? ($aliado->name ?? $aliado->company_name ?? 'Aliado') : ($aliado['name'] ?? $aliado['company_name'] ?? 'Aliado');
                                @endphp
                                @if($aliadoId)
                                    <option value="{{ $aliadoId }}" {{ request('ally_id') == $aliadoId ? 'selected' : '' }}>
                                        {{ $aliadoNombre }}
                                    </option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control"
                            value="{{ request('fecha_inicio', now()->subMonth()->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control"
                            value="{{ request('fecha_fin', now()->format('Y-m-d')) }}">
                    </div>

                    <div class="form-group">
                        <label for="search">Búsqueda</label>
                        <input type="text" name="search" id="search" class="form-control"
                            placeholder="ID, referencia, aliado..." value="{{ request('search') }}">
                    </div>

                    <div class="form-group filter-actions">
                        <label>&nbsp;</label>
                        <div class="button-group">
                            <button type="submit" class="action-button primary">
                                <i class="fas fa-search"></i> Filtrar
                            </button>
                            <a href="{{ route('admin.payouts.index') }}" class="action-button secondary">
                                <i class="fas fa-times"></i> Limpiar
                            </a>
                        </div>
                    </div>
                </div>
            </form>
        </div>

        {{-- Alertas con diseño moderno --}}
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

        {{-- Resumen rápido con tarjetas estadísticas --}}
        @if (isset($estadisticas))
            <div class="stats-grid small">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($estadisticas['total_pendiente'] ?? 0) }}</div>
                        <div class="stat-label">Pendientes</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-sync-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($estadisticas['total_procesando'] ?? 0) }}</div>
                        <div class="stat-label">Procesando</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($estadisticas['total_completado'] ?? 0) }}</div>
                        <div class="stat-label">Completados</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-undo-alt"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ number_format($estadisticas['total_revertido'] ?? 0) }}</div>
                        <div class="stat-label">Revertidos</div>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabla de pagos con diseño moderno --}}
        <div class="table-card">
            @if (empty($payouts) || count($payouts) === 0)
                <div class="no-data-message">
                    <i class="fas fa-money-bill-wave icon"></i>
                    <h3>No hay pagos registrados</h3>
                    <p class="text-muted">Los pagos aparecerán aquí cuando se procesen ventas con aliados.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="data-table">
                        <thead>
                            <tr>
                                <th>ID</th>
                                <th>Aliado</th>
                                <th>Venta ID</th>
                                <th>Monto Venta</th>
                                <th>Comisión</th>
                                <th>Neto</th>
                                <th>Estado</th>
                                <th>Fecha Generación</th>
                                <th>Fecha Pago</th>
                                <th>Referencia</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($payouts as $payout)
                                @php
                                    // Acceso directo a los campos de la tabla payouts
                                    $payoutId = $payout->id ?? $payout['id'] ?? null;
                                    
                                    // Montos directos de la tabla payouts
                                    $saleAmount = $payout->sale_amount ?? $payout['sale_amount'] ?? 0;
                                    $commissionPercentage = $payout->commission_percentage ?? $payout['commission_percentage'] ?? 0;
                                    $commissionAmount = $payout->commission_amount ?? $payout['commission_amount'] ?? 0;
                                    $netAmount = $payout->net_amount ?? $payout['net_amount'] ?? 0;
                                    $allyDiscount = $payout->ally_discount ?? $payout['ally_discount'] ?? 0;
                                    
                                    // Fechas y referencias
                                    $status = $payout->status ?? $payout['status'] ?? 'pending';
                                    $generationDate = $payout->generation_date ?? $payout['generation_date'] ?? $payout->created_at ?? $payout['created_at'] ?? null;
                                    $paymentDate = $payout->payment_date ?? $payout['payment_date'] ?? null;
                                    $paymentReference = $payout->payment_reference ?? $payout['payment_reference'] ?? null;
                                    $saleId = $payout->sale_id ?? $payout['sale_id'] ?? null;
                                    
                                    // Datos del aliado (si existe relación)
                                    $allyName = 'N/A';
                                    $allyEmail = '';
                                    
                                    if (isset($payout->ally) && $payout->ally) {
                                        $allyName = $payout->ally->name ?? $payout->ally->company_name ?? 'Aliado';
                                        $allyEmail = $payout->ally->email ?? '';
                                    } elseif (isset($payout['ally']) && is_array($payout['ally'])) {
                                        $allyName = $payout['ally']['name'] ?? $payout['ally']['company_name'] ?? 'Aliado';
                                        $allyEmail = $payout['ally']['email'] ?? '';
                                    }
                                @endphp

                                @if ($payoutId)
                                    <tr>
                                        <td><strong>#{{ $payoutId }}</strong></td>
                                        <td>
                                            <div class="font-weight-bold">{{ $allyName }}</div>
                                            @if($allyEmail)
                                                <small class="text-muted">{{ $allyEmail }}</small>
                                            @endif
                                        </td>
                                        <td>
                                            @if($saleId)
                                                <a href="{{ route('admin.payouts.show', $payoutId) }}" class="text-purple">
                                                    #{{ $saleId }}
                                                </a>
                                            @else
                                                <span class="text-muted">N/A</span>
                                            @endif
                                        </td>
                                        <td class="text-success">
                                            <strong>Bs. {{ number_format($saleAmount, 2, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            <span class="badge badge-warning">
                                                {{ number_format($commissionPercentage, 1) }}%
                                            </span>
                                            <br>
                                            <small class="text-danger">
                                                Bs. {{ number_format($commissionAmount, 2, ',', '.') }}
                                            </small>
                                            @if($allyDiscount > 0)
                                                <br>
                                                <small class="text-muted">
                                                    Dto: {{ number_format($allyDiscount, 1) }}%
                                                </small>
                                            @endif
                                        </td>
                                        <td class="text-success">
                                            <strong>Bs. {{ number_format($netAmount, 2, ',', '.') }}</strong>
                                        </td>
                                        <td>
                                            @php
                                                $estadoClase = match ($status) {
                                                    'completed' => 'badge-success',
                                                    'processing' => 'badge-warning',
                                                    'pending' => 'badge-info',
                                                    'reverted' => 'badge-danger',
                                                    default => 'badge-info',
                                                };
                                                $estadoTexto = match ($status) {
                                                    'completed' => 'Completado',
                                                    'processing' => 'Procesando',
                                                    'pending' => 'Pendiente',
                                                    'reverted' => 'Revertido',
                                                    default => 'Pendiente',
                                                };
                                            @endphp
                                            <span class="badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                                        </td>
                                        <td>
                                            @if ($generationDate)
                                                @php
                                                    $fechaGenObj = $generationDate instanceof \Carbon\Carbon
                                                        ? $generationDate
                                                        : \Carbon\Carbon::parse($generationDate);
                                                @endphp
                                                {{ $fechaGenObj->format('d/m/Y') }}
                                                <br>
                                                <small class="text-muted">{{ $fechaGenObj->format('H:i') }}</small>
                                            @else
                                                N/A
                                            @endif
                                        </td>
                                        <td>
                                            @if ($paymentDate)
                                                {{ \Carbon\Carbon::parse($paymentDate)->format('d/m/Y') }}
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($paymentReference)
                                                <code>{{ $paymentReference }}</code>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="action-buttons">
                                                <a href="{{ route('admin.payouts.show', $payoutId) }}"
                                                    class="btn-icon" title="Ver detalles">
                                                    <i class="fas fa-eye"></i>
                                                </a>

                                                @if ($status === 'pending')
                                                    <a href="{{ route('admin.payouts.edit', $payoutId) }}"
                                                        class="btn-icon" title="Editar">
                                                        <i class="fas fa-edit"></i>
                                                    </a>

                                                    <button type="button" class="btn-icon"
                                                        onclick="confirmarRevertir({{ $payoutId }})"
                                                        title="Revertir">
                                                        <i class="fas fa-undo-alt"></i>
                                                    </button>
                                                @endif

                                                <a href="{{ route('admin.payouts.auditoria', $payoutId) }}"
                                                    class="btn-icon" title="Auditoría">
                                                    <i class="fas fa-history"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="11" class="text-center py-4">
                                        <div class="no-data-message">
                                            <i class="fas fa-money-bill-wave icon"></i>
                                            <h3>No hay pagos registrados</h3>
                                            <p class="text-muted">Los pagos aparecerán aquí cuando se procesen ventas con
                                                aliados.</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                {{-- Paginación con diseño moderno --}}
                @if (isset($pagination))
                    <div class="pagination-container">
                        <div class="pagination-info">
                            <i class="fas fa-list-ul"></i>
                            Mostrando {{ ($pagination['current_page'] - 1) * $pagination['per_page'] + 1 }}
                            - {{ min($pagination['current_page'] * $pagination['per_page'], $pagination['total']) }}
                            de {{ $pagination['total'] }} pagos
                        </div>
                        <div class="pagination-links">
                            @if ($pagination['current_page'] > 1)
                                <a href="{{ route('admin.payouts.index', array_merge(request()->query(), ['page' => $pagination['current_page'] - 1])) }}"
                                    class="pagination-link">
                                    <i class="fas fa-chevron-left"></i>
                                </a>
                            @endif

                            @for ($i = 1; $i <= $pagination['last_page']; $i++)
                                @if ($i == $pagination['current_page'])
                                    <span class="pagination-link active">{{ $i }}</span>
                                @else
                                    <a href="{{ route('admin.payouts.index', array_merge(request()->query(), ['page' => $i])) }}"
                                        class="pagination-link">{{ $i }}</a>
                                @endif
                            @endfor

                            @if ($pagination['current_page'] < $pagination['last_page'])
                                <a href="{{ route('admin.payouts.index', array_merge(request()->query(), ['page' => $pagination['current_page'] + 1])) }}"
                                    class="pagination-link">
                                    <i class="fas fa-chevron-right"></i>
                                </a>
                            @endif
                        </div>
                    </div>
                @endif
            @endif
        </div>
    </div>

    {{-- Modal de confirmación para revertir con diseño moderno --}}
    <div id="revertirModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-undo-alt"></i> Revertir Pago</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <form action="" method="POST" id="revertirForm">
                @csrf
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres revertir este pago?</p>
                    <p class="text-warning">
                        <i class="fas fa-exclamation-triangle"></i> 
                        Esta acción no se puede deshacer.
                    </p>

                    <div class="form-group mt-4">
                        <label for="motivo">Motivo de la reversión</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3"
                            placeholder="Indique el motivo de la reversión..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-undo-alt"></i> Revertir Pago
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Cerrar alertas
            document.querySelectorAll('.close-alert').forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });

            // Modal de revertir
            const revertirModal = document.getElementById('revertirModal');
            const revertirForm = document.getElementById('revertirForm');
            const closeButtons = document.querySelectorAll('.close-modal-btn, .cancel-modal-btn');

            window.confirmarRevertir = function(payoutId) {
                // Usar la ruta correcta con el parámetro payoutId
                revertirForm.action = '/admin/payouts/' + payoutId + '/revertir';
                revertirModal.classList.remove('hidden');
            };

            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    revertirModal.classList.add('hidden');
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target === revertirModal) {
                    revertirModal.classList.add('hidden');
                }
            });
        });
    </script>
@endpush