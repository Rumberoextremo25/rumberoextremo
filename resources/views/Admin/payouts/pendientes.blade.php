@extends('layouts.admin')

@section('page_title_toolbar', 'Pagos Pendientes a Aliados')

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/payout.css') }}">
@endpush

@section('content')
    <div class="main-content">
        <h1 class="page-title">
            <span class="text-gray-900">Pagos Pendientes</span>
            <span class="text-purple">a Aliados</span>
        </h1>

        {{-- Estadísticas --}}
        @if (isset($estadisticas) && !empty($estadisticas) && isset($payouts) && count($payouts) > 0)
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-clock"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $estadisticas['total_pendiente'] ?? 0 }}</div>
                        <div class="stat-label">Pagos Pendientes</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-dollar-sign"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">Bs. {{ number_format($monto_total ?? 0, 2, ',', '.') }}</div>
                        <div class="stat-label">Monto Total Pendiente</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-handshake"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ $estadisticas['total_aliados'] ?? 0 }}</div>
                        <div class="stat-label">Aliados con Pagos</div>
                    </div>
                </div>
            </div>
        @endif

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

        @if (!isset($payouts) || empty($payouts) || count($payouts) === 0)
            <div class="no-payouts-message">
                <i class="fas fa-sack-dollar icon"></i>
                <h3>No hay pagos pendientes</h3>
                <p class="text-muted">Los pagos se generan automáticamente cuando se procesan ventas con aliados.</p>
            </div>
        @else
            {{-- Acciones --}}
            <div class="table-actions">
                {{-- Formulario BNC --}}
                <form action="{{ route('admin.payouts.generar-bnc') }}" method="POST" class="action-form" id="generateBncForm">
                    @csrf
                    <div class="form-group">
                        <label for="fecha_inicio">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" id="fecha_inicio" class="form-control" required
                            value="{{ date('Y-m-01') }}">
                    </div>
                    <div class="form-group">
                        <label for="fecha_fin">Fecha Fin</label>
                        <input type="date" name="fecha_fin" id="fecha_fin" class="form-control" required
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="tipo_cuenta">Tipo de Cuenta</label>
                        <select name="tipo_cuenta" id="tipo_cuenta" class="form-control" required>
                            <option value="">Seleccione...</option>
                            <option value="corriente" selected>Cuenta Corriente</option>
                            <option value="ahorro">Cuenta de Ahorro</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="concepto">Concepto</label>
                        <input type="text" name="concepto" id="concepto" class="form-control"
                            value="PAGO COMISION {{ strtoupper(date('F Y')) }}" maxlength="60">
                    </div>
                    <button type="submit" class="action-button generate-csv-btn">
                        <i class="fas fa-file-export"></i> Generar Archivo BNC
                    </button>
                </form>

                {{-- Formulario Confirmación --}}
                <form action="{{ route('admin.payouts.confirmar') }}" method="POST" class="action-form"
                    id="confirmPayoutsForm" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="payout_ids" id="selected-payout-ids">
                    <div class="form-group">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" required
                            value="{{ date('Y-m-d') }}">
                    </div>
                    <div class="form-group">
                        <label for="referencia_pago">Referencia Bancaria</label>
                        <input type="text" name="referencia_pago" id="referencia_pago" class="form-control"
                            placeholder="Ej: TRF-123456" required>
                    </div>
                    <div class="form-group">
                        <label for="archivo_comprobante">Comprobante (opcional)</label>
                        <input type="file" name="archivo_comprobante" id="archivo_comprobante" class="form-control"
                            accept=".pdf,.jpg,.png">
                    </div>
                    <button type="button" class="action-button process-btn" id="confirm-payouts-btn">
                        <i class="fas fa-check-circle"></i> Confirmar Pagos Seleccionados
                    </button>
                </form>
            </div>

            {{-- Tabla --}}
            <div class="table-responsive">
                <table class="payouts-table">
                    <thead>
                        <tr>
                            <th class="w-10"><input type="checkbox" id="select-all" class="rounded-sm"></th>
                            <th>ID</th>
                            <th>Aliado</th>
                            <th>Email</th>
                            <th>Monto Venta</th>
                            <th>Dto.</th>
                            <th>Comisión</th>
                            <th>Monto Com.</th>
                            <th>Neto a Pagar</th>
                            <th>Cuenta</th>
                            <th>Banco</th>
                            <th>Fecha</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($payouts as $payout)
                            @if (is_array($payout) && isset($payout['id']))
                                <tr>
                                    <td>
                                        <input type="checkbox" name="payout_ids[]" value="{{ $payout['id'] }}"
                                            class="payout-checkbox rounded-sm">
                                    </td>
                                    <td><strong>#{{ $payout['id'] }}</strong></td>
                                    <td>
                                        <div class="font-weight-bold">{{ $payout['aliado']['nombre'] ?? 'N/A' }}</div>
                                        <small class="text-muted">ID: {{ $payout['aliado']['id'] ?? '' }}</small>
                                    </td>
                                    <td>{{ $payout['aliado']['email'] ?? 'N/A' }}</td>
                                    <td class="text-success">
                                        <strong>
                                            {{ isset($payout['montos']['monto_despues_descuento']) 
                                                ? 'Bs. ' . number_format($payout['montos']['monto_despues_descuento'], 2, ',', '.')
                                                : (isset($payout['montos']['neto']) 
                                                    ? 'Bs. ' . number_format($payout['montos']['neto'], 2, ',', '.')
                                                    : 'Bs. 0,00') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">
                                            {{ $payout['montos']['descuento_aliado'] ?? 0 }}%
                                        </span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">
                                            {{ $payout['montos']['comision_porcentaje'] ?? 0 }}%
                                        </span>
                                    </td>
                                    <td class="text-danger">
                                        <strong>
                                            Bs. {{ number_format($payout['montos']['comision_monto'] ?? 0, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td class="text-success">
                                        <strong>
                                            Bs. {{ number_format($payout['montos']['neto'] ?? 0, 2, ',', '.') }}
                                        </strong>
                                    </td>
                                    <td>
                                        <code>{{ $payout['aliado']['cuenta_bancaria'] ?? 'N/A' }}</code>
                                        <br>
                                        <small class="text-muted">{{ $payout['aliado']['tipo_cuenta'] ?? '' }}</small>
                                    </td>
                                    <td>{{ $payout['aliado']['banco'] ?? 'N/A' }}</td>
                                    <td>
                                        @php
                                            $fecha = isset($payout['fechas']['generacion'])
                                                ? \Carbon\Carbon::parse($payout['fechas']['generacion'])
                                                : null;
                                        @endphp
                                        @if ($fecha)
                                            {{ $fecha->format('d/m/Y') }}
                                            <br>
                                            <small class="text-muted">{{ $fecha->format('H:i') }}</small>
                                        @else
                                            N/A
                                        @endif
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>

            {{-- Resumen de montos --}}
            <div class="stats-grid mt-4">
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-coins"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">Bs. {{ number_format($monto_total ?? 0, 2, ',', '.') }}</div>
                        <div class="stat-label">Total a Pagar</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-percent"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">
                            Bs. {{ number_format(collect($payouts)->sum('montos.comision_monto'), 2, ',', '.') }}
                        </div>
                        <div class="stat-label">Total Comisiones</div>
                    </div>
                </div>
                <div class="stat-item">
                    <div class="stat-icon">
                        <i class="fas fa-money-bill"></i>
                    </div>
                    <div class="stat-content">
                        <div class="stat-value">{{ count($payouts) }}</div>
                        <div class="stat-label">Pagos Seleccionables</div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de Confirmación --}}
    <div id="confirmationModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Procesamiento de Pagos</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p id="modal-message">¿Estás seguro de que quieres confirmar los <span id="selected-count">0</span> pagos seleccionados?</p>
                <p class="text-warning"><strong id="modal-total"></strong></p>
                <p class="text-muted">Esta acción no se puede deshacer.</p>
                
                <div class="form-group mt-4">
                    <label for="modal-fecha-pago">Fecha de Pago:</label>
                    <input type="date" id="modal-fecha-pago" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="modal-referencia">Referencia de Pago:</label>
                    <input type="text" id="modal-referencia" class="form-control" placeholder="Ej: TRF-123456" required>
                </div>
                <div class="form-group">
                    <label for="modal-comprobante">Comprobante (opcional):</label>
                    <input type="file" id="modal-comprobante" class="form-control" accept=".pdf,.jpg,.png">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <button type="button" class="btn confirm-modal-btn">Confirmar Pagos</button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Elementos del DOM
            const selectAllCheckbox = document.getElementById('select-all');
            const payoutCheckboxes = document.querySelectorAll('.payout-checkbox');
            const confirmPayoutsButton = document.getElementById('confirm-payouts-btn');
            const confirmationModal = document.getElementById('confirmationModal');
            const confirmModalButton = confirmationModal ? confirmationModal.querySelector('.confirm-modal-btn') : null;
            const cancelModalButton = confirmationModal ? confirmationModal.querySelector('.cancel-modal-btn') : null;
            const closeModalButton = confirmationModal ? confirmationModal.querySelector('.close-modal-btn') : null;
            const selectedCountSpan = document.getElementById('selected-count');
            const fechaPagoInput = document.getElementById('modal-fecha-pago');
            const referenciaInput = document.getElementById('modal-referencia');
            const comprobanteInput = document.getElementById('modal-comprobante');
            const generateBncForm = document.getElementById('generateBncForm');

            // Función para actualizar contador de seleccionados
            function updateSelectedCount() {
                const selectedIds = Array.from(payoutCheckboxes)
                    .filter(cb => cb.checked)
                    .map(cb => cb.value);
                
                if (selectedCountSpan) {
                    selectedCountSpan.textContent = selectedIds.length;
                }

                if (confirmPayoutsButton) {
                    confirmPayoutsButton.innerHTML = selectedIds.length > 0
                        ? `<i class="fas fa-check-circle"></i> Confirmar ${selectedIds.length} Pagos`
                        : `<i class="fas fa-check-circle"></i> Confirmar Pagos Seleccionados`;
                }
            }

            // Manejar selección de todos los checkboxes
            if (selectAllCheckbox && payoutCheckboxes.length > 0) {
                selectAllCheckbox.addEventListener('change', function() {
                    payoutCheckboxes.forEach(checkbox => {
                        checkbox.checked = this.checked;
                    });
                    updateSelectedCount();
                });

                payoutCheckboxes.forEach(checkbox => {
                    checkbox.addEventListener('change', updateSelectedCount);
                });
            }

            // Manejar click en botón de confirmar pagos
            if (confirmPayoutsButton) {
                confirmPayoutsButton.addEventListener('click', function() {
                    const selectedIds = Array.from(payoutCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);

                    if (selectedIds.length === 0) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Selección requerida',
                            text: 'Por favor, selecciona al menos un pago para confirmar.',
                            confirmButtonColor: '#8a2be2'
                        });
                        return;
                    }

                    // Calcular monto total seleccionado
                    const totalAmount = Array.from(payoutCheckboxes)
                        .filter(cb => cb.checked)
                        .reduce((sum, cb) => {
                            const row = cb.closest('tr');
                            if (row) {
                                const amountCell = row.querySelector('td:nth-child(9) strong');
                                if (amountCell) {
                                    const amountText = amountCell.textContent;
                                    const amount = parseFloat(amountText.replace(/[Bs\.,\s]/g, '').replace(',', '.'));
                                    return sum + (isNaN(amount) ? 0 : amount);
                                }
                            }
                            return sum;
                        }, 0);

                    // Mostrar modal de confirmación
                    if (confirmationModal) {
                        confirmationModal.classList.remove('hidden');
                        if (selectedCountSpan) {
                            selectedCountSpan.textContent = selectedIds.length;
                        }
                        
                        const modalBody = confirmationModal.querySelector('.modal-body p:first-child');
                        if (modalBody) {
                            modalBody.innerHTML = `¿Estás seguro de que quieres confirmar los <strong>${selectedIds.length}</strong> pagos seleccionados?<br>
                                <strong>Monto total: Bs. ${totalAmount.toFixed(2).replace('.', ',')}</strong>`;
                        }
                    }
                });
            }

            // Manejar confirmación en modal
            if (confirmModalButton) {
                confirmModalButton.addEventListener('click', function() {
                    const selectedIds = Array.from(payoutCheckboxes)
                        .filter(cb => cb.checked)
                        .map(cb => cb.value);
                    const form = document.getElementById('confirmPayoutsForm');
                    const hiddenInput = document.getElementById('selected-payout-ids');

                    // Validar campos requeridos
                    if (!fechaPagoInput || !fechaPagoInput.value || !referenciaInput || !referenciaInput.value) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Campos requeridos',
                            text: 'La fecha y referencia de pago son obligatorias.',
                            confirmButtonColor: '#8a2be2'
                        });
                        return;
                    }

                    // Setear valores en el formulario
                    if (hiddenInput) {
                        hiddenInput.value = JSON.stringify(selectedIds);
                    }

                    if (form) {
                        const fechaPagoField = form.querySelector('input[name="fecha_pago"]');
                        const referenciaField = form.querySelector('input[name="referencia_pago"]');
                        const comprobanteField = form.querySelector('input[name="archivo_comprobante"]');

                        if (fechaPagoField) fechaPagoField.value = fechaPagoInput.value;
                        if (referenciaField) referenciaField.value = referenciaInput.value;

                        if (comprobanteInput && comprobanteInput.files.length > 0 && comprobanteField) {
                            const dataTransfer = new DataTransfer();
                            dataTransfer.items.add(comprobanteInput.files[0]);
                            comprobanteField.files = dataTransfer.files;
                        }
                    }

                    // Mostrar loading y enviar formulario
                    Swal.fire({
                        title: 'Procesando pagos...',
                        text: 'Por favor espere...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    if (form) {
                        form.submit();
                    }
                });
            }

            // Cerrar modal
            const closeModalButtons = [closeModalButton, cancelModalButton];
            closeModalButtons.forEach(button => {
                if (button) {
                    button.addEventListener('click', function() {
                        if (confirmationModal) {
                            confirmationModal.classList.add('hidden');
                        }
                    });
                }
            });

            // Cerrar modal al hacer click fuera
            window.addEventListener('click', function(event) {
                if (event.target === confirmationModal) {
                    confirmationModal.classList.add('hidden');
                }
            });

            // Manejar envío del formulario BNC
            if (generateBncForm) {
                generateBncForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    Swal.fire({
                        title: 'Generando archivo BNC',
                        text: 'Por favor espere...',
                        allowOutsideClick: false,
                        didOpen: () => {
                            Swal.showLoading();
                        }
                    });

                    this.submit();
                });
            }

            // Cerrar alertas
            document.querySelectorAll('.close-alert').forEach(button => {
                button.addEventListener('click', function() {
                    this.parentElement.style.display = 'none';
                });
            });
        });
    </script>
@endpush