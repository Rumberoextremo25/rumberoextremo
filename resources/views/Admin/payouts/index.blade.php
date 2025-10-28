@extends('layouts.admin')

{{-- Define el título de la página en la toolbar --}}
@section('page_title_toolbar', 'Pagos Pendientes a Aliados')

{{-- Agrega los estilos CSS específicos de esta vista --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
    {{-- Incluir SweetAlert2 --}}
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        .hidden {
            display: none !important;
        }
        .modal-overlay {
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: rgba(0, 0, 0, 0.5);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }
        .modal-content {
            background: white;
            padding: 20px;
            border-radius: 8px;
            width: 90%;
            max-width: 500px;
        }
        .badge-warning {
            background-color: #ffc107;
            color: #000;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-info {
            background-color: #17a2b8;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
            padding: 4px 8px;
            border-radius: 4px;
            font-size: 12px;
            font-weight: 600;
        }
        .no-payouts-message {
            text-align: center;
            padding: 40px;
            color: #6c757d;
            font-size: 18px;
        }
        .no-payouts-message .icon {
            font-size: 48px;
            margin-bottom: 16px;
            color: #8a2be2;
        }
        .table-actions {
            display: flex;
            gap: 16px;
            margin-bottom: 20px;
            flex-wrap: wrap;
        }
        .action-form {
            background: #f8f9fa;
            padding: 16px;
            border-radius: 8px;
            border: 1px solid #e9ecef;
        }
        .form-group {
            margin-bottom: 12px;
        }
        .form-group label {
            display: block;
            margin-bottom: 4px;
            font-weight: 600;
            color: #495057;
        }
        .form-control {
            width: 100%;
            padding: 8px 12px;
            border: 1px solid #ced4da;
            border-radius: 4px;
            font-size: 14px;
        }
        .action-button {
            background: #8a2be2;
            color: white;
            border: none;
            padding: 10px 16px;
            border-radius: 6px;
            cursor: pointer;
            font-size: 14px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: background 0.3s;
        }
        .action-button:hover {
            background: #7b1fa2;
        }
        .payouts-table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }
        .payouts-table th {
            background: #f8f9fa;
            padding: 12px;
            text-align: left;
            font-weight: 600;
            color: #495057;
            border-bottom: 1px solid #e9ecef;
        }
        .payouts-table td {
            padding: 12px;
            border-bottom: 1px solid #e9ecef;
            vertical-align: middle;
        }
        .payouts-table tr:hover {
            background: #f8f9fa;
        }
        .text-success {
            color: #28a745;
        }
        .text-danger {
            color: #dc3545;
        }
        .stats-card {
            background: white;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 16px;
        }
        .stat-item {
            text-align: center;
            padding: 16px;
            background: #f8f9fa;
            border-radius: 6px;
        }
        .stat-value {
            font-size: 24px;
            font-weight: 700;
            color: #8a2be2;
        }
        .stat-label {
            font-size: 14px;
            color: #6c757d;
            margin-top: 4px;
        }
    </style>
@endpush

{{-- Contenido principal de la página --}}
@section('content')
    <div class="main-content">
        <h2 class="page-title text-gray-900">
            <span class="text-gray-900">Pagos Pendientes</span>
            <span style="color: #8a2be2;">a Aliados</span>
        </h2>

        {{-- Estadísticas --}}
        @if(isset($estadisticas) && !$payouts->isEmpty())
        <div class="stats-card">
            <div class="stats-grid">
                <div class="stat-item">
                    <div class="stat-value">{{ $estadisticas['total_pendiente'] ?? 0 }}</div>
                    <div class="stat-label">Pagos Pendientes</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">Bs. {{ number_format(collect($payouts)->sum('montos.neto'), 2, ',', '.') }}</div>
                    <div class="stat-label">Monto Total Pendiente</div>
                </div>
                <div class="stat-item">
                    <div class="stat-value">{{ $estadisticas['total_aliados'] ?? 0 }}</div>
                    <div class="stat-label">Aliados con Pagos</div>
                </div>
            </div>
        </div>
        @endif

        {{-- Contenedor principal para la tarjeta --}}
        <div class="payouts-card">
            {{-- Mensajes de sesión --}}
            @if (session('success'))
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert">
                    <i class="fas fa-check-circle"></i>
                    <span>{{ session('success') }}</span>
                    <button type="button" class="ml-auto text-green-700" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-6 flex items-center gap-2" role="alert">
                    <i class="fas fa-exclamation-circle"></i>
                    <span>{{ session('error') }}</span>
                    <button type="button" class="ml-auto text-red-700" onclick="this.parentElement.style.display='none';">&times;</button>
                </div>
            @endif

            @if(empty($payouts) || count($payouts) === 0)
                <div class="no-payouts-message">
                    <i class="fas fa-sack-dollar icon"></i>
                    <br>
                    No hay pagos pendientes en este momento.
                    <br>
                    <small class="text-muted">Los pagos se generan automáticamente cuando se procesan ventas con aliados.</small>
                </div>
            @else
                <div class="table-actions">
                    {{-- Formulario para generar archivo BNC --}}
                    <form action="{{ route('admin.payouts.generate_bnc') }}" method="POST" class="action-form" id="generateBncForm">
                        @csrf
                        <div class="form-group">
                            <label for="fecha_inicio">Fecha Inicio:</label>
                            <input type="date" name="fecha_inicio" class="form-control" required value="{{ date('Y-m-01') }}">
                        </div>
                        <div class="form-group">
                            <label for="fecha_fin">Fecha Fin:</label>
                            <input type="date" name="fecha_fin" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="concepto">Concepto:</label>
                            <input type="text" name="concepto" class="form-control" value="PAGO COMISION {{ strtoupper(date('F Y')) }}" maxlength="60">
                        </div>
                        <button type="submit" class="action-button generate-csv-btn">
                            <i class="fas fa-file-export"></i> Generar Archivo BNC
                        </button>
                    </form>
                    
                    {{-- Formulario para confirmar pagos --}}
                    <form action="{{ route('admin.payouts.confirm') }}" method="POST" class="action-form process-form" id="confirmPayoutsForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="payout_ids" id="selected-payout-ids">
                        <div class="form-group">
                            <label for="fecha_pago">Fecha Pago:</label>
                            <input type="date" name="fecha_pago" class="form-control" required value="{{ date('Y-m-d') }}">
                        </div>
                        <div class="form-group">
                            <label for="referencia_pago">Referencia:</label>
                            <input type="text" name="referencia_pago" placeholder="Referencia de pago" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="archivo_comprobante">Comprobante:</label>
                            <input type="file" name="archivo_comprobante" class="form-control" accept=".pdf,.jpg,.png">
                        </div>
                        <button type="button" class="action-button process-btn" id="confirm-payouts-btn">
                            <i class="fas fa-check-circle"></i> Confirmar Pagos Seleccionados
                        </button>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th class="w-10"><input type="checkbox" id="select-all" class="rounded-sm"></th>
                                <th>ID Pago</th>
                                <th>Aliado</th>
                                <th>Email</th>
                                <th>Monto Venta (Bs.)</th>
                                <th>Descuento Aliado</th>
                                <th>Comisión (%)</th>
                                <th>Monto Comisión (Bs.)</th>
                                <th>Neto a Pagar (Bs.)</th>
                                <th>Cuenta Destino</th>
                                <th>Banco</th>
                                <th>Fecha Generación</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                <tr>
                                    <td>
                                        <input type="checkbox" name="payout_ids[]" value="{{ $payout['id'] }}" class="payout-checkbox rounded-sm">
                                    </td>
                                    <td><strong>#{{ $payout['id'] }}</strong></td>
                                    <td>
                                        <div class="font-weight-bold">{{ $payout['aliado']['nombre'] ?? 'N/A' }}</div>
                                        <small class="text-muted">ID: {{ $payout['aliado']['id'] ?? 'N/A' }}</small>
                                    </td>
                                    <td>{{ $payout['aliado']['email'] ?? 'N/A' }}</td>
                                    <td class="text-success">
                                        <strong>{{ number_format($payout['montos']['monto_despues_descuento'] ?? $payout['montos']['neto'], 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <span class="badge badge-info">{{ number_format($payout['montos']['descuento_aliado'] ?? 0, 1) }}%</span>
                                    </td>
                                    <td>
                                        <span class="badge badge-warning">{{ number_format($payout['montos']['comision_porcentaje'] ?? 0, 1) }}%</span>
                                    </td>
                                    <td class="text-danger">
                                        <strong>{{ number_format($payout['montos']['comision_monto'] ?? 0, 2, ',', '.') }}</strong>
                                    </td>
                                    <td class="text-success">
                                        <strong>{{ number_format($payout['montos']['neto'] ?? 0, 2, ',', '.') }}</strong>
                                    </td>
                                    <td>
                                        <code>{{ $payout['aliado']['cuenta_bancaria'] ?? 'N/A' }}</code>
                                        <br>
                                        <small class="text-muted">{{ $payout['aliado']['tipo_cuenta'] ?? '' }}</small>
                                    </td>
                                    <td>{{ $payout['aliado']['banco'] ?? 'N/A' }}</td>
                                    <td>
                                        {{ \Carbon\Carbon::parse($payout['fechas']['generacion'])->format('d/m/Y H:i') }}
                                        <br>
                                        <small class="text-muted">Venta #{{ $payout['venta']['id'] ?? 'N/A' }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Resumen de montos --}}
                <div class="stats-card mt-4">
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-value">Bs. {{ number_format(collect($payouts)->sum('montos.neto'), 2, ',', '.') }}</div>
                            <div class="stat-label">Total a Pagar</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">Bs. {{ number_format(collect($payouts)->sum('montos.comision_monto'), 2, ',', '.') }}</div>
                            <div class="stat-label">Total Comisiones</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-value">{{ count($payouts) }}</div>
                            <div class="stat-label">Pagos Seleccionables</div>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

    {{-- Custom Confirmation Modal --}}
    <div id="confirmationModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Procesamiento de Pagos</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres confirmar los <span id="selected-count">0</span> pagos seleccionados? Esta acción no se puede deshacer.</p>
                <div class="form-group mt-4">
                    <label for="modal-fecha-pago">Fecha de Pago:</label>
                    <input type="date" id="modal-fecha-pago" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="form-group">
                    <label for="modal-referencia">Referencia de Pago:</label>
                    <input type="text" id="modal-referencia" class="form-control" placeholder="Referencia bancaria" required>
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
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const payoutCheckboxes = document.querySelectorAll('.payout-checkbox');
        const confirmPayoutsButton = document.getElementById('confirm-payouts-btn');
        const confirmationModal = document.getElementById('confirmationModal');
        const confirmModalButton = confirmationModal.querySelector('.confirm-modal-btn');
        const cancelModalButton = confirmationModal.querySelector('.cancel-modal-btn');
        const closeModalButton = confirmationModal.querySelector('.close-modal-btn');
        const selectedCountSpan = document.getElementById('selected-count');
        
        const fechaPagoInput = document.getElementById('modal-fecha-pago');
        const referenciaInput = document.getElementById('modal-referencia');
        const comprobanteInput = document.getElementById('modal-comprobante');

        // Manejar la selección de todos los checkboxes
        selectAllCheckbox.addEventListener('change', function() {
            payoutCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateSelectedCount();
        });

        // Actualizar contador de seleccionados
        payoutCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', updateSelectedCount);
        });

        function updateSelectedCount() {
            const selectedIds = Array.from(payoutCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            selectedCountSpan.textContent = selectedIds.length;
            
            // Actualizar texto del botón
            if (selectedIds.length > 0) {
                confirmPayoutsButton.innerHTML = `<i class="fas fa-check-circle"></i> Confirmar ${selectedIds.length} Pagos`;
            } else {
                confirmPayoutsButton.innerHTML = `<i class="fas fa-check-circle"></i> Confirmar Pagos Seleccionados`;
            }
        }

        // Manejar el click en el botón de confirmar pagos
        confirmPayoutsButton.addEventListener('click', function() {
            const selectedIds = Array.from(payoutCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            
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
                    const amountText = row.querySelector('td:nth-child(9) strong').textContent;
                    const amount = parseFloat(amountText.replace(/[Bs\.,\s]/g, '').replace(',', '.'));
                    return sum + (isNaN(amount) ? 0 : amount);
                }, 0);
            
            // Mostrar el modal de confirmación
            confirmationModal.classList.remove('hidden');
            confirmationModal.style.display = 'flex';
            
            // Actualizar información en el modal
            selectedCountSpan.textContent = selectedIds.length;
            document.querySelector('.modal-body p').innerHTML = 
                `¿Estás seguro de que quieres confirmar los <strong>${selectedIds.length}</strong> pagos seleccionados?<br>
                 <strong>Monto total: Bs. ${totalAmount.toFixed(2).replace('.', ',')}</strong><br>
                 Esta acción no se puede deshacer.`;
        });

        // Manejar la confirmación en el modal
        confirmModalButton.addEventListener('click', function() {
            const selectedIds = Array.from(payoutCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            const form = document.getElementById('confirmPayoutsForm');
            const hiddenInput = document.getElementById('selected-payout-ids');
            
            // Validar campos requeridos
            if (!fechaPagoInput.value || !referenciaInput.value) {
                Swal.fire({
                    icon: 'error',
                    title: 'Campos requeridos',
                    text: 'La fecha y referencia de pago son obligatorias.',
                    confirmButtonColor: '#8a2be2'
                });
                return;
            }

            // Setear valores en el formulario
            hiddenInput.value = JSON.stringify(selectedIds);
            
            // Actualizar campos en el formulario
            form.querySelector('input[name="fecha_pago"]').value = fechaPagoInput.value;
            form.querySelector('input[name="referencia_pago"]').value = referenciaInput.value;
            
            // Manejar archivo si se seleccionó
            if (comprobanteInput.files.length > 0) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(comprobanteInput.files[0]);
                form.querySelector('input[name="archivo_comprobante"]').files = dataTransfer.files;
            }

            // Mostrar loading
            Swal.fire({
                title: 'Procesando pagos...',
                text: 'Por favor espere mientras se confirman los pagos.',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            // Enviar formulario
            form.submit();
        });

        // Manejar el cierre del modal
        closeModalButton.addEventListener('click', function() {
            confirmationModal.style.display = 'none';
        });
        
        cancelModalButton.addEventListener('click', function() {
            confirmationModal.style.display = 'none';
        });

        // Manejar envío del formulario BNC
        document.getElementById('generateBncForm').addEventListener('submit', function(e) {
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
    });
</script>
@endpush