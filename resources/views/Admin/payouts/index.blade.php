@extends('layouts.admin')

{{-- Define el título de la página en la toolbar --}}
@section('page_title_toolbar', 'Pagos Pendientes a Aliados')

{{-- Agrega los estilos CSS específicos de esta vista --}}
@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap" rel="stylesheet">
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

            @if($pendingPayouts->isEmpty())
                <p class="no-payouts-message">
                    <i class="fas fa-sack-dollar icon"></i>
                    <br>
                    No hay pagos pendientes en este momento.
                </p>
            @else
                <div class="table-actions">
                    <form action="{{ route('admin.payouts.generate_csv') }}" method="GET" class="action-form">
                        <button type="submit" class="action-button generate-csv-btn">
                            <i class="fas fa-file-csv"></i> Generar CSV
                        </button>
                    </form>
                    
                    <form action="{{ route('admin.payouts.mark_processed') }}" method="POST" class="action-form process-form">
                        @csrf
                        <input type="hidden" name="payout_ids[]" id="selected-payout-ids">
                        <input type="text" name="transaction_reference" placeholder="Referencia bancaria (opcional)" class="form-control">
                        <button type="submit" class="action-button process-btn" id="mark-processed-btn">
                            <i class="fas fa-check-circle"></i> Marcar como Procesados
                        </button>
                    </form>
                </div>
                
                <div class="table-responsive">
                    <table class="payouts-table">
                        <thead>
                            <tr>
                                <th class="w-10"><input type="checkbox" id="select-all" class="rounded-sm"></th>
                                <th>ID Pago</th>
                                <th>Orden #</th>
                                <th>Aliado</th>
                                <th>Monto (Bs.)</th>
                                <th>Cuenta Destino</th>
                                <th>Fecha de Registro</th>
                                <th>Estado</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($pendingPayouts as $payout)
                                <tr>
                                    <td><input type="checkbox" name="payout_ids[]" value="{{ $payout->id }}" class="rounded-sm"></td>
                                    <td>{{ $payout->id }}</td>
                                    <td>{{ $payout->order->id }}</td>
                                    <td>{{ $payout->partner->name }}</td>
                                    <td>{{ number_format($payout->amount, 2, ',', '.') }}</td>
                                    <td>{{ $payout->partner->account_number }} ({{ $payout->partner->bank_name }})</td>
                                    <td>{{ $payout->created_at->format('d/m/Y H:i') }}</td>
                                    <td><span class="badge badge-warning">{{ $payout->status }}</span></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>

    {{-- Custom Confirmation Modal --}}
    <div id="processingModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Procesamiento</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que quieres marcar los pagos seleccionados como procesados? Esta acción no se puede deshacer.</p>
                <div class="form-group mt-4">
                    <label for="modal-transaction-ref">Referencia bancaria:</label>
                    <input type="text" id="modal-transaction-ref" class="form-control" placeholder="Referencia bancaria (opcional)">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                <button type="button" class="btn confirm-modal-btn">Procesar Pagos</button>
            </div>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const selectAllCheckbox = document.getElementById('select-all');
        const payoutCheckboxes = document.querySelectorAll('input[name="payout_ids[]"]');
        const markProcessedButton = document.getElementById('mark-processed-btn');
        const processingModal = document.getElementById('processingModal');
        const confirmProcessButton = processingModal.querySelector('.confirm-modal-btn');
        const cancelModalButton = processingModal.querySelector('.cancel-modal-btn');
        const closeModalButton = processingModal.querySelector('.close-modal-btn');
        const transactionRefInput = document.getElementById('modal-transaction-ref');
        
        let formToSubmit = null;

        // Manejar la selección de todos los checkboxes
        selectAllCheckbox.addEventListener('change', function() {
            payoutCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
        });

        // Manejar el click en el botón de "Marcar como Procesados"
        markProcessedButton.addEventListener('click', function(e) {
            e.preventDefault();
            const selectedIds = Array.from(payoutCheckboxes).filter(cb => cb.checked).map(cb => cb.value);
            
            if (selectedIds.length === 0) {
                // Notificación o modal de error en lugar de alert()
                console.warn('Por favor, selecciona al menos un pago para procesar.');
                return;
            }
            
            // Prepara el formulario para el modal
            formToSubmit = this.closest('form');
            const hiddenInput = formToSubmit.querySelector('#selected-payout-ids');
            hiddenInput.value = JSON.stringify(selectedIds);
            
            // Muestra el modal
            processingModal.classList.remove('hidden');
            processingModal.style.display = 'flex';
        });

        // Manejar la confirmación en el modal
        confirmProcessButton.addEventListener('click', function() {
            if (formToSubmit) {
                const transactionRef = transactionRefInput.value;
                const hiddenInput = formToSubmit.querySelector('#selected-payout-ids');
                
                // Crea un input oculto para la referencia bancaria y lo añade al formulario
                let refInput = formToSubmit.querySelector('input[name="transaction_reference"]');
                if (!refInput) {
                    refInput = document.createElement('input');
                    refInput.type = 'hidden';
                    refInput.name = 'transaction_reference';
                    formToSubmit.appendChild(refInput);
                }
                refInput.value = transactionRef;
                
                // Envía el formulario
                formToSubmit.submit();
            }
        });

        // Manejar el cierre del modal
        closeModalButton.addEventListener('click', function() {
            processingModal.style.display = 'none';
        });
        cancelModalButton.addEventListener('click', function() {
            processingModal.style.display = 'none';
        });
    });
</script>
@endpush