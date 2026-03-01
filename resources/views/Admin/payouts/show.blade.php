@extends('layouts.admin')

@section('page_title_toolbar', 'Detalle del Pago #' . ($payout->id ?? ''))

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/payouts-show.css') }}">
@endpush

@section('content')
    @php
        // Asegurar que $payout sea un objeto/array accesible
        $payoutObj = $payout ?? [];
        
        // Datos básicos del payout
        $payoutId = is_object($payoutObj) ? $payoutObj->id ?? null : ($payoutObj['id'] ?? null);
        
        // Estado
        $status = is_object($payoutObj) ? $payoutObj->status ?? 'pending' : ($payoutObj['status'] ?? 'pending');
        $estadoClase = match($status) {
            'completed' => 'badge-completed',
            'processing' => 'badge-processing',
            'pending' => 'badge-pending',
            'reverted' => 'badge-reverted',
            default => 'badge-pending'
        };
        $estadoTexto = match($status) {
            'completed' => 'Completado',
            'processing' => 'En Proceso',
            'pending' => 'Pendiente',
            'reverted' => 'Revertido',
            default => 'Pendiente'
        };
        
        // Montos directos de la tabla payouts
        $saleAmount = is_object($payoutObj) ? $payoutObj->sale_amount ?? 0 : ($payoutObj['sale_amount'] ?? 0);
        $commissionPercentage = is_object($payoutObj) ? $payoutObj->commission_percentage ?? 0 : ($payoutObj['commission_percentage'] ?? 0);
        $commissionAmount = is_object($payoutObj) ? $payoutObj->commission_amount ?? 0 : ($payoutObj['commission_amount'] ?? 0);
        $netAmount = is_object($payoutObj) ? $payoutObj->net_amount ?? 0 : ($payoutObj['net_amount'] ?? 0);
        $allyDiscount = is_object($payoutObj) ? $payoutObj->ally_discount ?? 0 : ($payoutObj['ally_discount'] ?? 0);
        $amountAfterDiscount = is_object($payoutObj) ? $payoutObj->amount_after_discount ?? 0 : ($payoutObj['amount_after_discount'] ?? 0);
        
        // Fechas y referencias
        $generationDate = is_object($payoutObj) ? ($payoutObj->generation_date ?? $payoutObj->created_at ?? null) : ($payoutObj['generation_date'] ?? $payoutObj['created_at'] ?? null);
        $paymentDate = is_object($payoutObj) ? $payoutObj->payment_date ?? null : ($payoutObj['payment_date'] ?? null);
        $paymentReference = is_object($payoutObj) ? $payoutObj->payment_reference ?? null : ($payoutObj['payment_reference'] ?? null);
        $saleReference = is_object($payoutObj) ? $payoutObj->sale_reference ?? null : ($payoutObj['sale_reference'] ?? null);
        $saleId = is_object($payoutObj) ? $payoutObj->sale_id ?? null : ($payoutObj['sale_id'] ?? null);
        
        // Transferencia a empresa
        $companyTransferAmount = is_object($payoutObj) ? $payoutObj->company_transfer_amount ?? 0 : ($payoutObj['company_transfer_amount'] ?? 0);
        $companyCommission = is_object($payoutObj) ? $payoutObj->company_commission ?? 0 : ($payoutObj['company_commission'] ?? 0);
        $companyAccount = is_object($payoutObj) ? $payoutObj->company_account ?? 'N/A' : ($payoutObj['company_account'] ?? 'N/A');
        $companyBank = is_object($payoutObj) ? $payoutObj->company_bank ?? 'N/A' : ($payoutObj['company_bank'] ?? 'N/A');
        $companyTransferReference = is_object($payoutObj) ? $payoutObj->company_transfer_reference ?? null : ($payoutObj['company_transfer_reference'] ?? null);
        $companyTransferDate = is_object($payoutObj) ? $payoutObj->company_transfer_date ?? null : ($payoutObj['company_transfer_date'] ?? null);
        
        // Reversión
        $reversionReason = is_object($payoutObj) ? $payoutObj->reversion_reason ?? null : ($payoutObj['reversion_reason'] ?? null);
        $revertedAt = is_object($payoutObj) ? $payoutObj->reverted_at ?? null : ($payoutObj['reverted_at'] ?? null);
        $confirmedAt = is_object($payoutObj) ? $payoutObj->confirmed_at ?? null : ($payoutObj['confirmed_at'] ?? null);
        $batchProcessedAt = is_object($payoutObj) ? $payoutObj->batch_processed_at ?? null : ($payoutObj['batch_processed_at'] ?? null);
        
        // Comprobante
        $paymentProofPath = is_object($payoutObj) ? $payoutObj->payment_proof_path ?? null : ($payoutObj['payment_proof_path'] ?? null);
        
        // Respuesta del banco
        $companyTransferResponse = is_object($payoutObj) ? $payoutObj->company_transfer_response ?? null : ($payoutObj['company_transfer_response'] ?? null);
        
        // Método de pago
        $allyPaymentMethod = is_object($payoutObj) ? $payoutObj->ally_payment_method ?? 'Transferencia' : ($payoutObj['ally_payment_method'] ?? 'Transferencia');
        
        // Datos del aliado (si existe relación)
        $allyName = 'N/A';
        $allyEmail = '';
        $allyPhone = '';
        $allyDocumentType = '';
        $allyDocumentNumber = '';
        $allyBankName = '';
        $allyAccountType = '';
        $allyBankAccountNumber = '';
        
        if (isset($payoutObj->ally) && $payoutObj->ally) {
            $allyName = $payoutObj->ally->name ?? $payoutObj->ally->company_name ?? 'Aliado';
            $allyEmail = $payoutObj->ally->email ?? '';
            $allyPhone = $payoutObj->ally->phone ?? '';
            $allyDocumentType = $payoutObj->ally->document_type ?? 'V';
            $allyDocumentNumber = $payoutObj->ally->document_number ?? '';
            $allyBankName = $payoutObj->ally->bank_name ?? '';
            $allyAccountType = $payoutObj->ally->account_type ?? '';
            $allyBankAccountNumber = $payoutObj->ally->bank_account_number ?? '';
        } elseif (isset($payoutObj['ally']) && is_array($payoutObj['ally'])) {
            $allyName = $payoutObj['ally']['name'] ?? $payoutObj['ally']['company_name'] ?? 'Aliado';
            $allyEmail = $payoutObj['ally']['email'] ?? '';
            $allyPhone = $payoutObj['ally']['phone'] ?? '';
            $allyDocumentType = $payoutObj['ally']['document_type'] ?? 'V';
            $allyDocumentNumber = $payoutObj['ally']['document_number'] ?? '';
            $allyBankName = $payoutObj['ally']['bank_name'] ?? '';
            $allyAccountType = $payoutObj['ally']['account_type'] ?? '';
            $allyBankAccountNumber = $payoutObj['ally']['bank_account_number'] ?? '';
        }
        
        // Datos de la venta (si existe relación)
        $saleFechaVenta = null;
        if (isset($payoutObj->sale) && $payoutObj->sale) {
            $saleFechaVenta = $payoutObj->sale->fecha_venta ?? null;
        } elseif (isset($payoutObj['sale']) && is_array($payoutObj['sale'])) {
            $saleFechaVenta = $payoutObj['sale']['fecha_venta'] ?? null;
        }
    @endphp

    <div class="main-content detail-container">
        {{-- Header con información básica --}}
        <div class="detail-header">
            <div class="detail-header-info">
                <div class="detail-id">
                    <span class="badge">PAYOUT #{{ $payoutId }}</span>
                    <h2>Detalle del Pago a {{ $allyName }}</h2>
                </div>
                <div class="detail-status">
                    <span class="badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                </div>
            </div>
            <div class="detail-actions">
                <a href="{{ route('admin.payouts.index') }}" class="btn-action">
                    <i class="fas fa-arrow-left"></i> Volver
                </a>
                @if($status === 'pending')
                    <a href="{{ route('admin.payouts.edit', $payoutId) }}" class="btn-action">
                        <i class="fas fa-edit"></i> Editar
                    </a>
                    <button type="button" class="btn-action warning" onclick="confirmarRevertir({{ $payoutId }})">
                        <i class="fas fa-undo-alt"></i> Revertir
                    </button>
                @endif
                @if($status === 'processing')
                    <button type="button" class="btn-action primary" onclick="confirmarPago({{ $payoutId }})">
                        <i class="fas fa-check-circle"></i> Confirmar Pago
                    </button>
                @endif
            </div>
        </div>

        {{-- Grid de información --}}
        <div class="info-grid">
            {{-- Información del Aliado --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-handshake"></i>
                    <h3>Información del Aliado</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Nombre / Empresa:</span>
                        <span class="info-value">{{ $allyName }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Email:</span>
                        <span class="info-value">{{ $allyEmail ?: 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Teléfono:</span>
                        <span class="info-value">{{ $allyPhone ?: 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Documento:</span>
                        <span class="info-value">
                            {{ $allyDocumentType }} - {{ $allyDocumentNumber ?: 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Información Bancaria --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-university"></i>
                    <h3>Información Bancaria</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Banco:</span>
                        <span class="info-value">{{ $allyBankName ?: 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Tipo de Cuenta:</span>
                        <span class="info-value">{{ $allyAccountType ?: 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Número de Cuenta:</span>
                        <span class="info-value"><code>{{ $allyBankAccountNumber ?: 'N/A' }}</code></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Método de Pago:</span>
                        <span class="info-value">{{ $allyPaymentMethod }}</span>
                    </div>
                </div>
            </div>

            {{-- Información de la Venta --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-shopping-cart"></i>
                    <h3>Información de la Venta</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">ID Venta:</span>
                        <span class="info-value">#{{ $saleId ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Referencia:</span>
                        <span class="info-value">{{ $saleReference ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha de Venta:</span>
                        <span class="info-value">
                            {{ $saleFechaVenta ? \Carbon\Carbon::parse($saleFechaVenta)->format('d/m/Y H:i') : ($generationDate ? \Carbon\Carbon::parse($generationDate)->format('d/m/Y H:i') : 'N/A') }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Monto Venta:</span>
                        <span class="info-value success">Bs. {{ number_format($saleAmount, 2, ',', '.') }}</span>
                    </div>
                </div>
            </div>
        </div>

        {{-- Sección de Montos --}}
        <div class="detail-table">
            <div class="detail-table-header">
                <h3><i class="fas fa-calculator"></i> Detalle de Montos</h3>
            </div>
            <div class="amounts-grid">
                <div class="amount-item">
                    <div class="amount-label">Monto Original</div>
                    <div class="amount-value original">Bs. {{ number_format($saleAmount, 2, ',', '.') }}</div>
                    <div class="amount-sub">Venta sin descuentos</div>
                </div>
                <div class="amount-item">
                    <div class="amount-label">Descuento Aliado</div>
                    <div class="amount-value discount">{{ $allyDiscount }}%</div>
                    <div class="amount-sub">Bs. {{ number_format($amountAfterDiscount, 2, ',', '.') }}</div>
                </div>
                <div class="amount-item">
                    <div class="amount-label">Comisión</div>
                    <div class="amount-value commission">{{ $commissionPercentage }}%</div>
                    <div class="amount-sub">Bs. {{ number_format($commissionAmount, 2, ',', '.') }}</div>
                </div>
                <div class="amount-item">
                    <div class="amount-label">Neto a Pagar</div>
                    <div class="amount-value neto">Bs. {{ number_format($netAmount, 2, ',', '.') }}</div>
                    <div class="amount-sub">Monto final para aliado</div>
                </div>
            </div>
        </div>

        {{-- Información de Transferencia --}}
        <div class="info-grid">
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-exchange-alt"></i>
                    <h3>Transferencia a Empresa</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Monto Transferido:</span>
                        <span class="info-value success">Bs. {{ number_format($companyTransferAmount, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Comisión Empresa:</span>
                        <span class="info-value warning">Bs. {{ number_format($companyCommission, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Cuenta Origen:</span>
                        <span class="info-value"><code>{{ $companyAccount }}</code></span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Banco Origen:</span>
                        <span class="info-value">{{ $companyBank }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Referencia:</span>
                        <span class="info-value">{{ $companyTransferReference ?? 'N/A' }}</span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha Transferencia:</span>
                        <span class="info-value">
                            {{ $companyTransferDate ? \Carbon\Carbon::parse($companyTransferDate)->format('d/m/Y H:i') : 'N/A' }}
                        </span>
                    </div>
                </div>
            </div>

            {{-- Información de Pago al Aliado --}}
            <div class="info-card">
                <div class="info-card-header">
                    <i class="fas fa-money-bill-wave"></i>
                    <h3>Pago al Aliado</h3>
                </div>
                <div class="info-card-body">
                    <div class="info-row">
                        <span class="info-label">Estado del Pago:</span>
                        <span class="info-value">
                            <span class="badge {{ $estadoClase }}">{{ $estadoTexto }}</span>
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="info-label">Fecha Generación:</span>
                        <span class="info-value">
                            {{ $generationDate ? \Carbon\Carbon::parse($generationDate)->format('d/m/Y H:i') : 'N/A' }}
                        </span>
                    </div>
                    @if($paymentDate)
                    <div class="info-row">
                        <span class="info-label">Fecha Pago:</span>
                        <span class="info-value">
                            {{ \Carbon\Carbon::parse($paymentDate)->format('d/m/Y H:i') }}
                        </span>
                    </div>
                    @endif
                    @if($paymentReference)
                    <div class="info-row">
                        <span class="info-label">Referencia Pago:</span>
                        <span class="info-value">{{ $paymentReference }}</span>
                    </div>
                    @endif
                    @if($reversionReason)
                    <div class="info-row">
                        <span class="info-label">Motivo Reversión:</span>
                        <span class="info-value danger">{{ $reversionReason }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Historial de Estados --}}
        <div class="detail-table">
            <div class="detail-table-header">
                <h3><i class="fas fa-history"></i> Historial de Estados</h3>
            </div>
            <div class="timeline">
                @php
                    $historial = [
                        [
                            'fecha' => $payoutObj->created_at ?? null,
                            'estado' => 'pending',
                            'descripcion' => 'Pago generado automáticamente'
                        ]
                    ];
                    
                    if ($batchProcessedAt) {
                        $historial[] = [
                            'fecha' => $batchProcessedAt,
                            'estado' => 'processing',
                            'descripcion' => 'Incluido en lote de procesamiento'
                        ];
                    }
                    
                    if ($confirmedAt) {
                        $historial[] = [
                            'fecha' => $confirmedAt,
                            'estado' => 'completed',
                            'descripcion' => 'Pago confirmado'
                        ];
                    }
                    
                    if ($revertedAt) {
                        $historial[] = [
                            'fecha' => $revertedAt,
                            'estado' => 'reverted',
                            'descripcion' => $reversionReason ?? 'Pago revertido'
                        ];
                    }
                    
                    // Filtrar eventos sin fecha
                    $historial = array_filter($historial, function($item) {
                        return !is_null($item['fecha']);
                    });
                @endphp

                @forelse($historial as $item)
                    <div class="timeline-item {{ $item['estado'] }}">
                        <div class="timeline-date">{{ \Carbon\Carbon::parse($item['fecha'])->format('d/m/Y H:i:s') }}</div>
                        <div class="timeline-status">
                            @switch($item['estado'])
                                @case('pending') ⏳ Pendiente @break
                                @case('processing') 🔄 En Proceso @break
                                @case('completed') ✅ Completado @break
                                @case('reverted') ↩️ Revertido @break
                                @default {{ $item['estado'] }}
                            @endswitch
                        </div>
                        <div class="timeline-description">{{ $item['descripcion'] }}</div>
                    </div>
                @empty
                    <div class="no-data-message">
                        <p class="text-muted">No hay historial disponible</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- Comprobante (si existe) --}}
        @if($paymentProofPath)
        <div class="detail-table">
            <div class="detail-table-header">
                <h3><i class="fas fa-file-invoice"></i> Comprobante de Pago</h3>
            </div>
            <div class="proof-preview">
                <div class="proof-icon">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div class="proof-info">
                    <div class="proof-name">{{ basename($paymentProofPath) }}</div>
                    <div class="proof-meta">
                        Subido el {{ \Carbon\Carbon::parse($confirmedAt ?? $payoutObj->updated_at ?? now())->format('d/m/Y H:i') }}
                    </div>
                </div>
                <a href="{{ Storage::url($paymentProofPath) }}" class="proof-download" target="_blank">
                    <i class="fas fa-download"></i> Descargar
                </a>
            </div>
        </div>
        @endif

        {{-- Respuesta del Banco (si existe) --}}
        @if($companyTransferResponse)
        <div class="detail-table">
            <div class="detail-table-header">
                <h3><i class="fas fa-code"></i> Respuesta del Banco</h3>
            </div>
            <pre style="background: #f3f4f6; padding: 1rem; border-radius: 0.5rem; font-size: 0.75rem; overflow-x: auto;">
                {{ is_string($companyTransferResponse) ? json_encode(json_decode($companyTransferResponse), JSON_PRETTY_PRINT) : json_encode($companyTransferResponse, JSON_PRETTY_PRINT) }}
            </pre>
        </div>
        @endif

        {{-- Acciones adicionales --}}
        <div class="action-section">
            <a href="{{ route('admin.payouts.auditoria', $payoutId) }}" class="btn-action">
                <i class="fas fa-history"></i> Ver Auditoría
            </a>
            @if($status === 'completed')
                <button type="button" class="btn-action warning" onclick="confirmarRevertir({{ $payoutId }})">
                    <i class="fas fa-undo-alt"></i> Revertir Pago
                </button>
            @endif
        </div>
    </div>

    {{-- Modal de Confirmación para Revertir --}}
    <div id="revertirModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Revertir Pago #{{ $payoutId }}</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <form action="{{ route('admin.payouts.revertir', $payoutId) }}" method="POST" id="revertirForm">
                @csrf
                <div class="modal-body">
                    <p>¿Estás seguro de que quieres revertir este pago?</p>
                    <p class="text-warning"><i class="fas fa-exclamation-triangle"></i> Esta acción no se puede deshacer.</p>
                    
                    <div class="form-group mt-4">
                        <label for="motivo">Motivo de la reversión</label>
                        <textarea name="motivo" id="motivo" class="form-control" rows="3" 
                                  placeholder="Indique el motivo de la reversión..." required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                    <button type="submit" class="btn btn-danger">Revertir Pago</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Modal de Confirmación para Pago --}}
    <div id="confirmarModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Confirmar Pago #{{ $payoutId }}</h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <form action="{{ route('admin.payouts.confirmar-individual', $payoutId) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <p>Confirma que el pago ha sido procesado exitosamente.</p>
                    
                    <div class="form-group mt-4">
                        <label for="fecha_pago">Fecha de Pago</label>
                        <input type="date" name="fecha_pago" id="fecha_pago" class="form-control" 
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="referencia_pago">Referencia Bancaria</label>
                        <input type="text" name="referencia_pago" id="referencia_pago" class="form-control" 
                               placeholder="Ej: TRF-123456" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="archivo_comprobante">Comprobante de Pago</label>
                        <input type="file" name="archivo_comprobante" id="archivo_comprobante" class="form-control" 
                               accept=".pdf,.jpg,.png" required>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn cancel-modal-btn">Cancelar</button>
                    <button type="submit" class="btn confirm-modal-btn">Confirmar Pago</button>
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
        const confirmarModal = document.getElementById('confirmarModal');
        const closeButtons = document.querySelectorAll('.close-modal-btn, .cancel-modal-btn');

        window.confirmarRevertir = function(payoutId) {
            revertirModal.classList.remove('hidden');
        };

        window.confirmarPago = function(payoutId) {
            confirmarModal.classList.remove('hidden');
        };

        closeButtons.forEach(button => {
            button.addEventListener('click', function() {
                revertirModal.classList.add('hidden');
                confirmarModal.classList.add('hidden');
            });
        });

        window.addEventListener('click', function(event) {
            if (event.target === revertirModal) {
                revertirModal.classList.add('hidden');
            }
            if (event.target === confirmarModal) {
                confirmarModal.classList.add('hidden');
            }
        });
    });
</script>
@endpush