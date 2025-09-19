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
        }
        .badge-info {
            background-color: #17a2b8;
            color: #fff;
        }
        .badge-success {
            background-color: #28a745;
            color: #fff;
        }
        .badge-danger {
            background-color: #dc3545;
            color: #fff;
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

            @if($payouts->isEmpty())
                <p class="no-payouts-message">
                    <i class="fas fa-sack-dollar icon"></i>
                    <br>
                    No hay pagos pendientes en este momento.
                </p>
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
                            <label for="tipo_cuenta">Tipo Cuenta:</label>
                            <select name="tipo_cuenta" class="form-control" required>
                                <option value="corriente">Corriente</option>
                                <option value="ahorro">Ahorro</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="concepto">Concepto:</label>
                            <input type="text" name="concepto" class="form-control" value="PAGO COMISION {{ strtoupper(date('F Y')) }}">
                        </div>
                        <button type="submit" class="action-button generate-csv-btn">
                            <i class="fas fa-file-export"></i> Generar Archivo BNC
                        </button>
                    </form>
                    
                    {{-- Formulario para confirmar pagos --}}
                    <form action="{{ route('admin.payouts.confirm') }}" method="POST" class="action-form process-form" id="confirmPayoutsForm" enctype="multipart/form-data">
                        @csrf
                        <input type="hidden" name="payout_ids" id="selected-payout-ids">
                        <input type="date" name="fecha_pago" class="form-control" required value="{{ date('Y-m-d') }}">
                        <input type="text" name="referencia_pago" placeholder="Referencia de pago" class="form-control" required>
                        <input type="file" name="archivo_comprobante" class="form-control" accept=".pdf,.jpg,.png">
                        <button type="button" class="action-button process-btn" id="confirm-payouts-btn">
                            <i class="fas fa-check-circle"></i> Confirmar Pagos
                        </button>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th class="w-10"><input type="checkbox" id="select-all" class="rounded-sm"></th>
                                <th>ID Pago</th>
                                <th>Venta #</th>
                                <th>Aliado</th>
                                <th>Monto Venta (Bs.)</th>
                                <th>Comisión (%)</th>
                                <th>Monto Comisión (Bs.)</th>
                                <th>Cuenta Destino</th>
                                <th>Fecha Generación</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($payouts as $payout)
                                <tr>
                                    <td><input type="checkbox" name="payout_ids[]" value="{{ $payout->id }}" class="payout-checkbox rounded-sm"></td>
                                    <td>{{ $payout->id }}</td>
                                    <td>{{ $payout->sale_id }}</td>
                                    <td>{{ $payout->ally->company_name }}</td>
                                    <td>{{ number_format($payout->sale_amount, 2, ',', '.') }}</td>
                                    <td>{{ number_format($payout->commission_percentage, 2) }}%</td>
                                    <td>{{ number_format($payout->commission_amount, 2, ',', '.') }}</td>
                                    <td>{{ $payout->ally_account_number }} ({{ $payout->ally_bank }})</td>
                                    <td>{{ $payout->generation_date->format('d/m/Y H:i') }}</td>
                                    <td>
                                        <span class="badge 
                                            @if($payout->status == 'pending') badge-warning
                                            @elseif($payout->status == 'processing') badge-info
                                            @elseif($payout->status == 'paid') badge-success
                                            @else badge-danger @endif">
                                            {{ $payout->status_text }}
                                        </span>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Paginación --}}
                @if($payouts->hasPages())
                <div class="pagination-container">
                    {{ $payouts->links() }}
                </div>
                @endif
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
            
            // Mostrar el modal de confirmación
            confirmationModal.classList.remove('hidden');
            confirmationModal.style.display = 'flex';
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
            
            // Crear o actualizar campos en el formulario
            let fechaInput = form.querySelector('input[name="fecha_pago"]');
            let refInput = form.querySelector('input[name="referencia_pago"]');
            let comprobanteInputForm = form.querySelector('input[name="archivo_comprobante"]');
            
            fechaInput.value = fechaPagoInput.value;
            refInput.value = referenciaInput.value;
            
            // Manejar archivo si se seleccionó
            if (comprobanteInput.files.length > 0) {
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(comprobanteInput.files[0]);
                comprobanteInputForm.files = dataTransfer.files;
            }

            // Enviar formulario
            form.submit();
            
            // Cerrar modal
            confirmationModal.style.display = 'none';
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