{{-- resources/views/transacciones/partials/detalle-modal.blade.php --}}
<div class="transaction-detail">
    <div class="detail-header">
        <span class="detail-id">Transacción #{{ $transaccion->id }}</span>
        <span class="detail-date">{{ $transaccion->created_at->format('d/m/Y H:i:s') }}</span>
    </div>
    
    <div class="detail-sections">
        {{-- Información general --}}
        <div class="detail-section">
            <h3>Información General</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Código de Referencia:</span>
                    <span class="detail-value">{{ $transaccion->reference_code }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Estado:</span>
                    <span class="status-badge status-{{ $transaccion->status }}">
                        @switch($transaccion->status)
                            @case('confirmed') ✅ Confirmada @break
                            @case('awaiting_review') ⏳ En Revisión @break
                            @case('pending_manual_confirmation') ⌛ Pendiente de Confirmación @break
                            @case('failed') ❌ Fallida @break
                            @default {{ $transaccion->status }}
                        @endswitch
                    </span>
                </div>
            </div>
        </div>
        
        {{-- Información del aliado (si es admin) --}}
        @if(auth()->user()->user_type === 'admin')
        <div class="detail-section">
            <h3>Aliado</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nombre:</span>
                    <span class="detail-value">{{ $transaccion->ally->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $transaccion->ally->email ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        @endif
        
        {{-- Información del usuario --}}
        <div class="detail-section">
            <h3>Usuario</h3>
            <div class="detail-grid">
                <div class="detail-item">
                    <span class="detail-label">Nombre:</span>
                    <span class="detail-value">{{ $transaccion->user->name ?? 'N/A' }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Email:</span>
                    <span class="detail-value">{{ $transaccion->user->email ?? 'N/A' }}</span>
                </div>
            </div>
        </div>
        
        {{-- Información financiera --}}
        <div class="detail-section">
            <h3>Detalles del Pago</h3>
            <div class="detail-grid financial">
                <div class="detail-item">
                    <span class="detail-label">Monto Original:</span>
                    <span class="detail-value">$ {{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Descuento:</span>
                    <span class="detail-value">{{ $transaccion->discount_percentage }}%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Comisión Plataforma:</span>
                    <span class="detail-value">$ {{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Monto para Aliado:</span>
                    <span class="detail-value amount">$ {{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item full-width">
                    <span class="detail-label">Método de Pago:</span>
                    <span class="detail-value">
                        @if($transaccion->payment_method == 'pago_movil') 📱 Pago Móvil
                        @elseif($transaccion->payment_method == 'transferencia_bancaria') 🏦 Transferencia Bancaria
                        @else {{ $transaccion->payment_method }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
        @if($transaccion->confirmation_data)
        <div class="detail-section">
            <h3>Datos de Confirmación</h3>
            <pre class="confirmation-data">{{ json_encode($transaccion->confirmation_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) }}</pre>
        </div>
        @endif
    </div>
    
    <div class="detail-footer">
        <button class="btn-close" onclick="closeModal()">Cerrar</button>
        @if(in_array($transaccion->status, ['pending_manual_confirmation', 'awaiting_review']) && auth()->user()->user_type === 'admin')
        <div class="footer-actions">
            <button class="btn-approve" onclick="aprobarTransaccion({{ $transaccion->id }})">✅ Confirmar</button>
            <button class="btn-reject" onclick="rechazarTransaccion({{ $transaccion->id }})">❌ Rechazar</button>
        </div>
        @endif
    </div>
</div>