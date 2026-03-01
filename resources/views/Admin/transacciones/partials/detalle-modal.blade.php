{{-- resources/views/Admin/transacciones/partials/detalle-modal.blade.php --}}
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
                    <span class="status-badge-modern badge-{{ $transaccion->status }}">
                        @switch($transaccion->status)
                            @case('confirmed') ✅ Confirmada @break
                            @case('awaiting_review') ⏳ En Revisión @break
                            @case('pending_manual_confirmation') ⌛ Pendiente @break
                            @case('failed') ❌ Fallida @break
                            @default {{ $transaccion->status }}
                        @endswitch
                    </span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Método de Pago:</span>
                    <span class="detail-value">
                        @if($transaccion->payment_method == 'pago_movil') 📱 Pago Móvil
                        @elseif($transaccion->payment_method == 'transferencia_bancaria') 🏦 Transferencia
                        @else {{ $transaccion->payment_method }}
                        @endif
                    </span>
                </div>
            </div>
        </div>
        
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
        
        {{-- Información del aliado --}}
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
        
        {{-- Información financiera --}}
        <div class="detail-section">
            <h3>Detalles del Pago</h3>
            <div class="detail-grid financial">
                <div class="detail-item">
                    <span class="detail-label">Monto Original:</span>
                    <span class="detail-value amount">$ {{ number_format($transaccion->original_amount, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Descuento:</span>
                    <span class="detail-value">{{ $transaccion->discount_percentage }}%</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Comisión:</span>
                    <span class="detail-value commission">$ {{ number_format($transaccion->platform_commission, 0, ',', '.') }}</span>
                </div>
                <div class="detail-item">
                    <span class="detail-label">Neto:</span>
                    <span class="detail-value neto">$ {{ number_format($transaccion->amount_to_ally, 0, ',', '.') }}</span>
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

<style>
.transaction-detail {
    color: #1e293b;
}

.detail-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding-bottom: 15px;
    margin-bottom: 20px;
    border-bottom: 1px solid #e9eef2;
}

.detail-id {
    font-size: 1.1rem;
    font-weight: 600;
    color: #A601B3;
}

.detail-date {
    font-size: 0.9rem;
    color: #64748b;
}

.detail-section {
    margin-bottom: 25px;
}

.detail-section h3 {
    font-size: 1rem;
    color: #1e293b;
    margin-bottom: 15px;
    padding-bottom: 5px;
    border-bottom: 1px solid #e9eef2;
    font-weight: 600;
}

.detail-grid {
    display: grid;
    grid-template-columns: repeat(2, 1fr);
    gap: 15px;
}

.detail-item {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.detail-item.full-width {
    grid-column: 1 / -1;
}

.detail-label {
    font-size: 0.8rem;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.detail-value {
    font-size: 0.95rem;
    color: #1e293b;
    font-weight: 500;
}

.detail-value.amount {
    color: #10b981;
    font-weight: 600;
}

.detail-value.commission {
    color: #f59e0b;
    font-weight: 600;
}

.detail-value.neto {
    color: #A601B3;
    font-weight: 600;
}

.status-badge-modern {
    display: inline-flex;
    align-items: center;
    gap: 0.4rem;
    padding: 0.4rem 1rem;
    border-radius: 30px;
    font-size: 0.75rem;
    font-weight: 600;
}

.badge-confirmed {
    background: #d1fae5;
    color: #065f46;
}

.badge-awaiting_review, .badge-pending_manual_confirmation {
    background: #fef3c7;
    color: #92400e;
}

.badge-failed {
    background: #fee2e2;
    color: #991b1b;
}

.confirmation-data {
    background: #f8fafc;
    padding: 15px;
    border-radius: 10px;
    font-family: monospace;
    font-size: 0.85rem;
    color: #1e293b;
    overflow-x: auto;
    white-space: pre-wrap;
    word-wrap: break-word;
}

.detail-footer {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 25px;
    padding-top: 15px;
    border-top: 1px solid #e9eef2;
}

.btn-close {
    padding: 10px 25px;
    background: #f1f5f9;
    border: 1px solid #e9eef2;
    border-radius: 10px;
    color: #1e293b;
    cursor: pointer;
    transition: all 0.3s ease;
    font-weight: 500;
}

.btn-close:hover {
    background: #e2e8f0;
}

.footer-actions {
    display: flex;
    gap: 10px;
}

.btn-approve,
.btn-reject {
    padding: 10px 20px;
    border: none;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-approve {
    background: linear-gradient(135deg, #10b981, #059669);
    color: white;
}

.btn-approve:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
}

.btn-reject {
    background: linear-gradient(135deg, #ef4444, #dc2626);
    color: white;
}

.btn-reject:hover {
    transform: translateY(-2px);
    box-shadow: 0 5px 15px rgba(239, 68, 68, 0.3);
}
</style>