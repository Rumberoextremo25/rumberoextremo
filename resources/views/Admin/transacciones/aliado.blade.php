{{-- resources/views/Admin/transacciones/aliado.blade.php --}}
@extends('layouts.admin')

@section('title', 'Mis Transacciones')

@section('page_title_toolbar', 'Mis Transacciones')

@section('content')
    @php
        $isAdmin = auth()->user()->role === 'admin';

        $statusMeta = [
            'confirmed' => ['label' => 'Confirmada', 'badge' => 'badge-success', 'icon' => 'fa-circle-check'],
            'pending_manual_confirmation' => ['label' => 'Pendiente', 'badge' => 'badge-warning', 'icon' => 'fa-hourglass-half'],
            'awaiting_review' => ['label' => 'En revisión', 'badge' => 'badge-warning', 'icon' => 'fa-clock'],
            'failed' => ['label' => 'Fallida', 'badge' => 'badge-danger', 'icon' => 'fa-circle-xmark'],
        ];

        $methodLabels = [
            'pago_movil' => ['label' => 'Pago móvil', 'icon' => 'fa-mobile-screen'],
            'transferencia_bancaria' => ['label' => 'Transferencia', 'icon' => 'fa-building-columns'],
            'transferencia' => ['label' => 'Transferencia', 'icon' => 'fa-building-columns'],
            'tarjeta' => ['label' => 'Tarjeta', 'icon' => 'fa-credit-card'],
            'efectivo' => ['label' => 'Efectivo', 'icon' => 'fa-money-bill'],
        ];
    @endphp

    <div class="cm-page">

        {{-- ============================ CABECERA ============================ --}}
        <div class="page-header-block">
            <div>
                <h1 class="title-main">Mis <span class="title-accent">transacciones</span></h1>
                <p class="page-subtitle">
                    Historial de pagos, comisiones y estado de cada operación.
                </p>
            </div>

            <div class="header-actions">
                <span class="cm-chip {{ $isAdmin ? 'cm-chip--primary' : '' }}">
                    <i class="fa-solid {{ $isAdmin ? 'fa-crown' : 'fa-handshake' }}"></i>
                    {{ $isAdmin ? 'Administrador' : 'Aliado' }}
                </span>
                <button type="button" class="btn btn-secondary" id="refreshTable">
                    <i class="fa-solid fa-rotate-right"></i> Actualizar
                </button>
            </div>
        </div>

        {{-- ============================= MÉTRICAS ============================= --}}
        <div class="cm-stats">
            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--green"><i class="fa-solid fa-coins"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($totalRecibido ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Total recibido</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-circle-check"></i> Confirmado</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon cm-stat__icon--gold"><i class="fa-solid fa-hourglass-half"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">${{ number_format($pendienteCobrar ?? 0, 0, ',', '.') }}</span>
                    <span class="cm-stat__label">Pendiente por cobrar</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-clock"></i> Por confirmar</span>
                </span>
            </div>

            <div class="cm-stat">
                <span class="cm-stat__icon"><i class="fa-solid fa-right-left"></i></span>
                <span class="cm-stat__body">
                    <span class="cm-stat__value">{{ number_format($totalTransacciones ?? 0) }}</span>
                    <span class="cm-stat__label">Transacciones</span>
                    <span class="cm-stat__delta"><i class="fa-solid fa-chart-line"></i> Histórico</span>
                </span>
            </div>
        </div>

        {{-- ============================= FILTROS ============================= --}}
        <div class="cm-filters">
            <div class="search-box">
                <i class="fa-solid fa-magnifying-glass"></i>
                <input type="search" id="searchInput" placeholder="Buscar por referencia o usuario…"
                    aria-label="Buscar transacciones">
            </div>

            @if ($isAdmin)
                <select id="aliadoSelector" class="filter-select" aria-label="Filtrar por aliado">
                    <option value="">Todos los aliados</option>
                    @foreach ($aliados ?? [] as $aliado)
                        <option value="{{ $aliado->id }}" @selected((string) request('aliado_id') === (string) $aliado->id)>
                            {{ $aliado->company_name }}
                        </option>
                    @endforeach
                </select>
            @endif

            <select id="filterDate" class="filter-select" aria-label="Filtrar por fecha">
                <option value="all">Todas las fechas</option>
                <option value="today">Hoy</option>
                <option value="yesterday">Ayer</option>
                <option value="week">Esta semana</option>
                <option value="month">Este mes</option>
            </select>

            <select id="filterStatus" class="filter-select" aria-label="Filtrar por estado">
                <option value="all">Todos los estados</option>
                <option value="confirmed">Confirmada</option>
                <option value="pending_manual_confirmation">Pendiente</option>
                <option value="awaiting_review">En revisión</option>
                <option value="failed">Fallida</option>
            </select>

            <button type="button" class="btn btn-primary btn-sm" id="applyFilters">
                <i class="fa-solid fa-filter"></i> Filtrar
            </button>

            <a href="{{ route('transacciones.exportar') }}?{{ http_build_query(request()->all()) }}"
                class="btn btn-secondary btn-sm">
                <i class="fa-solid fa-download"></i> Exportar
            </a>
        </div>

        {{-- Barra de Acciones Masivas (solo admin) --}}
        @if ($isAdmin)
            <div class="tx-bulk" id="bulkActionsBar" style="display: none;">
                <div class="tx-bulk__content">
                    <span class="tx-bulk__count">
                        <strong id="selectedCount">0</strong> transacciones seleccionadas
                    </span>

                    <div class="tx-bulk__actions">
                        <button type="button" class="btn btn-success btn-sm" id="approveSelectedBtn">
                            <i class="fa-solid fa-circle-check"></i> Aprobar
                        </button>
                        <button type="button" class="btn btn-danger btn-sm" id="rejectSelectedBtn">
                            <i class="fa-solid fa-circle-xmark"></i> Rechazar
                        </button>
                        <button type="button" class="btn btn-secondary btn-sm" id="clearSelectedBtn">
                            <i class="fa-solid fa-xmark"></i> Limpiar
                        </button>
                    </div>
                </div>
            </div>
        @endif

        {{-- ============================== TABLA ============================== --}}
        <div class="cm-table-card">
            <div class="rx-table-scroll">
                <table class="rx-stack-table tx-table">
                    <thead>
                        <tr>
                            @if ($isAdmin)
                                <th style="width:42px;">
                                    <input type="checkbox" id="selectAll" class="select-all-checkbox"
                                        title="Seleccionar todas">
                                </th>
                            @endif
                            <th>Fecha</th>
                            <th>Referencia</th>
                            @if ($isAdmin)
                                <th class="hide-mobile">Aliado</th>
                            @endif
                            <th>Usuario</th>
                            <th>Monto</th>
                            <th class="hide-mobile">Dto.</th>
                            <th class="hide-mobile hide-wide">Comisión</th>
                            <th class="hide-mobile hide-wide">Método</th>
                            <th>Estado</th>
                            <th style="text-align:right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($transacciones as $transaccion)
                            @php
                                $status = $statusMeta[$transaccion->status] ?? [
                                    'label' => ucfirst($transaccion->status),
                                    'badge' => 'badge-neutral',
                                    'icon' => 'fa-circle',
                                ];
                                $method = $methodLabels[$transaccion->payment_method] ?? [
                                    'label' => $transaccion->payment_method ?: '—',
                                    'icon' => 'fa-wallet',
                                ];
                                $selectable = in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']);
                            @endphp
                            <tr class="table-row tx-row" data-id="{{ $transaccion->id }}"
                                data-status="{{ $transaccion->status }}">
                                @if ($isAdmin)
                                    <td data-label="Seleccionar" class="{{ $selectable ? '' : 'hide-mobile' }}">
                                        @if ($selectable)
                                            <input type="checkbox" class="transaction-checkbox"
                                                value="{{ $transaccion->id }}">
                                        @endif
                                    </td>
                                @endif

                                <td data-label="Fecha">
                                    <span class="cm-date">
                                        {{ $transaccion->created_at->format('d/m/Y') }}
                                        <small>{{ $transaccion->created_at->format('H:i') }}</small>
                                    </span>
                                </td>

                                <td data-label="Referencia">
                                    <span class="cm-chip" style="font-family:monospace;">
                                        {{ $transaccion->reference_code ?: '—' }}
                                    </span>
                                </td>

                                @if ($isAdmin)
                                    <td class="hide-mobile" data-label="Aliado">
                                        <span class="cm-ally__name">{{ $transaccion->ally->company_name ?? 'N/A' }}</span>
                                    </td>
                                @endif

                                <td data-label="Usuario">
                                    <div class="cm-user">
                                        <span class="cm-user__text">
                                            <span class="cm-user__name">{{ $transaccion->user->name ?? 'N/A' }}</span>
                                            <span class="cm-user__email">{{ $transaccion->user->email ?? '' }}</span>
                                        </span>
                                    </div>
                                </td>

                                <td data-label="Monto">
                                    <strong>${{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</strong>
                                    <span class="cm-date"><small>de ${{ number_format($transaccion->original_amount, 0, ',', '.') }}</small></span>
                                </td>

                                <td class="hide-mobile" data-label="Descuento">
                                    @if ($transaccion->discount_percentage > 0)
                                        <span class="cm-chip cm-chip--primary">{{ $transaccion->discount_percentage }}%</span>
                                    @else
                                        <span class="cm-chip">—</span>
                                    @endif
                                </td>

                                <td class="hide-mobile hide-wide" data-label="Comisión">
                                    ${{ number_format($transaccion->platform_commission, 0, ',', '.') }}
                                </td>

                                <td class="hide-mobile hide-wide" data-label="Método">
                                    <span class="cm-chip">
                                        <i class="fa-solid {{ $method['icon'] }}"></i> {{ $method['label'] }}
                                    </span>
                                </td>

                                <td data-label="Estado">
                                    <span class="badge {{ $status['badge'] }}">
                                        <i class="fa-solid {{ $status['icon'] }}"></i> {{ $status['label'] }}
                                    </span>
                                </td>

                                <td data-label="Acciones">
                                    <div class="cm-actions">
                                        <button type="button" class="cm-icon-btn" title="Ver detalle"
                                            onclick="verDetalle({{ $transaccion->id }})">
                                            <i class="fa-solid fa-eye"></i>
                                        </button>

                                        @if ($isAdmin && in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']))
                                            <button type="button" class="cm-icon-btn" title="Aprobar"
                                                onclick="aprobarTransaccion({{ $transaccion->id }})">
                                                <i class="fa-solid fa-check"></i>
                                            </button>
                                            <button type="button" class="cm-icon-btn cm-icon-btn--danger"
                                                title="Rechazar" onclick="rechazarTransaccion({{ $transaccion->id }})">
                                                <i class="fa-solid fa-xmark"></i>
                                            </button>
                                        @endif

                                        <button type="button" class="cm-icon-btn" title="Imprimir comprobante"
                                            onclick="imprimirComprobante({{ $transaccion->id }})">
                                            <i class="fa-solid fa-print"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="{{ $isAdmin ? 11 : 10 }}">
                                    <div class="cm-empty" style="border:0;border-radius:0;">
                                        <i class="fa-regular fa-file-lines"></i>
                                        <h3>No hay transacciones todavía</h3>
                                        <p>Las transacciones aparecerán cuando los usuarios realicen pagos.</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <div class="cm-pagination">
                <span class="cm-pagination__info">
                    Mostrando <strong>{{ $transacciones->firstItem() ?? 0 }}</strong>–<strong>{{ $transacciones->lastItem() ?? 0 }}</strong>
                    de <strong>{{ $transacciones->total() }}</strong>
                </span>

                {{ $transacciones->onEachSide(1)->links('pagination::bootstrap-4') }}
            </div>
        </div>
    </div>

    {{-- ====================== MODAL DE DETALLE ====================== --}}
    <div class="modal cf-modal tx-modal" id="transactionModal">
        <div class="modal-card" style="max-width:600px;">
            <div class="modal-header">
                <h3 class="modal-title">
                    <i class="fa-solid fa-receipt"></i> Detalle de la transacción
                </h3>
                <button type="button" class="modal-close" onclick="closeModal()" aria-label="Cerrar">
                    <i class="fa-solid fa-xmark"></i>
                </button>
            </div>
            <div class="modal-body" id="transactionDetails">
                <div class="loading-spinner">
                    <div class="spinner-ring"></div>
                    <p>Cargando detalles…</p>
                </div>
            </div>
        </div>
    </div>

    {{-- ====================== MODAL DE CONFIRMACIÓN ====================== --}}
    <div class="modal cf-modal tx-modal" id="confirmModal">
        <div class="modal-card" style="max-width:460px;">
            <div class="modal-body">
                <div class="cf-confirm">
                    <span class="cf-confirm__icon"><i class="fa-solid fa-triangle-exclamation"></i></span>
                    <h2 id="confirmModalTitle">Confirmar acción</h2>
                    <p id="confirmMessage">¿Seguro que quieres continuar?</p>
                </div>
            </div>

            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">
                    <i class="fa-solid fa-xmark"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="confirmActionBtn">
                    <i class="fa-solid fa-check"></i> Confirmar
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const selectAllCheckbox = document.getElementById('selectAll');
            const transactionCheckboxes = document.querySelectorAll('.transaction-checkbox');
            const bulkActionsBar = document.getElementById('bulkActionsBar');
            const selectedCountSpan = document.getElementById('selectedCount');
            let selectedIds = new Set();

            if (selectAllCheckbox) {
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

                        selectAllCheckbox.checked = transactionCheckboxes.length ===
                            document.querySelectorAll('.transaction-checkbox:checked').length;
                        selectAllCheckbox.indeterminate =
                            document.querySelectorAll('.transaction-checkbox:checked').length > 0 &&
                            !selectAllCheckbox.checked;

                        updateBulkActionsBar();
                    });
                });
            }

            function updateBulkActionsBar() {
                const count = selectedIds.size;
                if (!bulkActionsBar || !selectedCountSpan) return;

                if (count > 0) {
                    selectedCountSpan.textContent = count;
                    bulkActionsBar.style.display = 'block';
                } else {
                    bulkActionsBar.style.display = 'none';
                }
            }

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

            document.getElementById('approveSelectedBtn')?.addEventListener('click', function() {
                if (selectedIds.size === 0) return;

                const ids = Array.from(selectedIds);
                document.getElementById('confirmModalTitle').textContent = 'Aprobar transacciones';
                document.getElementById('confirmMessage').innerHTML =
                    `¿Estás seguro de aprobar <strong>${ids.length}</strong> transacciones seleccionadas?`;

                openConfirmModal('approve', ids);
            });

            document.getElementById('rejectSelectedBtn')?.addEventListener('click', function() {
                if (selectedIds.size === 0) return;

                const ids = Array.from(selectedIds);
                document.getElementById('confirmModalTitle').textContent = 'Rechazar transacciones';
                document.getElementById('confirmMessage').innerHTML =
                    `¿Estás seguro de rechazar <strong>${ids.length}</strong> transacciones seleccionadas?`;

                openConfirmModal('reject', ids);
            });

            let currentAction = null;
            let currentIds = [];

            function openConfirmModal(action, ids) {
                currentAction = action;
                currentIds = ids;

                const confirmButton = document.getElementById('confirmActionBtn');
                if (confirmButton) confirmButton.onclick = submitBulkConfirm;

                document.getElementById('confirmModal').classList.add('active');
            }

            function submitBulkConfirm() {
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
            }

            document.getElementById('refreshTable')?.addEventListener('click', function() {
                location.reload();
            });

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

            document.getElementById('aliadoSelector')?.addEventListener('change', function() {
                document.getElementById('applyFilters').click();
            });

            document.getElementById('searchInput')?.addEventListener('keyup', function() {
                const searchTerm = this.value.toLowerCase();
                document.querySelectorAll('.table-row').forEach(row => {
                    const text = row.textContent.toLowerCase();
                    row.style.display = text.includes(searchTerm) ? '' : 'none';
                });
            });
        });

        function verDetalle(id) {
            const modal = document.getElementById('transactionModal');
            const detailsDiv = document.getElementById('transactionDetails');

            modal.classList.add('active');
            detailsDiv.innerHTML = `
                <div class="loading-spinner">
                    <div class="spinner-ring"></div>
                    <p>Cargando detalles…</p>
                </div>
            `;

            fetch(`/transacciones/${id}/detalle`, {
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                })
                .then(response => {
                    if (!response.ok) throw new Error('Error en la respuesta del servidor');
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
                            <i class="fa-solid fa-circle-exclamation"></i>
                            <p>Error al cargar los detalles. Intenta de nuevo.</p>
                        </div>
                    `;
                });
        }

        function aprobarTransaccion(id) {
            document.getElementById('confirmModalTitle').textContent = 'Aprobar transacción';
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
                        if (data.success) location.reload();
                        else alert('Error: ' + data.message);
                    })
                    .catch(() => alert('Error al procesar la solicitud'))
                    .finally(() => closeConfirmModal());
            };

            document.getElementById('confirmModal').classList.add('active');
        }

        function rechazarTransaccion(id) {
            document.getElementById('confirmModalTitle').textContent = 'Rechazar transacción';
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
                        if (data.success) location.reload();
                        else alert('Error: ' + data.message);
                    })
                    .catch(() => alert('Error al procesar la solicitud'))
                    .finally(() => closeConfirmModal());
            };

            document.getElementById('confirmModal').classList.add('active');
        }

        function imprimirComprobante(id) {
            window.open(`/transacciones/${id}/comprobante`, '_blank');
        }

        function closeModal() {
            document.getElementById('transactionModal').classList.remove('active');
        }

        function closeConfirmModal() {
            document.getElementById('confirmModal').classList.remove('active');
        }

        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeModal();
                closeConfirmModal();
            }
        });
    </script>
@endpush
