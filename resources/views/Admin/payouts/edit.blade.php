@extends('layouts.admin')

@section('page_title_toolbar', 'Editar Pago #' . ($payoutId ?? ''))

@push('styles')
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin/payouts-edit.css') }}">
@endpush

@section('content')
    @php
        // Asegurar que $payout sea un objeto/array accesible
        $payoutObj = $payout ?? [];
        
        // Datos básicos del payout
        $payoutId = is_object($payoutObj) ? ($payoutObj->id ?? null) : ($payoutObj['id'] ?? null);
        
        // Estado
        $status = is_object($payoutObj) ? ($payoutObj->status ?? 'pending') : ($payoutObj['status'] ?? 'pending');
        $estadoClase = match ($status) {
            'completed' => 'success',
            'processing' => 'warning',
            'pending' => 'warning',
            'reverted' => 'danger',
            default => '',
        };
        $estadoTexto = match ($status) {
            'completed' => 'Completado',
            'processing' => 'En Proceso',
            'pending' => 'Pendiente',
            'reverted' => 'Revertido',
            default => $status,
        };
        
        // Montos directos de la tabla payouts
        $saleAmount = is_object($payoutObj) ? ($payoutObj->sale_amount ?? 0) : ($payoutObj['sale_amount'] ?? 0);
        $commissionPercentage = is_object($payoutObj) ? ($payoutObj->commission_percentage ?? 0) : ($payoutObj['commission_percentage'] ?? 0);
        $commissionAmount = is_object($payoutObj) ? ($payoutObj->commission_amount ?? 0) : ($payoutObj['commission_amount'] ?? 0);
        $netAmount = is_object($payoutObj) ? ($payoutObj->net_amount ?? 0) : ($payoutObj['net_amount'] ?? 0);
        $allyDiscount = is_object($payoutObj) ? ($payoutObj->ally_discount ?? 0) : ($payoutObj['ally_discount'] ?? 0);
        $amountAfterDiscount = is_object($payoutObj) ? ($payoutObj->amount_after_discount ?? 0) : ($payoutObj['amount_after_discount'] ?? 0);
        $saleId = is_object($payoutObj) ? ($payoutObj->sale_id ?? null) : ($payoutObj['sale_id'] ?? null);
        
        // Fechas
        $generationDate = is_object($payoutObj) ? ($payoutObj->generation_date ?? null) : ($payoutObj['generation_date'] ?? null);
        $paymentDate = is_object($payoutObj) ? ($payoutObj->payment_date ?? null) : ($payoutObj['payment_date'] ?? null);
        $revertedAt = is_object($payoutObj) ? ($payoutObj->reverted_at ?? null) : ($payoutObj['reverted_at'] ?? null);
        
        // Referencias
        $paymentReference = is_object($payoutObj) ? ($payoutObj->payment_reference ?? null) : ($payoutObj['payment_reference'] ?? null);
        $saleReference = is_object($payoutObj) ? ($payoutObj->sale_reference ?? null) : ($payoutObj['sale_reference'] ?? null);
        
        // Notas y reversión
        $notes = is_object($payoutObj) ? ($payoutObj->notes ?? '') : ($payoutObj['notes'] ?? '');
        $reversionReason = is_object($payoutObj) ? ($payoutObj->reversion_reason ?? null) : ($payoutObj['reversion_reason'] ?? null);
        
        // Datos del aliado (si existe relación)
        $allyName = 'N/A';
        if (isset($payoutObj->ally) && $payoutObj->ally) {
            $allyName = $payoutObj->ally->name ?? $payoutObj->ally->company_name ?? 'Aliado';
        } elseif (isset($payoutObj['ally']) && is_array($payoutObj['ally'])) {
            $allyName = $payoutObj['ally']['name'] ?? $payoutObj['ally']['company_name'] ?? 'Aliado';
        }
    @endphp

    <div class="main-content edit-container">
        {{-- Header --}}
        <div class="page-header">
            <div class="header-left">
                <a href="{{ route('admin.payouts.show', $payoutId) }}" class="btn-back">
                    <i class="fas fa-arrow-left"></i> Volver al Pago
                </a>
                <h1>
                    <span class="text-gray-900">Editar Pago</span>
                    <span class="text-purple">#{{ $payoutId }}</span>
                </h1>
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

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-exclamation-triangle"></i>
                <span>Por favor corrige los errores en el formulario.</span>
            </div>
        @endif

        {{-- Tarjeta de Edición --}}
        <div class="edit-card">
            {{-- Información del Pago (Solo Lectura) --}}
            <div class="payout-info">
                <div class="info-header">
                    <i class="fas fa-info-circle"></i>
                    <h3>Información del Pago</h3>
                </div>
                <div class="info-grid">
                    <div class="info-item">
                        <span class="info-label">Aliado</span>
                        <span class="info-value">{{ $allyName }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Venta ID</span>
                        <span class="info-value">#{{ $saleId ?? 'N/A' }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Monto Venta</span>
                        <span class="info-value success">Bs.
                            {{ number_format($saleAmount, 2, ',', '.') }}</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Estado Actual</span>
                        <span class="info-value {{ $estadoClase }}">{{ $estadoTexto }}</span>
                    </div>
                </div>
            </div>

            {{-- Formulario de Edición --}}
            <form action="{{ route('admin.payouts.update', $payoutId) }}" method="POST">
                @csrf
                @method('PUT')

                {{-- Sección: Montos --}}
                <div class="form-section">
                    <h3>
                        <i class="fas fa-calculator"></i>
                        Montos del Pago
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="sale_amount">
                                <i class="fas fa-dollar-sign"></i>
                                Monto de Venta
                            </label>
                            <div class="amount-field">
                                <span class="currency">Bs.</span>
                                <input type="number" name="sale_amount" id="sale_amount"
                                    class="form-control @error('sale_amount') error @enderror"
                                    value="{{ old('sale_amount', $saleAmount) }}" step="0.01" min="0"
                                    required>
                            </div>
                            @error('sale_amount')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="commission_percentage">
                                <i class="fas fa-percent"></i>
                                Porcentaje de Comisión
                            </label>
                            <input type="number" name="commission_percentage" id="commission_percentage"
                                class="form-control @error('commission_percentage') error @enderror"
                                value="{{ old('commission_percentage', $commissionPercentage) }}" step="0.1"
                                min="0" max="100" required>
                            @error('commission_percentage')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="commission_amount">
                                <i class="fas fa-coins"></i>
                                Monto de Comisión
                            </label>
                            <div class="amount-field">
                                <span class="currency">Bs.</span>
                                <input type="number" name="commission_amount" id="commission_amount"
                                    class="form-control @error('commission_amount') error @enderror"
                                    value="{{ old('commission_amount', $commissionAmount) }}" step="0.01"
                                    min="0" required>
                            </div>
                            @error('commission_amount')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="net_amount">
                                <i class="fas fa-money-bill-wave"></i>
                                Monto Neto
                            </label>
                            <div class="amount-field">
                                <span class="currency">Bs.</span>
                                <input type="number" name="net_amount" id="net_amount"
                                    class="form-control @error('net_amount') error @enderror"
                                    value="{{ old('net_amount', $netAmount) }}" step="0.01" min="0"
                                    required>
                            </div>
                            @error('net_amount')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="help-text">
                                <i class="fas fa-info-circle"></i>
                                Este es el monto final que recibe el aliado
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección: Descuentos --}}
                <div class="form-section">
                    <h3>
                        <i class="fas fa-tags"></i>
                        Descuentos Aplicados
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="ally_discount">
                                <i class="fas fa-percent"></i>
                                Descuento del Aliado (%)
                            </label>
                            <input type="number" name="ally_discount" id="ally_discount"
                                class="form-control @error('ally_discount') error @enderror"
                                value="{{ old('ally_discount', $allyDiscount) }}" step="0.1" min="0"
                                max="100">
                            @error('ally_discount')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="amount_after_discount">
                                <i class="fas fa-calculator"></i>
                                Monto después de Descuento
                            </label>
                            <div class="amount-field">
                                <span class="currency">Bs.</span>
                                <input type="number" name="amount_after_discount" id="amount_after_discount"
                                    class="form-control @error('amount_after_discount') error @enderror"
                                    value="{{ old('amount_after_discount', $amountAfterDiscount) }}"
                                    step="0.01" min="0">
                            </div>
                            @error('amount_after_discount')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección: Estado y Fechas --}}
                <div class="form-section">
                    <h3>
                        <i class="fas fa-clock"></i>
                        Estado y Fechas
                    </h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="status">
                                <i class="fas fa-flag"></i>
                                Estado del Pago
                            </label>
                            <select name="status" id="status" class="form-control @error('status') error @enderror"
                                required>
                                <option value="pending" {{ old('status', $status) == 'pending' ? 'selected' : '' }}>Pendiente</option>
                                <option value="processing" {{ old('status', $status) == 'processing' ? 'selected' : '' }}>En Proceso</option>
                                <option value="completed" {{ old('status', $status) == 'completed' ? 'selected' : '' }}>Completado</option>
                                <option value="reverted" {{ old('status', $status) == 'reverted' ? 'selected' : '' }}>Revertido</option>
                            </select>
                            @error('status')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="generation_date">
                                <i class="fas fa-calendar-plus"></i>
                                Fecha de Generación
                            </label>
                            <input type="datetime-local" name="generation_date" id="generation_date"
                                class="form-control @error('generation_date') error @enderror"
                                value="{{ old('generation_date', $generationDate ? \Carbon\Carbon::parse($generationDate)->format('Y-m-d\TH:i') : '') }}">
                            @error('generation_date')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group">
                            <label for="payment_date">
                                <i class="fas fa-calendar-check"></i>
                                Fecha de Pago
                            </label>
                            <input type="date" name="payment_date" id="payment_date"
                                class="form-control @error('payment_date') error @enderror"
                                value="{{ old('payment_date', $paymentDate ? \Carbon\Carbon::parse($paymentDate)->format('Y-m-d') : '') }}">
                            @error('payment_date')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección: Referencias --}}
                <div class="form-section">
                    <h3>
                        <i class="fas fa-hashtag"></i>
                        Referencias
                    </h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="payment_reference">
                                <i class="fas fa-barcode"></i>
                                Referencia de Pago
                            </label>
                            <input type="text" name="payment_reference" id="payment_reference"
                                class="form-control @error('payment_reference') error @enderror"
                                value="{{ old('payment_reference', $paymentReference) }}"
                                placeholder="Ej: TRF-123456, PAG-7890">
                            @error('payment_reference')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>

                        <div class="form-group full-width">
                            <label for="sale_reference">
                                <i class="fas fa-receipt"></i>
                                Referencia de Venta
                            </label>
                            <input type="text" name="sale_reference" id="sale_reference"
                                class="form-control @error('sale_reference') error @enderror"
                                value="{{ old('sale_reference', $saleReference) }}"
                                placeholder="Referencia de la venta original">
                            @error('sale_reference')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                        </div>
                    </div>
                </div>

                {{-- Sección: Notas --}}
                <div class="form-section">
                    <h3>
                        <i class="fas fa-sticky-note"></i>
                        Notas y Observaciones
                    </h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <label for="notes">
                                <i class="fas fa-edit"></i>
                                Notas Internas
                            </label>
                            <textarea name="notes" id="notes" class="form-control @error('notes') error @enderror" rows="4"
                                placeholder="Agrega notas internas sobre este pago...">{{ old('notes', $notes) }}</textarea>
                            @error('notes')
                                <div class="error-message">
                                    <i class="fas fa-exclamation-circle"></i>
                                    {{ $message }}
                                </div>
                            @enderror
                            <div class="help-text">
                                <i class="fas fa-info-circle"></i>
                                Estas notas solo son visibles para administradores
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Sección: Reversión (solo si está revertido) --}}
                @if ($status === 'reverted' && $reversionReason)
                    <div class="form-section">
                        <h3 style="color: #ef4444;">
                            <i class="fas fa-undo-alt"></i>
                            Información de Reversión
                        </h3>
                        <div class="form-grid">
                            <div class="form-group full-width">
                                <label for="reversion_reason">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Motivo de Reversión
                                </label>
                                <textarea name="reversion_reason" id="reversion_reason" class="form-control" rows="3" readonly>{{ $reversionReason }}</textarea>
                            </div>

                            <div class="form-group">
                                <label for="reverted_at">
                                    <i class="fas fa-calendar-times"></i>
                                    Fecha de Reversión
                                </label>
                                <input type="text" name="reverted_at" id="reverted_at" class="form-control"
                                    value="{{ $revertedAt ? \Carbon\Carbon::parse($revertedAt)->format('d/m/Y H:i:s') : '' }}"
                                    readonly>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Botones de Acción --}}
                <div class="form-actions">
                    <a href="{{ route('admin.payouts.show', $payoutId) }}" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancelar
                    </a>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Guardar Cambios
                    </button>
                </div>
            </form>
        </div>

        {{-- Acciones Peligrosas (solo si está pendiente) --}}
        @if ($status === 'pending')
            <div class="edit-card" style="border-color: #fee2e2;">
                <div class="form-section">
                    <h3 style="color: #ef4444;">
                        <i class="fas fa-exclamation-triangle"></i>
                        Zona de Peligro
                    </h3>
                    <div class="form-grid">
                        <div class="form-group full-width">
                            <p class="text-muted">Estas acciones son irreversibles y pueden afectar los registros
                                financieros.</p>
                            <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                                <button type="button" class="btn btn-danger" onclick="confirmarEliminacion()">
                                    <i class="fas fa-trash"></i> Eliminar Pago
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

    {{-- Modal de Confirmación para Eliminación --}}
    <div id="confirmarModal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>
                    <i class="fas fa-exclamation-triangle"></i>
                    Confirmar Eliminación
                </h3>
                <button type="button" class="close-modal-btn">&times;</button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el pago <strong>#{{ $payoutId }}</strong>?</p>
                <p class="text-muted" style="margin-top: 1rem;">
                    <i class="fas fa-info-circle"></i>
                    Esta acción no se puede deshacer y eliminará permanentemente este registro.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="cerrarModal()">Cancelar</button>
                <form action="{{ route('admin.payouts.destroy', $payoutId) }}" method="POST" style="display: inline;">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn btn-danger">
                        <i class="fas fa-trash"></i> Eliminar Permanentemente
                    </button>
                </form>
            </div>
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

            // Auto-cálculo de montos
            const saleAmount = document.getElementById('sale_amount');
            const commissionPercentage = document.getElementById('commission_percentage');
            const commissionAmount = document.getElementById('commission_amount');
            const netAmount = document.getElementById('net_amount');
            const allyDiscount = document.getElementById('ally_discount');
            const amountAfterDiscount = document.getElementById('amount_after_discount');

            function calcularMontos() {
                const venta = parseFloat(saleAmount.value) || 0;
                const porcentaje = parseFloat(commissionPercentage.value) || 0;
                const descuento = parseFloat(allyDiscount.value) || 0;

                // Calcular monto después de descuento
                const montoDespuesDescuento = venta * (1 - (descuento / 100));
                if (amountAfterDiscount) {
                    amountAfterDiscount.value = montoDespuesDescuento.toFixed(2);
                }

                // Calcular comisión y neto
                const comisionCalculada = montoDespuesDescuento * (porcentaje / 100);
                if (commissionAmount) {
                    commissionAmount.value = comisionCalculada.toFixed(2);
                }

                const netoCalculado = montoDespuesDescuento - comisionCalculada;
                if (netAmount) {
                    netAmount.value = netoCalculado.toFixed(2);
                }
            }

            if (saleAmount && commissionPercentage && allyDiscount) {
                saleAmount.addEventListener('input', calcularMontos);
                commissionPercentage.addEventListener('input', calcularMontos);
                allyDiscount.addEventListener('input', calcularMontos);
            }

            // Validar que los montos no sean negativos
            const montoInputs = document.querySelectorAll('input[type="number"]');
            montoInputs.forEach(input => {
                input.addEventListener('change', function() {
                    if (this.value < 0) this.value = 0;
                });
            });

            // Modal de confirmación
            const confirmarModal = document.getElementById('confirmarModal');
            const closeButtons = document.querySelectorAll('.close-modal-btn');

            window.confirmarEliminacion = function() {
                if (confirmarModal) {
                    confirmarModal.classList.remove('hidden');
                }
            };

            closeButtons.forEach(button => {
                button.addEventListener('click', function() {
                    if (confirmarModal) {
                        confirmarModal.classList.add('hidden');
                    }
                });
            });

            window.addEventListener('click', function(event) {
                if (event.target === confirmarModal) {
                    confirmarModal.classList.add('hidden');
                }
            });

            window.cerrarModal = function() {
                if (confirmarModal) {
                    confirmarModal.classList.add('hidden');
                }
            };
        });
    </script>
@endpush
