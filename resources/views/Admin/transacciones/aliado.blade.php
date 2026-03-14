{{-- resources/views/Admin/transacciones/aliado.blade.php --}}
@extends('layouts.admin')

@section('title', 'Mis Transacciones - Rumbero Extremo')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="{{ asset('css/admin/transactions.css') }}">
    <style>
        /* Estilos adicionales para selección múltiple */
        .select-all-checkbox,
        .transaction-checkbox {
            width: 18px;
            height: 18px;
            cursor: pointer;
            accent-color: #A601B3;
        }

        .bulk-actions-bar {
            background: #ffffff;
            border-radius: 16px;
            padding: 1rem 1.5rem;
            margin-bottom: 1.5rem;
            border: 2px solid #A601B3;
            box-shadow: 0 4px 15px rgba(166, 1, 179, 0.15);
            animation: slideDown 0.3s ease;
        }

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .bulk-actions-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 1rem;
        }

        .selected-count {
            font-size: 1rem;
            font-weight: 600;
            color: #1e293b;
        }

        .selected-count span {
            color: #A601B3;
            font-size: 1.2rem;
            font-weight: 700;
        }

        .bulk-buttons {
            display: flex;
            gap: 0.8rem;
            flex-wrap: wrap;
        }

        .bulk-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.7rem 1.5rem;
            border: none;
            border-radius: 12px;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .bulk-btn.approve-all {
            background: #10b981;
            color: white;
            box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);
        }

        .bulk-btn.approve-all:hover {
            background: #059669;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(16, 185, 129, 0.3);
        }

        .bulk-btn.reject-all {
            background: #ef4444;
            color: white;
            box-shadow: 0 4px 10px rgba(239, 68, 68, 0.2);
        }

        .bulk-btn.reject-all:hover {
            background: #dc2626;
            transform: translateY(-2px);
            box-shadow: 0 8px 16px rgba(239, 68, 68, 0.3);
        }

        .bulk-btn.clear-all {
            background: #f1f5f9;
            color: #64748b;
        }

        .bulk-btn.clear-all:hover {
            background: #e2e8f0;
            color: #1e293b;
        }

        .table-row.selected {
            background: rgba(166, 1, 179, 0.05);
            border-left: 3px solid #A601B3;
        }

        /* Estilo para el checkbox en el header */
        .select-all-checkbox {
            margin-left: 0;
        }
    </style>
@endpush

@section('content')
    <div class="transactions-wrapper">
        {{-- Header con Gradiente --}}
        <div class="transactions-header">
            <div class="header-content">
                <div class="role-badge">
                    @if (auth()->user()->role === 'admin')
                        <span class="badge-admin">
                            <i class="fa-solid fa-crown"></i>
                            ADMINISTRADOR
                        </span>
                    @else
                        <span class="badge-aliado">
                            <i class="fa-solid fa-handshake"></i>
                            ALIADO
                        </span>
                    @endif
                </div>
                <h1 class="page-title">
                    <span class="title-main">Mis</span>
                    <span class="title-accent">Transacciones</span>
                </h1>
                <div class="page-subtitle">
                    <i class="fa-regular fa-calendar"></i>
                    <span>Historial de pagos · Actualizado {{ now()->format('d/m/Y H:i') }}</span>
                </div>
            </div>
            <div class="header-actions">
                <button class="btn-refresh" id="refreshTable">
                    <i class="fa-solid fa-rotate-right"></i>
                    Actualizar
                </button>
            </div>
        </div>

        {{-- Tarjetas de Resumen --}}
        <div class="summary-grid">
            <div class="summary-card" data-color="purple">
                <div class="card-icon">
                    <i class="fa-solid fa-coins"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Total Recibido</span>
                    <span class="card-value">$ {{ number_format($totalRecibido ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="card-trend">
                    <i class="fa-solid fa-arrow-up"></i>
                    <span>+12%</span>
                </div>
            </div>

            <div class="summary-card" data-color="orange">
                <div class="card-icon">
                    <i class="fa-solid fa-hourglass-half"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Pendiente por Cobrar</span>
                    <span class="card-value">$ {{ number_format($pendienteCobrar ?? 0, 0, ',', '.') }}</span>
                </div>
                <div class="card-trend warning">
                    <i class="fa-solid fa-clock"></i>
                    <span>Por confirmar</span>
                </div>
            </div>

            <div class="summary-card" data-color="green">
                <div class="card-icon">
                    <i class="fa-solid fa-arrow-right-arrow-left"></i>
                </div>
                <div class="card-content">
                    <span class="card-label">Total Transacciones</span>
                    <span class="card-value">{{ $totalTransacciones ?? 0 }}</span>
                </div>
                <div class="card-trend">
                    <i class="fa-solid fa-chart-line"></i>
                    <span>Histórico</span>
                </div>
            </div>
        </div>

        {{-- Selector de Aliado (solo admin) --}}
        @if (auth()->user()->role === 'admin')
            <div class="filter-section">
                <div class="filter-card">
                    <div class="filter-icon">
                        <i class="fa-solid fa-handshake"></i>
                    </div>
                    <div class="filter-content">
                        <h3>Filtrar por Aliado</h3>
                        <select id="aliadoSelector" class="filter-select">
                            <option value="">Todos los aliados</option>
                            @foreach ($aliados ?? [] as $aliado)
                                <option value="{{ $aliado->id }}"
                                    {{ request('aliado_id') == $aliado->id ? 'selected' : '' }}>
                                    {{ $aliado->name }} - {{ $aliado->email }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>
        @endif

        {{-- Panel de Filtros --}}
        <div class="filters-panel">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="text" id="searchInput" placeholder="Buscar por referencia o usuario...">
            </div>

            <div class="filters-group">
                <select id="filterDate" class="filter-select">
                    <option value="all">📅 Todas las fechas</option>
                    <option value="today">📅 Hoy</option>
                    <option value="yesterday">📅 Ayer</option>
                    <option value="week">📅 Esta semana</option>
                    <option value="month">📅 Este mes</option>
                </select>

                <select id="filterStatus" class="filter-select">
                    <option value="all">📊 Todos los estados</option>
                    <option value="confirmed">✅ Confirmada</option>
                    <option value="pending_manual_confirmation">⏳ Pendiente</option>
                    <option value="awaiting_review">⏳ En Revisión</option>
                    <option value="failed">❌ Fallida</option>
                </select>

                <button class="btn-filter" id="applyFilters">
                    <i class="fa-solid fa-filter"></i>
                    Filtrar
                </button>

                <a href="{{ route('transacciones.exportar') }}?{{ http_build_query(request()->all()) }}"
                    class="btn-export">
                    <i class="fa-solid fa-download"></i>
                    Exportar
                </a>
            </div>
        </div>

        {{-- Barra de Acciones Masivas (solo admin) --}}
        @if (auth()->user()->role === 'admin')
            <div class="bulk-actions-bar" id="bulkActionsBar" style="display: none;">
                <div class="bulk-actions-content">
                    <div class="selected-count">
                        <span id="selectedCount">0</span> transacciones seleccionadas
                    </div>
                    <div class="bulk-buttons">
                        <button class="bulk-btn approve-all" id="approveSelectedBtn">
                            <i class="fa-regular fa-circle-check"></i>
                            Aprobar seleccionadas
                        </button>
                        <button class="bulk-btn reject-all" id="rejectSelectedBtn">
                            <i class="fa-regular fa-circle-xmark"></i>
                            Rechazar seleccionadas
                        </button>
                        <button class="bulk-btn clear-all" id="clearSelectedBtn">
                            <i class="fa-regular fa-times"></i>
                            Limpiar selección
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- Tabla de Transacciones --}}
        <div class="table-container">
            <div class="table-responsive">
                <table class="modern-table">
                    <thead>
                        <tr>
                            @if (auth()->user()->role === 'admin')
                                <th style="width: 40px;">
                                    <input type="checkbox" id="selectAll" class="select-all-checkbox"
                                        title="Seleccionar todas">
                                </th>
                            @endif
                            <th>ID</th>
                            <th>Fecha</th>
                            <th>Referencia</th>
                            @if (auth()->user()->role === 'admin')
                                <th>Aliado</th>
                            @endif
                            <th>Usuario</th>
                            <th>Original</th>
                            <th>Dto.</th>
                            <th>Comisión</th>
                            <th>Neto</th>
                            <th>Método</th>
                            <th>Estado</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($transacciones as $transaccion)
                            <tr class="table-row" data-id="{{ $transaccion->id }}"
                                data-status="{{ $transaccion->status }}">
                                @if (auth()->user()->role === 'admin')
                                    <td>
                                        @if (in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']))
                                            <input type="checkbox" class="transaction-checkbox"
                                                value="{{ $transaccion->id }}">
                                        @endif
                                    </td>
                                @endif
                                <td>
                                    <span class="id-badge">#{{ $transaccion->id }}</span>
                                </td>
                                <td>
                                    <span class="date-badge">{{ $transaccion->created_at->format('d/m/Y H:i') }}</span>
                                </td>
                                <td>
                                    <span class="reference-badge">{{ $transaccion->reference_code }}</span>
                                </td>

                                @if (auth()->user()->role === 'admin')
                                    <td>
                                        <div class="user-info">
                                            <span class="user-name">{{ $transaccion->ally->company_name ?? 'N/A' }}</span>
                                            <span class="user-email">{{ $transaccion->ally->email ?? '' }}</span>
                                        </div>
                                    </td>
                                @endif

                                <td>
                                    <div class="user-info">
                                        <span class="user-name">{{ $transaccion->user->name ?? 'N/A' }}</span>
                                        <span class="user-email">{{ $transaccion->user->email ?? '' }}</span>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="amount original">${{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    @if ($transaccion->discount_percentage > 0)
                                        <span class="discount-badge">
                                            <i class="fa-solid fa-tag"></i>
                                            {{ $transaccion->discount_percentage }}%
                                        </span>
                                    @else
                                        <span class="no-discount">-</span>
                                    @endif
                                </td>
                                <td>
                                    <span
                                        class="amount commission">${{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span
                                        class="amount net">${{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
                                </td>
                                <td>
                                    <span class="method-badge method-{{ $transaccion->payment_method }}">
                                        @if ($transaccion->payment_method == 'pago_movil')
                                            <i class="fa-solid fa-mobile"></i> Pago Móvil
                                        @elseif($transaccion->payment_method == 'transferencia_bancaria')
                                            <i class="fa-solid fa-building"></i> Transferencia
                                        @else
                                            {{ $transaccion->payment_method }}
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <span class="status-badge status-{{ $transaccion->status }}">
                                        @switch($transaccion->status)
                                            @case('confirmed')
                                                <i class="fa-regular fa-circle-check"></i> Confirmada
                                            @break

                                            @case('awaiting_review')
                                                <i class="fa-regular fa-clock"></i> En Revisión
                                            @break

                                            @case('pending_manual_confirmation')
                                                <i class="fa-regular fa-hourglass"></i> Pendiente
                                            @break

                                            @case('failed')
                                                <i class="fa-regular fa-circle-xmark"></i> Fallida
                                            @break

                                            @default
                                                {{ $transaccion->status }}
                                        @endswitch
                                    </span>
                                </td>
                                <td>
                                    <div class="action-group">
                                        <button class="action-btn view" onclick="verDetalle({{ $transaccion->id }})"
                                            title="Ver detalles">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>

                                        @if (in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']) &&
                                                auth()->user()->role === 'admin')
                                            <button class="action-btn approve"
                                                onclick="aprobarTransaccion({{ $transaccion->id }})" title="Confirmar">
                                                <i class="fa-regular fa-circle-check"></i>
                                            </button>
                                            <button class="action-btn reject"
                                                onclick="rechazarTransaccion({{ $transaccion->id }})" title="Rechazar">
                                                <i class="fa-regular fa-circle-xmark"></i>
                                            </button>
                                        @endif

                                        <button class="action-btn print"
                                            onclick="imprimirComprobante({{ $transaccion->id }})" title="Imprimir">
                                            <i class="fa-regular fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ auth()->user()->role === 'admin' ? 13 : 12 }}" class="empty-state">
                                        <div class="empty-content">
                                            <i class="fa-regular fa-file-lines"></i>
                                            <h3>No hay transacciones aún</h3>
                                            <p>Las transacciones aparecerán cuando los usuarios realicen pagos</p>
                                        </div>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Paginación Moderna --}}
            <div class="pagination-modern">
                <div class="pagination-info">
                    <i class="fa-regular fa-chart-bar"></i>
                    <span>
                        Mostrando <strong>{{ $transacciones->firstItem() ?? 0 }}</strong> -
                        <strong>{{ $transacciones->lastItem() ?? 0 }}</strong> de
                        <strong>{{ $transacciones->total() }}</strong> transacciones
                    </span>
                </div>
                <div class="pagination-links">
                    {{ $transacciones->onEachSide(1)->links('pagination::bootstrap-4') }}
                </div>
            </div>
        </div>

        {{-- Modal de Detalles --}}
        <div class="modal-modern" id="transactionModal">
            <div class="modal-card" style="max-width: 600px;">
                <div class="modal-header">
                    <h3 class="modal-title">
                        <i class="fa-regular fa-receipt"></i>
                        Detalles de Transacción
                    </h3>
                    <button class="modal-close" onclick="closeModal()">
                        <i class="fa-regular fa-times"></i>
                    </button>
                </div>
                <div class="modal-body" id="transactionDetails">
                    <div class="loading-spinner">
                        <div class="spinner-ring"></div>
                        <p>Cargando detalles...</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal de Confirmación --}}
        <div class="modal-modern" id="confirmModal">
            <div class="modal-card" style="max-width: 450px;">
                <div class="modal-icon warning">
                    <i class="fa-solid fa-exclamation-triangle"></i>
                </div>
                <h3 class="modal-title" id="confirmModalTitle">Confirmar Acción</h3>
                <div class="modal-body">
                    <p id="confirmMessage">¿Estás seguro de realizar esta acción?</p>
                </div>
                <div class="modal-actions">
                    <button class="btn-secondary" onclick="closeConfirmModal()">
                        <i class="fa-regular fa-ban"></i>
                        Cancelar
                    </button>
                    <button class="btn-danger" id="confirmActionBtn">
                        <i class="fa-regular fa-check"></i>
                        Confirmar
                    </button>
                </div>
            </div>
        </div>

        <meta name="csrf-token" content="{{ csrf_token() }}">
    @endsection

    @push('scripts')
        <script src="{{ asset('js/admin/transactions.js') }}"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Variables para selección múltiple
                const selectAllCheckbox = document.getElementById('selectAll');
                const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
                const bulkActionsBar = document.getElementById('bulkActionsBar');
                const selectedCountSpan = document.getElementById('selectedCount');
                let selectedIds = new Set();

                // ========== SELECCIÓN MÚLTIPLE ==========
                if (selectAllCheckbox) {
                    // Seleccionar/Deseleccionar todos
                    selectAllCheckbox.addEventListener('change', function() {
                        transactionCheckboxes.forEach(checkbox => {
                            checkbox.checked = this.checked;
                            const row = checkbox.closest('.table-row');
                            if (checkbox.checked) {
                                selectedIds.add(parseInt(checkbox.value));
                                row?.classList.add('selected');
                            } else {
                                selectedIds.delete(parseInt(checkbox.value));
                                row?.classList.remove('selected');
                            }
                        });
                        updateBulkActionsBar();
                    });

                    // Checkbox individuales
                    transactionCheckboxes.forEach(checkbox => {
                        checkbox.addEventListener('change', function() {
                            const row = this.closest('.table-row');
                            const id = parseInt(this.value);

                            if (this.checked) {
                                selectedIds.add(id);
                                row?.classList.add('selected');
                            } else {
                                selectedIds.delete(id);
                                row?.classList.remove('selected');
                            }

                            // Actualizar estado del checkbox "Seleccionar todos"
                            selectAllCheckbox.checked = transactionCheckboxes.length ===
                                document.querySelectorAll('.transaction-checkbox:checked').length;
                            selectAllCheckbox.indeterminate =
                                document.querySelectorAll('.transaction-checkbox:checked').length > 0 &&
                                !selectAllCheckbox.checked;

                            updateBulkActionsBar();
                        });
                    });
                }

                // Actualizar barra de acciones masivas
                function updateBulkActionsBar() {
                    const count = selectedIds.size;
                    if (count > 0) {
                        selectedCountSpan.textContent = count;
                        bulkActionsBar.style.display = 'block';
                    } else {
                        bulkActionsBar.style.display = 'none';
                    }
                }

                // Limpiar selección
                document.getElementById('clearSelectedBtn')?.addEventListener('click', function() {
                    selectedIds.clear();
                    transactionCheckboxes.forEach(checkbox => {
                        checkbox.checked = false;
                        checkbox.closest('.table-row')?.classList.remove('selected');
                    });
                    if (selectAllCheckbox) {
                        selectAllCheckbox.checked = false;
                        selectAllCheckbox.indeterminate = false;
                    }
                    updateBulkActionsBar();
                });

                // Aprobar seleccionadas
                document.getElementById('approveSelectedBtn')?.addEventListener('click', function() {
                    if (selectedIds.size === 0) return;

                    const ids = Array.from(selectedIds);
                    document.getElementById('confirmModalTitle').textContent = 'Aprobar Transacciones';
                    document.getElementById('confirmMessage').innerHTML =
                        `¿Estás seguro de aprobar <strong>${ids.length}</strong> transacciones seleccionadas?`;

                    openConfirmModal('approve', ids);
                });

                // Rechazar seleccionadas
                document.getElementById('rejectSelectedBtn')?.addEventListener('click', function() {
                    if (selectedIds.size === 0) return;

                    const ids = Array.from(selectedIds);
                    document.getElementById('confirmModalTitle').textContent = 'Rechazar Transacciones';
                    document.getElementById('confirmMessage').innerHTML =
                        `¿Estás seguro de rechazar <strong>${ids.length}</strong> transacciones seleccionadas?`;

                    openConfirmModal('reject', ids);
                });

                // Abrir modal de confirmación para acciones masivas
                let currentAction = null;
                let currentIds = [];

                function openConfirmModal(action, ids) {
                    currentAction = action;
                    currentIds = ids;
                    document.getElementById('confirmModal').classList.add('active');
                }

                // Confirmar acción masiva
                document.getElementById('confirmActionBtn').addEventListener('click', function() {
                    if (!currentAction || currentIds.length === 0) return;

                    const url = currentAction === 'approve' ?
                        '{{ route('transacciones.aprobar-masivas') }}' :
                        '{{ route('transacciones.rechazar-masivas') }}';

                    fetch(url, {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            },
                            body: JSON.stringify({
                                ids: currentIds
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Recargar la página para ver los cambios
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Ocurrió un error al procesar las transacciones');
                        })
                        .finally(() => {
                            closeConfirmModal();
                        });
                });

                // ========== FUNCIONES EXISTENTES ==========

                // Refresh button
                document.getElementById('refreshTable')?.addEventListener('click', function() {
                    location.reload();
                });

                // Filtros
                document.getElementById('applyFilters')?.addEventListener('click', function() {
                    const url = new URL(window.location.href);

                    const fecha = document.getElementById('filterDate').value;
                    const estado = document.getElementById('filterStatus').value;
                    const aliado = document.getElementById('aliadoSelector')?.value;

                    if (fecha && fecha !== 'all') url.searchParams.set('fecha', fecha);
                    else url.searchParams.delete('fecha');

                    if (estado && estado !== 'all') url.searchParams.set('estado', estado);
                    else url.searchParams.delete('estado');

                    if (aliado) url.searchParams.set('aliado_id', aliado);
                    else url.searchParams.delete('aliado_id');

                    window.location.href = url.toString();
                });

                // Selector de aliado
                document.getElementById('aliadoSelector')?.addEventListener('change', function() {
                    document.getElementById('applyFilters').click();
                });

                // Animaciones de entrada
                document.querySelectorAll('.table-row').forEach((row, index) => {
                    row.style.animation = `fadeInUp 0.3s ease forwards ${index * 0.05}s`;
                });

                // Búsqueda en tiempo real
                document.getElementById('searchInput')?.addEventListener('keyup', function() {
                    const searchTerm = this.value.toLowerCase();
                    document.querySelectorAll('.table-row').forEach(row => {
                        const text = row.textContent.toLowerCase();
                        row.style.display = text.includes(searchTerm) ? '' : 'none';
                    });
                });
            });

            // ========== FUNCIONES GLOBALES ==========

            // Ver detalle de transacción
            function verDetalle(id) {
                const modal = document.getElementById('transactionModal');
                const detailsDiv = document.getElementById('transactionDetails');

                // Mostrar modal y loading
                modal.classList.add('active');
                detailsDiv.innerHTML = `
        <div class="loading-spinner">
            <div class="spinner-ring"></div>
            <p>Cargando detalles...</p>
        </div>
    `;

                // Usar la ruta correcta (sin /admin/)
                fetch(`/transacciones/${id}/detalle`, {
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        }
                    })
                    .then(response => {
                        if (!response.ok) {
                            throw new Error('Error en la respuesta del servidor');
                        }
                        return response.json();
                    })
                    .then(data => {
                        if (data.html) {
                            detailsDiv.innerHTML = data.html;
                        } else {
                            detailsDiv.innerHTML = '<p class="error">No se pudo cargar la información</p>';
                        }
                    })
                    .catch(error => {
                        console.error('Error:', error);
                        detailsDiv.innerHTML = `
            <div class="error-message">
                <i class="fa-solid fa-exclamation-circle"></i>
                <p>Error al cargar los detalles. Intenta de nuevo.</p>
            </div>
        `;
                    });
            }

            // Aprobar transacción individual
            function aprobarTransaccion(id) {
                document.getElementById('confirmModalTitle').textContent = 'Aprobar Transacción';
                document.getElementById('confirmMessage').innerHTML =
                    `¿Estás seguro de aprobar la transacción #${id}?`;

                document.getElementById('confirmActionBtn').onclick = function() {
                    fetch(`/admin/transacciones/${id}/aprobar`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            alert('Error al procesar la solicitud');
                        })
                        .finally(() => {
                            closeConfirmModal();
                        });
                };

                document.getElementById('confirmModal').classList.add('active');
            }

            // Rechazar transacción individual
            function rechazarTransaccion(id) {
                document.getElementById('confirmModalTitle').textContent = 'Rechazar Transacción';
                document.getElementById('confirmMessage').innerHTML =
                    `¿Estás seguro de rechazar la transacción #${id}?`;

                document.getElementById('confirmActionBtn').onclick = function() {
                    fetch(`/admin/transacciones/${id}/rechazar`, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                location.reload();
                            } else {
                                alert('Error: ' + data.message);
                            }
                        })
                        .catch(error => {
                            alert('Error al procesar la solicitud');
                        })
                        .finally(() => {
                            closeConfirmModal();
                        });
                };

                document.getElementById('confirmModal').classList.add('active');
            }

            // Imprimir comprobante
            function imprimirComprobante(id) {
                // Usar la ruta correcta sin /admin/
                const url = `/transacciones/${id}/comprobante`;
                window.open(url, '_blank');
            }

            // Cerrar modales
            function closeModal() {
                document.getElementById('transactionModal').classList.remove('active');
            }

            function closeConfirmModal() {
                document.getElementById('confirmModal').classList.remove('active');
            }

            // Cerrar modales con Escape
            document.addEventListener('keydown', function(e) {
                if (e.key === 'Escape') {
                    closeModal();
                    closeConfirmModal();
                }
            });
        </script>
    @endpush
